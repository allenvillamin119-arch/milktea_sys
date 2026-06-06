@extends('layouts.main')

@section('title', 'Sales Reports')
@section('page-title', 'Sales Reports')

@section('content')
<div class="row">
    <!-- Filter Form -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filter Sales Report</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reports.sales.generate') }}" method="GET" id="sales-filter-form">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Period</label>
                            <select class="form-select @error('period') is-invalid @enderror" name="period" id="period-select">
                                <option value="daily" {{ old('period') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ old('period') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ old('period', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ old('period') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="custom" {{ old('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                            @error('period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3" id="start-date-group" style="display: {{ old('period') == 'custom' ? 'block' : 'none' }};">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" id="start_date" value="{{ old('start_date') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3" id="end-date-group" style="display: {{ old('period') == 'custom' ? 'block' : 'none' }};">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" id="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Generate Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card sales h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-muted">Total Sales</h6>
                    <h3 class="text-success">&#8369;0.00</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card stat-card transactions h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-muted">Transactions</h6>
                    <h3>0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card stat-card products h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-muted">Avg. Transaction</h6>
                    <h3>&#8369;0.00</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card stat-card low-stock h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-muted">Top Product</h6>
                    <h5 class="text-primary">-</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Select a filter and click "Generate Report" to view sales data</h5>
    </div>
    <div class="card-body text-center py-5">
        <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
        <p class="text-muted">Sales report will be displayed here</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

// Prevent page jump on form submit - scroll to filter section after submit
document.getElementById('sales-filter-form').addEventListener('submit', function() {
    setTimeout(function() {
        document.querySelector('.card-header h5').scrollIntoView({ behavior: 'smooth' });
    }, 100);
});
</script>
@endpush
