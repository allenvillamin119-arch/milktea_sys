<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryLog;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Show supply items with filtering and search
        $query = Product::with(['inventoryLogs', 'categoryModel'])
            ->where('type', 'supply');
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        // Status filter
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'in_stock') {
                $query->where('stock', '>', 0)
                      ->where('stock', '>', 'low_stock_threshold');
            } elseif ($status === 'low_stock') {
                $query->where('stock', '<=', 'low_stock_threshold')
                      ->where('stock', '>', 0);
            } elseif ($status === 'out_of_stock') {
                $query->where('stock', 0);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Active filter (show all by default, but can filter active only)
        if ($request->filled('active')) {
            $query->where('is_active', $request->active === 'true');
        }
        
        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $products = $query->latest()->paginate(15)->withQueryString();
        
        return view('inventory.index', compact('products'));
    }

    /**
     * Show form to create a new supply item
     */
    public function create()
    {
        return view('inventory.create');
    }

    /**
     * Store a newly created supply item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => 'supply',
            'item_type' => 'inventory',
            'category' => 'supply',
            'category_id' => null,
            'stock' => $validated['stock'],
            'price' => $validated['price'],
            'low_stock_threshold' => $validated['low_stock_threshold'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Supply item created successfully.');
    }

    public function stockIn()
    {
        // Show only supply items for stock-in
        $products = Product::where('type', 'supply')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('inventory.stock-in', compact('products'));
    }

    public function stockInStore(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
            'log_date' => 'required|date',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        // Update product stock
        $product->increment('stock', $validated['quantity']);

        // Create inventory log
        InventoryLog::create([
            'product_id' => $validated['product_id'],
            'type' => 'IN',
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'] ?? 'Stock replenishment',
            'user_id' => auth()->id(),
            'log_date' => $validated['log_date'],
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Stock added successfully.');
    }

    public function stockOut()
    {
        // Show only supply items for stock-out
        $products = Product::where('type', 'supply')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('inventory.stock-out', compact('products'));
    }

    public function stockOutStore(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
            'log_date' => 'required|date',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Check if enough stock
        if ($product->stock < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Insufficient stock available.']);
        }

        // Update product stock
        $product->decrement('stock', $validated['quantity']);

        // Create inventory log
        InventoryLog::create([
            'product_id' => $validated['product_id'],
            'type' => 'OUT',
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'] ?? 'Stock adjustment',
            'user_id' => auth()->id(),
            'log_date' => $validated['log_date'],
        ]);

        // If this supply is now low, notify admins
        $product->refresh();
        if ($product->type === 'supply' && $product->stock <= $product->low_stock_threshold) {
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\SupplyLowNotification($product));
            }

            $emails = array_filter(array_map('trim', explode(',', env('SUPPLY_ALERT_EMAILS', ''))));
            foreach ($emails as $mail) {
                if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    \Illuminate\Support\Facades\Notification::route('mail', $mail)
                        ->notify(new \App\Notifications\SupplyLowNotification($product));
                }
            }
        }

        return redirect()->route('inventory.index')
            ->with('success', 'Stock reduced successfully.');
    }

    public function history()
    {
        $logs = InventoryLog::with(['product', 'user'])
            ->latest()
            ->paginate(15);
        
        return view('inventory.history', compact('logs'));
    }

    /**
     * Edit a supply item
     */
    public function edit(Product $product)
    {
        // Only allow editing supplies
        if (!$product->isSupply()) {
            return redirect()->route('products.index')
                ->with('error', 'Only supply items can be edited here.');
        }

        return view('inventory.edit', compact('product'));
    }

    /**
     * Update a supply item
     */
    public function update(Request $request, Product $product)
    {
        // Only allow editing supplies
        if (!$product->isSupply()) {
            return redirect()->route('products.index')
                ->with('error', 'Only supply items can be edited here.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Supply item updated successfully.');
    }

    /**
     * Delete a supply item
     */
    public function destroy(Product $product)
    {
        // Only allow deleting supplies
        if (!$product->isSupply()) {
            return redirect()->route('products.index')
                ->with('error', 'Only supply items can be deleted here.');
        }

        $product->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Supply item deleted successfully.');
    }
}
