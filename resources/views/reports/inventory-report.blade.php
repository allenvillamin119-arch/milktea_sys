@extends('layouts.main')

@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report - Analytics')

@section('content')
<!-- Charts Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<div class="row">
    <!-- Filter Form -->
    <div class="col-md-12 mb-4 no-print">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filter Inventory Report</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reports.inventory.generate') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Period</label>
                            <select class="form-select @error('period') is-invalid @enderror" name="period" id="period-select">
                                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                            @error('period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3" id="start-date-group" style="display: {{ $period == 'custom' ? 'block' : 'none' }};">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date', $startDate ?? '') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3" id="end-date-group" style="display: {{ $period == 'custom' ? 'block' : 'none' }};">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date', $endDate ?? '') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-refresh me-2"></i> Generate Report
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('inventory.stock-in') }}" class="btn btn-success w-100">
                                <i class="fas fa-arrow-down me-2"></i> Stock In
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards - KPIs -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Total Stock In</h6>
                        <h3 class="text-success mb-0">{{ $totalStockIn }}</h3>
                        <small class="text-muted">Units Added</small>
                    </div>
                    <i class="fas fa-arrow-down text-success fa-2x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Total Stock Out</h6>
                        <h3 class="text-danger mb-0">{{ $totalStockOut }}</h3>
                        <small class="text-muted">Units Removed</small>
                    </div>
                    <i class="fas fa-arrow-up text-danger fa-2x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Low Stock Items</h6>
                        <h3 class="text-warning mb-0">{{ $lowStockItems->count() }}</h3>
                        <small class="text-muted">Need Replenishment</small>
                    </div>
                    <i class="fas fa-exclamation-triangle text-warning fa-2x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">{{ $periodLabel }}</h5>
            <small class="text-muted">Generated {{ now()->format('M d, Y h:i A') }}</small>
        </div>
        <div class="btn-group btn-group-sm no-print">
            <a class="btn btn-outline-success" href="{{ route('reports.inventory.csv', ['period' => $period, 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                <i class="fas fa-file-csv me-1"></i> CSV
            </a>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Inventory Movement Chart -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Inventory Movement</h5>
            </div>
            <div class="card-body">
                @if(count($inventoryMovement) > 0)
                    <canvas id="inventoryMovementChart"></canvas>
                @else
                    <p class="text-muted text-center py-4">No inventory movement data</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Stock Status Distribution -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-pie-chart me-2"></i> Stock Status</h5>
            </div>
            <div class="card-body">
                @php
                    $inStock = $currentStock->filter(fn($item) => $item->stock > $item->low_stock_threshold)->count();
                    $lowStock = $currentStock->filter(fn($item) => $item->stock <= $item->low_stock_threshold && $item->stock > 0)->count();
                    $outOfStock = $currentStock->where('stock', '<=', 0)->count();
                @endphp
                <canvas id="stockStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>
@if($lowStockItems->count() > 0)
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Low Stock Alert</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockItems as $item)
                    <tr>
                        <td><strong>{{ $item->name }}</strong></td>
                        <td>{{ $item->category_name }}</td>
                        <td class="{{ $item->stock <= 0 ? 'text-danger fw-bold' : 'text-warning' }}">
                            {{ $item->stock }}
                            <small class="text-muted d-block">Low at {{ $item->low_stock_threshold }}</small>
                        </td>
                        <td>
                            @if($item->stock == 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($item->stock <= $item->low_stock_threshold)
                                <span class="badge bg-warning">Low</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Current Stock Levels -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Current Stock Levels</h5>
    </div>
    <div class="card-body">
        @if($currentStock->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currentStock as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td>{{ $item->category_name }}</td>
                            <td>{{ $item->item_type === 'sellable' ? 'POS Item' : 'Inventory Item' }}</td>
                            <td class="{{ $item->stock <= $item->low_stock_threshold ? 'text-danger' : 'text-success' }}">
                                {{ $item->stock }}
                                <small class="text-muted d-block">Low at {{ $item->low_stock_threshold }}</small>
                            </td>
                            <td>
                                @if($item->stock == 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($item->stock <= $item->low_stock_threshold)
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success">In Stock</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center py-4">No products in inventory</p>
        @endif
    </div>
</div>

<!-- Inventory Logs -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Inventory Transactions</h5>
    </div>
    <div class="card-body">
        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->log_date->format('M d, Y') }}</td>
                            <td><strong>{{ optional($log->product)->name ?? 'Unknown product' }}</strong></td>
                            <td>
                                @if($log->type == 'IN')
                                    <span class="badge bg-success">Stock In</span>
                                @else
                                    <span class="badge bg-warning">Stock Out</span>
                                @endif
                            </td>
                            <td>
                                <span class="{{ $log->type == 'IN' ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ $log->type == 'IN' ? '+' : '-' }}{{ $log->quantity }}
                                </span>
                            </td>
                            <td>{{ $log->reason ?? 'N/A' }}</td>
                            <td>{{ optional($log->user)->name ?? 'Unknown' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        @else
            <p class="text-muted text-center py-4">No inventory transactions found for the selected period</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('period-select').addEventListener('change', function() {
    const isCustom = this.value === 'custom';
    document.getElementById('start-date-group').style.display = isCustom ? 'block' : 'none';
    document.getElementById('end-date-group').style.display = isCustom ? 'block' : 'none';
});

@if(count($inventoryMovement) > 0)
const inventoryLabels = {!! json_encode(array_map(fn($date) => date('M d', strtotime($date)), array_keys($inventoryMovement))) !!};
const stockInData = {!! json_encode(array_values(array_map(fn($data) => $data['in'], $inventoryMovement))) !!};
const stockOutData = {!! json_encode(array_values(array_map(fn($data) => $data['out'], $inventoryMovement))) !!};

new Chart(document.getElementById('inventoryMovementChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: inventoryLabels,
        datasets: [
            {
                label: 'Stock In',
                data: stockInData,
                backgroundColor: '#10b981'
            },
            {
                label: 'Stock Out',
                data: stockOutData,
                backgroundColor: '#ef4444'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
@endif

new Chart(document.getElementById('stockStatusChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['In Stock', 'Low Stock', 'Out of Stock'],
        datasets: [{
            data: [{{ $inStock }}, {{ $lowStock }}, {{ $outOfStock }}],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
