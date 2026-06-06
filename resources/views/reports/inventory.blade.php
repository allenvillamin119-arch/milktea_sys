@extends('layouts.main')

@section('title', 'Inventory Reports')
@section('page-title', 'Inventory Reports')

@section('content')
<div class="row">
    <!-- Filter Form -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Filter Inventory Report</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reports.inventory.generate') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Period</label>
                            <select class="form-select @error('period') is-invalid @enderror" name="period" id="period-select">
                                <option value="daily" {{ old('period') == 'daily' ? 'selected' : '' }}>Daily</option>
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
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3" id="end-date-group" style="display: {{ old('period') == 'custom' ? 'block' : 'none' }};">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Generate Report
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('inventory.stock-in') }}" class="btn btn-success w-100">
                                <i class="fas fa-plus me-2"></i> Stock In
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-muted">Total Stock In</h6>
                    <h3 class="text-success">0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-muted">Total Stock Out</h6>
                    <h3 class="text-danger">0</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-muted">Low Stock Items</h6>
                    <h3 class="text-warning">0</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Select date range and click "Generate Report" to view inventory data</h5>
    </div>
    <div class="card-body text-center py-5">
        <i class="fas fa-warehouse fa-4x text-muted mb-3"></i>
        <p class="text-muted">Inventory report will be displayed here</p>
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
</script>
@endpush
