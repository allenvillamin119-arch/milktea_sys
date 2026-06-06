<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Today's sales
        $todaySales = Transaction::whereDate('created_at', today())
            ->sum('total_amount');

        // Today's transactions count
        $todayTransactions = Transaction::whereDate('created_at', today())->count();

        // Total products
        $totalProducts = Product::where('is_active', true)->count();

        // Low stock products
        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->count();

        // Low supply products (for admin dashboard)
        $lowSupplyProducts = Product::where('category', 'supply')
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->count();

        $lowSupplyList = Product::where('category', 'supply')
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        // Weekly sales (last 7 days)
        $weeklySales = Transaction::whereBetween('created_at', [
            now()->subDays(7),
            now()
        ])->sum('total_amount');

        // Monthly sales (current month)
        $monthlySales = Transaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        // Recent transactions
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'todaySales',
            'todayTransactions',
            'totalProducts',
            'lowStockProducts',
            'weeklySales',
            'monthlySales',
            'recentTransactions'
        ));
    }
}
