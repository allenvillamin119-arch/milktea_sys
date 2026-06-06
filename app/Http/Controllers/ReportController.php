<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\InventoryLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function salesIndex()
    {
        return view('reports.sales');
    }

    public function salesReport(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|in:daily,weekly,monthly,yearly,custom',
            'start_date' => 'required_if:period,custom|nullable|date',
            'end_date' => 'required_if:period,custom|nullable|date|after_or_equal:start_date',
            'transaction_search' => 'nullable|string|max:100',
            'payment_method' => 'nullable|in:cash,gcash,paymaya',
            'cashier_id' => 'nullable|exists:users,id',
        ]);

        $query = $this->buildSalesQuery($validated)->with(['transactionItems.product', 'user']);
        $groupBy = $validated['period'] === 'yearly' ? 'MONTH' : 'DAY';

        $allTransactions = (clone $query)->latest()->get();
        $transactions = (clone $query)->latest()->paginate(20)
            ->appends($request->only([
                'period',
                'start_date',
                'end_date',
                'transaction_search',
                'payment_method',
                'cashier_id',
            ]));
        
        // Group transactions by period
        $groupedSales = $this->groupSalesByPeriod($allTransactions, $groupBy);
        
        // Top selling products
        $topProducts = $this->getTopSellingProducts($allTransactions);
        $paymentMethods = $allTransactions->groupBy('payment_method');
        
        // Summary
        $totalSales = $allTransactions->sum('total_amount');
        $totalTransactions = $allTransactions->count();
        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        $period = $validated['period'];
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $periodLabel = $this->salesPeriodLabel($validated);
        $transactionSearch = $validated['transaction_search'] ?? null;
        $paymentMethod = $validated['payment_method'] ?? null;
        $cashierId = $validated['cashier_id'] ?? null;
        $cashiers = User::orderBy('name')->get(['id', 'name']);

        return view('reports.sales-report', compact(
            'transactions',
            'groupedSales',
            'topProducts',
            'totalSales',
            'totalTransactions',
            'averageTransaction',
            'paymentMethods',
            'period',
            'periodLabel',
            'startDate',
            'endDate',
            'transactionSearch',
            'paymentMethod',
            'cashierId',
            'cashiers'
        ));
    }

    public function inventoryIndex()
    {
        return view('reports.inventory');
    }

    public function salesCsv(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|in:daily,weekly,monthly,yearly,custom',
            'start_date' => 'required_if:period,custom|nullable|date',
            'end_date' => 'required_if:period,custom|nullable|date|after_or_equal:start_date',
            'transaction_search' => 'nullable|string|max:100',
            'payment_method' => 'nullable|in:cash,gcash,paymaya',
            'cashier_id' => 'nullable|exists:users,id',
        ]);

        $query = $this->buildSalesQuery($validated)->with(['transactionItems.product', 'user']);
        $transactions = $query->latest()->get();
        $filename = 'sales-report-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $file = fopen('php://output', 'w');
            $totalSales = $transactions->sum('total_amount');
            $totalTransactions = $transactions->count();
            $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

            fputcsv($file, ['Sales Report Summary']);
            fputcsv($file, ['Total Sales', number_format((float) $totalSales, 2, '.', '')]);
            fputcsv($file, ['Total Transactions', $totalTransactions]);
            fputcsv($file, ['Average Transaction', number_format((float) $averageTransaction, 2, '.', '')]);
            fputcsv($file, []);
            fputcsv($file, ['Transaction #', 'Date & Time', 'Cashier', 'Payment Method', 'Items', 'Total']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_number,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    optional($transaction->user)->name ?? 'Unknown',
                    ucfirst($transaction->payment_method),
                    $transaction->transactionItems->count(),
                    number_format((float) $transaction->total_amount, 2, '.', ''),
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function inventoryReport(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|in:daily,monthly,yearly,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = $this->buildInventoryQuery($validated)->with(['product', 'user']);

        $allLogs = (clone $query)->get();
        $logs = (clone $query)->latest('log_date')->latest('created_at')->paginate(20)
            ->appends($request->only(['period', 'start_date', 'end_date']));

        // Summary
        $totalStockIn = $allLogs->where('type', 'IN')->sum('quantity');
        $totalStockOut = $allLogs->where('type', 'OUT')->sum('quantity');
        $inventoryMovement = $this->groupInventoryMovementByDate($allLogs);

        // Current stock levels
        $currentStock = Product::with('categoryModel')
            ->where('is_active', true)
            ->select('id', 'name', 'category', 'category_id', 'item_type', 'stock', 'low_stock_threshold')
            ->orderBy('stock', 'asc')
            ->get();

        $lowStockItems = $currentStock->filter(function ($product) {
            return $product->stock <= $product->low_stock_threshold;
        });
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $period = $validated['period'] ?? 'custom';
        $periodLabel = $this->inventoryPeriodLabel($validated);

        return view('reports.inventory-report', compact(
            'logs',
            'totalStockIn',
            'totalStockOut',
            'currentStock',
            'lowStockItems',
            'inventoryMovement',
            'startDate',
            'endDate',
            'period',
            'periodLabel'
        ));
    }

    public function inventoryCsv(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|in:daily,monthly,yearly,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = $this->buildInventoryQuery($validated)->with(['product', 'user']);
        $logs = $query->latest('log_date')->latest('created_at')->get();
        $filename = 'inventory-report-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $file = fopen('php://output', 'w');
            $totalStockIn = $logs->where('type', 'IN')->sum('quantity');
            $totalStockOut = $logs->where('type', 'OUT')->sum('quantity');

            fputcsv($file, ['Inventory Report Summary']);
            fputcsv($file, ['Total Stock In', $totalStockIn]);
            fputcsv($file, ['Total Stock Out', $totalStockOut]);
            fputcsv($file, ['Total Movements', $logs->count()]);
            fputcsv($file, []);
            fputcsv($file, ['Date', 'Product', 'Type', 'Quantity', 'Reason', 'Processed By']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->log_date->format('Y-m-d'),
                    optional($log->product)->name ?? 'Unknown product',
                    $log->type === 'IN' ? 'Stock In' : 'Stock Out',
                    $log->quantity,
                    $log->reason ?? 'N/A',
                    optional($log->user)->name ?? 'Unknown',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildSalesQuery(array $validated)
    {
        $query = Transaction::query();

        switch ($validated['period']) {
            case 'daily':
                $query->whereDate('created_at', today());
                break;
            case 'weekly':
                $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;
            case 'monthly':
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                break;
            case 'yearly':
                $query->whereYear('created_at', now()->year);
                break;
            case 'custom':
                if ($validated['start_date'] && $validated['end_date']) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($validated['start_date'])->startOfDay(),
                        Carbon::parse($validated['end_date'])->endOfDay(),
                    ]);
                }
                break;
        }

        if (!empty($validated['transaction_search'])) {
            $search = $validated['transaction_search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($validated['payment_method'])) {
            $query->where('payment_method', $validated['payment_method']);
        }

        if (!empty($validated['cashier_id'])) {
            $query->where('user_id', $validated['cashier_id']);
        }

        return $query;
    }

    private function buildInventoryQuery(array $validated)
    {
        $query = InventoryLog::query();

        switch ($validated['period'] ?? 'custom') {
            case 'daily':
                $query->whereDate('log_date', today());
                break;
            case 'monthly':
                $query->whereMonth('log_date', now()->month)
                    ->whereYear('log_date', now()->year);
                break;
            case 'yearly':
                $query->whereYear('log_date', now()->year);
                break;
            case 'custom':
            default:
                break;
        }

        if (!empty($validated['start_date'])) {
            $query->whereDate('log_date', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->whereDate('log_date', '<=', $validated['end_date']);
        }

        return $query;
    }

    private function salesPeriodLabel(array $validated)
    {
        return match ($validated['period']) {
            'daily' => 'Daily Report - ' . today()->format('F d, Y'),
            'weekly' => 'Weekly Report - ' . now()->startOfWeek()->format('M d, Y') . ' to ' . now()->endOfWeek()->format('M d, Y'),
            'monthly' => 'Monthly Report - ' . now()->format('F Y'),
            'yearly' => 'Yearly Report - ' . now()->format('Y'),
            'custom' => 'Custom Report - ' . Carbon::parse($validated['start_date'])->format('M d, Y') . ' to ' . Carbon::parse($validated['end_date'])->format('M d, Y'),
            default => 'Sales Report',
        };
    }

    private function inventoryPeriodLabel(array $validated)
    {
        if (!empty($validated['period']) && $validated['period'] !== 'custom') {
            return match ($validated['period']) {
                'daily' => 'Daily Inventory Report - ' . today()->format('F d, Y'),
                'monthly' => 'Monthly Inventory Report - ' . now()->format('F Y'),
                'yearly' => 'Yearly Inventory Report - ' . now()->format('Y'),
                default => 'Inventory Report',
            };
        }

        if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
            return 'Inventory Report - ' . Carbon::parse($validated['start_date'])->format('M d, Y') . ' to ' . Carbon::parse($validated['end_date'])->format('M d, Y');
        }

        if (!empty($validated['start_date'])) {
            return 'Inventory Report - From ' . Carbon::parse($validated['start_date'])->format('M d, Y');
        }

        if (!empty($validated['end_date'])) {
            return 'Inventory Report - Until ' . Carbon::parse($validated['end_date'])->format('M d, Y');
        }

        return 'Inventory Report - All Dates';
    }

    private function groupSalesByPeriod($transactions, $groupBy)
    {
        $grouped = [];
        
        foreach ($transactions as $transaction) {
            $date = Carbon::parse($transaction->created_at);
            
            switch ($groupBy) {
                case 'DAY':
                    $key = $date->format('Y-m-d');
                    break;
                case 'MONTH':
                    $key = $date->format('Y-m');
                    break;
                default:
                    $key = $date->format('Y-m-d');
            }
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'total' => 0,
                    'count' => 0,
                ];
            }
            
            $grouped[$key]['total'] += $transaction->total_amount;
            $grouped[$key]['count'] += 1;
        }
        
        ksort($grouped);
        
        return $grouped;
    }

    private function getTopSellingProducts($transactions)
    {
        $productSales = [];
        
        foreach ($transactions as $transaction) {
            foreach ($transaction->transactionItems as $item) {
                $productId = $item->product_id;
                $productName = optional($item->product)->name ?? 'Unknown product';
                
                if (!isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'name' => $productName,
                        'quantity_sold' => 0,
                        'total_revenue' => 0,
                    ];
                }
                
                $productSales[$productId]['quantity_sold'] += $item->quantity;
                $productSales[$productId]['total_revenue'] += $item->subtotal;
            }
        }
        
        // Sort by quantity sold
        usort($productSales, function($a, $b) {
            return $b['quantity_sold'] - $a['quantity_sold'];
        });
        
        return array_slice($productSales, 0, 10); // Top 10
    }

    private function groupInventoryMovementByDate($logs)
    {
        $grouped = [];

        foreach ($logs as $log) {
            $key = Carbon::parse($log->log_date)->format('Y-m-d');

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'in' => 0,
                    'out' => 0,
                ];
            }

            if ($log->type === 'IN') {
                $grouped[$key]['in'] += $log->quantity;
            } else {
                $grouped[$key]['out'] += $log->quantity;
            }
        }

        ksort($grouped);

        return $grouped;
    }
}
