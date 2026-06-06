@extends('layouts.main')

@section('title', 'Inventory History')
@section('page-title', 'Inventory Transaction History')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Inventory Logs</h5>
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
                            <td>
                                <strong>{{ $log->product->name }}</strong>
                                <br>
                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $log->product->category)) }}</small>
                            </td>
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
                            <td>{{ $log->user->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-history fa-4x text-muted mb-3"></i>
                <h5>No inventory logs found</h5>
                <p class="text-muted">Inventory transactions will be recorded here</p>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <a href="{{ route('inventory.stock-in') }}" class="btn btn-success w-100">
                            <i class="fas fa-arrow-down me-2"></i> Add Stock
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('inventory.stock-out') }}" class="btn btn-warning w-100">
                            <i class="fas fa-arrow-up me-2"></i> Reduce Stock
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection