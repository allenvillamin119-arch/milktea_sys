@extends('layouts.main')

@section('title', 'Product Details')
@section('page-title', $product->name)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Product Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded" alt="{{ $product->name }}">
                        @else
                            <div class="bg-light rounded p-5 text-center">
                                <i class="fas fa-box text-secondary" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h3 class="mb-3">{{ $product->name }}</h3>
                        
                        <div class="mb-3">
                            <label class="text-muted">Category</label>
                            <p class="fs-5">
                                <span class="badge bg-info">
                                    {{ ucfirst(str_replace('_', ' ', $product->category)) }}
                                </span>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted">Price</label>
                            <p class="fs-5 fw-bold text-success">₱{{ number_format($product->price, 2) }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted">Current Stock</label>
                            <p class="fs-5">
                                @php
                                    $isSellable = isset($product->item_type) && $product->item_type === 'sellable';
                                    $isSupply = isset($product->category) && $product->category === 'supply';
                                    $showStock = !$isSellable; // show stock for inventory items and supplies
                                @endphp

                                @if(!$showStock)
                                    <span class="text-muted">-</span>
                                @else
                                    @if($product->stock > 10)
                                        <span class="badge bg-success">{{ $product->stock }} units</span>
                                    @elseif($product->stock > 0)
                                        <span class="badge bg-warning">{{ $product->stock }} units (Low Stock)</span>
                                    @else
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted">Status</label>
                            <p class="fs-5">
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                        
                        @if($product->description)
                            <div class="mb-3">
                                <label class="text-muted">Description</label>
                                <p>{{ $product->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Actions</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Product
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST" data-confirm="Are you sure?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash"></i> Delete Product
                    </button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Product Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted d-block">Created</small>
                    <small>{{ $product->created_at->format('M d, Y H:i') }}</small>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Last Updated</small>
                    <small>{{ $product->updated_at->format('M d, Y H:i') }}</small>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Product ID</small>
                    <small>#{{ $product->id }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
