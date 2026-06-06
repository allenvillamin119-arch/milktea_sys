@extends('layouts.main')

@section('title', 'Add Supply')
@section('page-title', 'Add New Supply Item')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">New Supply Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Supply Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required 
                               placeholder="e.g., Plastic Cup 16oz, Straw Regular, Cup Lid, Paper Bag">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="stock" class="form-label">Initial Stock <span class="text-danger">*</span></label>
                                <input type="number" min="0"
                                       class="form-control @error('stock') is-invalid @enderror"
                                       id="stock" name="stock" value="{{ old('stock', 0) }}" required>
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="price" class="form-label">Unit Price (₱) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('price') is-invalid @enderror"
                                       id="price" name="price" value="{{ old('price', 0) }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="low_stock_threshold" class="form-label">Low Stock Alert <span class="text-danger">*</span></label>
                                <input type="number" min="0"
                                       class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                       id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', 10) }}" required>
                                <small class="text-muted">Alert when stock falls to or below this number</small>
                                @error('low_stock_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active / Available for Use</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Information</h6>
                            <p class="text-muted mb-2">Supply items are non-sellable inventory items like:</p>
                            <ul class="text-muted mb-0" style="font-size: 0.875rem;">
                                <li>Cups (various sizes)</li>
                                <li>Straws</li>
                                <li>Cup lids</li>
                                <li>Paper bags</li>
                                <li>Napkins</li>
                                <li>Sealing film</li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This item will be used for inventory tracking and stock management.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Supply
                </button>
            </div>
        </form>
    </div>
</div>
@endsection