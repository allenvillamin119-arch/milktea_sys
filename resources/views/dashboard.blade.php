@extends('layouts.main')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Today's Sales -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card sales h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Today's Sales</h6>
                        <h3 class="mb-0">&#8369;{{ number_format($todaySales, 2) }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-peso-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Transactions -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card transactions h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Today's Transactions</h6>
                        <h3 class="mb-0">{{ $todayTransactions }}</h3>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-receipt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Products -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card products h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Products</h6>
                        <h3 class="mb-0">{{ $totalProducts }}</h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-box fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card low-stock h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Low Stock Items</h6>
                        <h3 class="mb-0">{{ $lowStockProducts }}</h3>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Low Supplies Alert -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card supply-low h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Low Supplies</h6>
                        <h3 class="mb-0">{{ $lowSupplyProducts ?? 0 }}</h3>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-box-open fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Weekly & Monthly Sales -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Sales Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted">Weekly Sales</h6>
                            <h4 class="text-primary">&#8369;{{ number_format($weeklySales, 2) }}</h4>
                            <small class="text-muted">Last 7 days</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded">
                            <h6 class="text-muted">Monthly Sales</h6>
                            <h4 class="text-success">&#8369;{{ number_format($monthlySales, 2) }}</h4>
                            <small class="text-muted">This month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Transactions</h5>
                <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @if($recentTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Transaction #</th>
                                    <th>Amount</th>
                                    <th>Cashier</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_number }}</td>
                                    <td>&#8369;{{ number_format($transaction->total_amount, 2) }}</td>
                                    <td>{{ optional($transaction->user)->name ?? 'Unknown' }}</td>
                                    <td>{{ $transaction->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No transactions yet</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if(isset($lowSupplyList) && $lowSupplyList->count() > 0)
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Low Supply Details</h5>
                <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">View Inventory</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Supply</th>
                                <th>Stock</th>
                                <th>Low Threshold</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowSupplyList as $s)
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->stock }}</td>
                                <td>{{ $s->low_stock_threshold }}</td>
                                <td><a href="{{ route('products.supplies.edit', $s) }}" class="btn btn-sm btn-outline-primary">Manage</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <!-- Quick Actions -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('pos.index') }}" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-cash-register me-2"></i> New Sale
                        </a>
                    </div>
                    @if(auth()->user()->isAdmin())
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('products.create') }}" class="btn btn-outline-primary w-100 py-2">
                            <i class="fas fa-plus me-2"></i> Add Product
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('inventory.stock-in') }}" class="btn btn-outline-success w-100 py-2">
                            <i class="fas fa-arrow-down me-2"></i> Stock In
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('reports.sales') }}" class="btn btn-outline-info w-100 py-2">
                            <i class="fas fa-chart-bar me-2"></i> View Reports
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
