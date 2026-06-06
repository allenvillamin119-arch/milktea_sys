@extends('layouts.main')

@section('title', 'Stock Out')
@section('page-title', 'Stock Out - Reduce Inventory')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Reduce Stock from Inventory</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.stock-out') }}" method="POST">
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
                        <small class="text-muted">Maximum available: <span id="max-quantity">0</span></small>
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
                               placeholder="e.g., Damaged items, Expired products">
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Note:</strong> This will decrease the stock level of the selected product. Make sure the quantity is correct.
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-minus"></i> Reduce Stock
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('product_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const text = selectedOption.text;
        const match = text.match(/Current: (\d+)/);
        if (match) {
            document.getElementById('max-quantity').textContent = match[1];
        }
    } else {
        document.getElementById('max-quantity').textContent = '0';
    }
});
</script>
@endpush