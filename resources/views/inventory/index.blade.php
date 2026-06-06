@extends('layouts.main')

@section('title', 'Inventory')
@section('page-title', 'Inventory Management')

@section('content')
<div class="row">
    <!-- Quick Actions -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <a href="{{ route('inventory.create') }}" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-plus me-2"></i> Add New Supply
                        </a>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <a href="{{ route('inventory.stock-in') }}" class="btn btn-success w-100 py-2">
                            <i class="fas fa-arrow-down me-2"></i> Stock In
                        </a>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <a href="{{ route('inventory.stock-out') }}" class="btn btn-warning w-100 py-2">
                            <i class="fas fa-arrow-up me-2"></i> Stock Out
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('inventory.history') }}" class="btn btn-info w-100 py-2">
                            <i class="fas fa-history me-2"></i> View History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="col-md-12 mb-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supply name..." class="form-control" />
            </div>
            <div class="col-auto">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="active" class="form-select">
                    <option value="">All Items</option>
                    <option value="true" {{ request('active') == 'true' ? 'selected' : '' }}>Active Only</option>
                    <option value="false" {{ request('active') == 'false' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('inventory.index') }}" class="btn btn-link">Reset</a>
            </div>
        </form>
    </div>

    <!-- Product List -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Current Inventory</h5>
                <span class="badge bg-primary">{{ $products->total() }} items</span>
            </div>
            <div class="card-body">
                @if($products->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($product->image)
                                                <img src="{{ asset('storage/' . $product->image) }}" 
                                                     alt="{{ $product->name }}" 
                                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; margin-right: 10px;">
                                            @endif
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                @if(!$product->is_active)
                                                    <span class="badge bg-secondary ms-1">Inactive</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Supply</span>
                                    </td>
                                    <td>
                                        <span class="{{ $product->stock <= $product->low_stock_threshold ? 'text-danger fw-bold' : 'text-success' }}">
                                            {{ $product->stock }}
                                            @if($product->stock <= $product->low_stock_threshold)
                                                <i class="fas fa-exclamation-triangle ms-1"></i>
                                            @endif
                                        </span>
                                        <small class="text-muted d-block">Low at {{ $product->low_stock_threshold }}</small>
                                    </td>
                                    <td>₱{{ number_format($product->price, 2) }}</td>
                                    <td>
                                        @if($product->stock == 0)
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @elseif($product->stock <= $product->low_stock_threshold)
                                            <span class="badge bg-warning">Low Stock</span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->updated_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('inventory.edit', $product) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('inventory.stock-in') }}?product_id={{ $product->id }}" class="btn btn-outline-success" title="Add Stock">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <a href="{{ route('inventory.stock-out') }}?product_id={{ $product->id }}" class="btn btn-outline-warning" title="Remove Stock">
                                                <i class="fas fa-minus"></i>
                                            </a>
                                            <form action="{{ route('inventory.destroy', $product) }}" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this supply item? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h5>No supply items in inventory</h5>
                        <p class="text-muted">Add supply items to start managing your inventory</p>
                        <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Supply
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
