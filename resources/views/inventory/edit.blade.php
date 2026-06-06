@extends('layouts.main')

@section('title', 'Edit Supply')
@section('page-title', 'Edit Supply: ' . $product->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Supply Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Supply Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        <small class="text-muted">Examples: Plastic Cup 16oz, Straw Regular, Cup Lid, Paper Bag</small>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stock" class="form-label">Current Stock <span class="text-danger">*</span></label>
                                <input type="number" min="0"
                                       class="form-control @error('stock') is-invalid @enderror"
                                       id="stock" name="stock" value="{{ old('stock', $product->stock) }}" required>
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="low_stock_threshold" class="form-label">Low Stock Alert Threshold <span class="text-danger">*</span></label>
                                <input type="number" min="0"
                                       class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                       id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" required>
                                <small class="text-muted">Alert when stock falls to or below this number</small>
                                @error('low_stock_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active / Available for Use</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('inventory.stock-in') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add Stock
                                </a>
                                <a href="{{ route('inventory.stock-out') }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-minus me-1"></i> Remove Stock
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Current Status:</strong><br>
                        Stock: <strong>{{ $product->stock }}</strong><br>
                        Status: <strong>{{ $product->stock <= $product->low_stock_threshold ? 'Low Stock' : 'In Stock' }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Supply
                </button>
            </div>
        </form>
    </div>
</div>
@endsection