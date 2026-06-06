@extends('layouts.main')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report - Analytics')

@section('content')
<!-- Charts Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

@php
    // Defensive defaults so the report view can render without errors when data is missing
    $groupedSales = $groupedSales ?? [];
    $topProducts = $topProducts ?? [];
    $paymentMethods = $paymentMethods ?? collect();
    $transactions = $transactions ?? collect();
    $totalSales = $totalSales ?? 0;
    $totalTransactions = $totalTransactions ?? 0;
    $averageTransaction = $averageTransaction ?? 0;
    $period = $period ?? 'custom';
    $periodLabel = $periodLabel ?? 'Sales Report';
    $startDate = $startDate ?? null;
    $endDate = $endDate ?? null;
    $transactionSearch = $transactionSearch ?? null;
    $paymentMethod = $paymentMethod ?? null;
    $cashierId = $cashierId ?? null;
    $cashiers = $cashiers ?? collect();
    if (!is_array($topProducts) && $topProducts instanceof \Illuminate\Support\Collection) {
        $topProducts = $topProducts->toArray();
    }
@endphp

<div class="row">
    <!-- Filter Form -->
    <div class="col-md-12 mb-4 no-print">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filter Sales Report</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reports.sales.generate') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Period</label>
                            <select class="form-select @error('period') is-invalid @enderror" name="period" id="period-select">
                                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
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
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" id="start_date" value="{{ old('start_date', $startDate ?? '') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3" id="end-date-group" style="display: {{ $period == 'custom' ? 'block' : 'none' }};">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" id="end_date" value="{{ old('end_date', $endDate ?? '') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-refresh me-2"></i> Generate Report
                            </button>
                        </div>
                    </div>

                    <div class="row align-items-end mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Transaction / Cashier Search</label>
                            <input type="text"
                                   class="form-control @error('transaction_search') is-invalid @enderror"
                                   name="transaction_search"
                                   value="{{ old('transaction_search', $transactionSearch ?? '') }}"
                                   placeholder="Transaction # or cashier name">
                            @error('transaction_search')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" name="payment_method">
                                <option value="">All Methods</option>
                                <option value="cash" {{ ($paymentMethod ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="gcash" {{ ($paymentMethod ?? '') == 'gcash' ? 'selected' : '' }}>GCash</option>
                                <option value="paymaya" {{ ($paymentMethod ?? '') == 'paymaya' ? 'selected' : '' }}>PayMaya</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cashier</label>
                            <select class="form-select @error('cashier_id') is-invalid @enderror" name="cashier_id">
                                <option value="">All Cashiers</option>
                                @foreach($cashiers as $cashier)
                                    <option value="{{ $cashier->id }}" {{ (string) ($cashierId ?? '') === (string) $cashier->id ? 'selected' : '' }}>
                                        {{ $cashier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cashier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('reports.sales.generate', ['period' => $period, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-secondary w-100">
                                Clear Details
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards - KPIs -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card sales h-100 border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Total Sales</h6>
                        <h3 class="text-success mb-0">&#8369;{{ number_format($totalSales, 2) }}</h3>
                        <small class="text-muted">Revenue</small>
                    </div>
                    <i class="fas fa-chart-line text-success fa-2x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card stat-card transactions h-100 border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Transactions</h6>
                        <h3 class="text-info mb-0">{{ $totalTransactions }}</h3>
                        <small class="text-muted">Total Orders</small>
                    </div>
                    <i class="fas fa-receipt text-info fa-2x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card stat-card products h-100 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Avg. Transaction</h6>
                        <h3 class="text-primary mb-0">&#8369;{{ number_format($averageTransaction, 2) }}</h3>
                        <small class="text-muted">Per Order</small>
                    </div>
                    <i class="fas fa-calculator text-primary fa-2x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card stat-card h-100 border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Top Product</h6>
                        <h5 class="text-warning mb-0">{{ $topProducts[0]['name'] ?? '-' }}</h5>
                        <small class="text-muted">{{ $topProducts[0]['quantity_sold'] ?? 0 }} units</small>
                    </div>
                    <i class="fas fa-star text-warning fa-2x opacity-25"></i>
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
            <a class="btn btn-outline-success" href="{{ route('reports.sales.csv', ['period' => $period, 'start_date' => $startDate, 'end_date' => $endDate, 'transaction_search' => $transactionSearch, 'payment_method' => $paymentMethod, 'cashier_id' => $cashierId]) }}">
                <i class="fas fa-file-csv me-1"></i> CSV
            </a>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sales Trend Chart -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Sales Trend</h5>
            </div>
            <div class="card-body">
                @if(count($groupedSales) > 0)
                    <canvas id="salesTrendChart"></canvas>
                @else
                    <p class="text-muted text-center py-4">No sales data available</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i> Payment Methods</h5>
            </div>
            <div class="card-body">
                @if($paymentMethods->count() > 0)
                    <canvas id="paymentMethodChart"></canvas>
                @else
                    <p class="text-muted text-center py-4">No payment data</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sales Breakdown Table -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i> Sales Breakdown</h5>
            </div>
            <div class="card-body">
                @if(count($groupedSales) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th class="text-end">Transactions</th>
                                    <th class="text-end">Total Sales</th>
                                    <th class="text-end">Avg. Transaction</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedSales as $date => $data)
                                <tr>
                                    <td>{{ $period == 'yearly' ? date('M Y', strtotime($date . '-01')) : date('M d, Y', strtotime($date)) }}</td>
                                    <td class="text-end">{{ $data['count'] }}</td>
                                    <td class="text-end fw-bold text-success">&#8369;{{ number_format($data['total'], 2) }}</td>
                                    <td class="text-end">&#8369;{{ number_format($data['count'] > 0 ? $data['total'] / $data['count'] : 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <td><strong>Total</strong></td>
                                    <td class="text-end"><strong>{{ $totalTransactions }}</strong></td>
                                    <td class="text-end"><strong>&#8369;{{ number_format($totalSales, 2) }}</strong></td>
                                    <td class="text-end"><strong>&#8369;{{ number_format($averageTransaction, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No sales data for the selected period</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i> Top Selling Products</h5>
            </div>
            <div class="card-body">
                @if(count($topProducts) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topProducts as $index => $product)
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                    <strong>{{ $product['name'] }}</strong>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 small">
                                <span class="text-success">{{ $product['quantity_sold'] }} sold</span>
                                <span class="fw-bold">&#8369;{{ number_format($product['total_revenue'], 2) }}</span>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: {{ ($product['quantity_sold'] / ($topProducts[0]['quantity_sold'] ?? 1)) * 100 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-4">No product sales data</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Transaction List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i> Transaction Details</h5>
            <small class="text-muted">{{ $transactions->total() }} matching transactions</small>
        </div>
    </div>
    <div class="card-body">
        @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Transaction #</th>
                            <th>Date & Time</th>
                            <th>Cashier</th>
                            <th>Payment Method</th>
                            <th>Items</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr>
                            <td><strong>{{ $transaction->transaction_number }}</strong></td>
                            <td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ optional($transaction->user)->name ?? 'Unknown' }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->payment_method == 'cash' ? 'success' : ($transaction->payment_method == 'gcash' ? 'info' : 'warning') }}">
                                    {{ ucfirst($transaction->payment_method) }}
                                </span>
                            </td>
                            <td>{{ $transaction->transactionItems->count() }} items</td>
                            <td class="text-end fw-bold">&#8369;{{ number_format($transaction->total_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        @else
            <p class="text-muted text-center py-4">No transactions found for the selected period</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Sales Trend Chart
@if(count($groupedSales) > 0)
const labels = {!! json_encode(array_map(fn($date) => $period == 'yearly' ? date('M Y', strtotime($date . '-01')) : date('M d', strtotime($date)), array_keys($groupedSales))) !!};
const salesData = {!! json_encode(array_values(array_map(fn($data) => $data['total'], $groupedSales))) !!};
const transactionData = {!! json_encode(array_values(array_map(fn($data) => $data['count'], $groupedSales))) !!};

const ctx = document.getElementById('salesTrendChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Sales (PHP)',
                data: salesData,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                yAxisID: 'y'
            },
            {
                label: 'Transactions',
                data: transactionData,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Sales (PHP)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Transactions'
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});
@endif

// Payment Methods Chart
@if($paymentMethods->count() > 0)
const paymentLabels = {!! json_encode($paymentMethods->keys()->map(fn($method) => ucfirst($method))->toArray()) !!};
const paymentData = {!! json_encode($paymentMethods->map(fn($items) => $items->sum('total_amount'))->values()->toArray()) !!};

const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: paymentLabels,
        datasets: [{
            data: paymentData,
            backgroundColor: [
                '#10b981',
                '#3b82f6',
                '#f59e0b',
                '#ef4444'
            ],
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
@endif

// Period selector toggle
document.getElementById('period-select').addEventListener('change', function() {
    const startDateGroup = document.getElementById('start-date-group');
    const endDateGroup = document.getElementById('end-date-group');
    
    if (this.value === 'custom') {
        startDateGroup.style.display = 'block';
        endDateGroup.style.display = 'block';
    } else {
        startDateGroup.style.display = 'none';
        endDateGroup.style.display = 'none';
    }
});
</script>
@endpush
