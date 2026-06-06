@extends('layouts.main')

@section('title', 'Stock In')
@section('page-title', 'Stock In - Add Inventory')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Add Stock to Inventory</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.stock-in') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Select Product <span class="text-danger">*</span></label>
                        <select class="form-select @error('product_id') is-invalid @enderror" 
                                id="product_id" name="product_id" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} (Current: {{ $product->stock }}) - {{ ucfirst(str_replace('_', ' ', $product->category)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" 
                               class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="log_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control @error('log_date') is-invalid @enderror" 
                               id="log_date" name="log_date" value="{{ old('log_date', date('Y-m-d')) }}" required>
                        @error('log_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <input type="text" 
                               class="form-control @error('reason') is-invalid @enderror" 
                               id="reason" name="reason" value="{{ old('reason') }}" 
                               placeholder="e.g., New shipment received">
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> This will increase the stock level of the selected product.
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Stock
                </button>
            </div>
        </form>
    </div>
</div>
@endsection