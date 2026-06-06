<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        // Get only sellable products (type='product', not supplies)
        $products = Product::with('categoryModel')
            ->sellable()
            ->where('stock', '>', 0)
            ->orderBy('category')
            ->get()
            ->groupBy('category');

        // Get categories for filtering (only product categories, not supply)
        $categories = Category::where('is_active', true)
            ->whereNotIn('slug', ['supply'])
            ->whereHas('products', function ($query) {
                $query->sellable()
                    ->where('stock', '>', 0);
            })
            ->orderBy('name')
            ->get();
        
        return view('pos.index', compact('products', 'categories'));
    }

    public function getProducts()
    {
        // Get only sellable products for POS
        $products = Product::sellable()
            ->where('stock', '>', 0)
            ->select('id', 'name', 'price', 'stock', 'category', 'category_id')
            ->get();
        
        return response()->json($products);
    }

    public function processTransaction(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'cash_received' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,gcash,paymaya',
        ]);

        DB::beginTransaction();
        
        try {
            // Calculate total
            $totalAmount = 0;
            $cartItems = [];
            
            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['productId']);
                
                if (!$product || $product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                // Check if product is sellable (not a supply item)
                if (!$product->isSellable()) {
                    throw new \Exception("{$product->name} is not available in POS");
                }
                
                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;
                
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Calculate change
            $cashReceived = $validated['cash_received'];
            $change = $cashReceived - $totalAmount;
            
            if ($change < 0) {
                throw new \Exception('Insufficient payment amount');
            }

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => Transaction::generateTransactionNumber(),
                'total_amount' => $totalAmount,
                'cash_received' => $cashReceived,
                'change' => $change,
                'payment_method' => $validated['payment_method'],
                'user_id' => auth()->id(),
            ]);

            // Create transaction items and update stock
            foreach ($cartItems as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
                // Update product stock
                $item['product']->decrement('stock', $item['quantity']);

                InventoryLog::create([
                    'product_id' => $item['product']->id,
                    'type' => 'OUT',
                    'quantity' => $item['quantity'],
                    'reason' => 'POS sale - ' . $transaction->transaction_number,
                    'user_id' => auth()->id(),
                    'log_date' => now()->toDateString(),
                ]);

                // Consume defined supplies for this product (if any)
                if (method_exists($item['product'], 'supplies')) {
                    $supplies = $item['product']->supplies()->withPivot('quantity')->get();

                    foreach ($supplies as $supply) {
                        $useQty = (int) $supply->pivot->quantity * $item['quantity'];

                        if ($useQty <= 0) {
                            continue;
                        }

                        // decrement supply stock
                        $supply->decrement('stock', $useQty);
                        $supply->refresh();

                        InventoryLog::create([
                            'product_id' => $supply->id,
                            'type' => 'OUT',
                            'quantity' => $useQty,
                            'reason' => 'POS sale - supply used for ' . $item['product']->name . ' - ' . $transaction->transaction_number,
                            'user_id' => auth()->id(),
                            'log_date' => now()->toDateString(),
                        ]);

                        // notify admins if supply low
                        if ($supply->stock <= $supply->low_stock_threshold) {
                            $admins = \App\Models\User::where('role', 'admin')->get();
                            foreach ($admins as $admin) {
                                $admin->notify(new \App\Notifications\SupplyLowNotification($supply));
                            }

                            // also notify additional emails from env (comma-separated)
                            $emails = array_filter(array_map('trim', explode(',', env('SUPPLY_ALERT_EMAILS', ''))));
                            foreach ($emails as $mail) {
                                if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                                    \Illuminate\Support\Facades\Notification::route('mail', $mail)
                                        ->notify(new \App\Notifications\SupplyLowNotification($supply));
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'transaction' => $transaction->load('transactionItems.product'),
                'message' => 'Transaction completed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function voidTransaction(Request $request)
    {
        $validated = $request->validate([
            'transaction_number' => 'required|string|max:100',
            'void_reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $transaction = Transaction::with('transactionItems.product')
                ->where('transaction_number', $validated['transaction_number'])
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                throw new \Exception('Transaction not found.');
            }

            if ($transaction->status === 'voided') {
                throw new \Exception('Transaction is already voided.');
            }

            foreach ($transaction->transactionItems as $item) {
                if (!$item->product) {
                    continue;
                }

                // restore sold product stock
                $item->product->increment('stock', $item->quantity);

                InventoryLog::create([
                    'product_id' => $item->product_id,
                    'type' => 'IN',
                    'quantity' => $item->quantity,
                    'reason' => 'POS void - ' . $transaction->transaction_number,
                    'user_id' => auth()->id(),
                    'log_date' => now()->toDateString(),
                ]);

                // restore supplies used by this sold product (if any)
                if (method_exists($item->product, 'supplies')) {
                    $supplies = $item->product->supplies()->withPivot('quantity')->get();

                    foreach ($supplies as $supply) {
                        $restoreQty = (int) $supply->pivot->quantity * $item->quantity;

                        if ($restoreQty <= 0) {
                            continue;
                        }

                        $supply->increment('stock', $restoreQty);

                        InventoryLog::create([
                            'product_id' => $supply->id,
                            'type' => 'IN',
                            'quantity' => $restoreQty,
                            'reason' => 'POS void - supply restore for ' . $item->product->name . ' - ' . $transaction->transaction_number,
                            'user_id' => auth()->id(),
                            'log_date' => now()->toDateString(),
                        ]);
                    }
                }
            }

            $transaction->update([
                'status' => 'voided',
                'void_reason' => $validated['void_reason'],
                'voided_by' => auth()->id(),
                'voided_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'transaction' => $transaction->fresh(['transactionItems.product', 'user', 'voidedBy']),
                'message' => 'Transaction voided and stock restored successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt($id)
    {
        $transaction = Transaction::with(['transactionItems.product', 'user', 'voidedBy'])
            ->findOrFail($id);
        
        return view('pos.receipt', compact('transaction'));
    }

    public function printReceipt($id)
    {
        $transaction = Transaction::with(['transactionItems.product', 'user', 'voidedBy'])
            ->findOrFail($id);
        
        return view('pos.print-receipt', compact('transaction'));
    }
}
