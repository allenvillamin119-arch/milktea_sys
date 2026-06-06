@extends('layouts.main')

@section('title', 'Products')
@section('page-title', 'Product Management')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Products</h5>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
    <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-auto">
                    <input type="text" name="q" value="{{ old('q', $q ?? '') }}" placeholder="Search product name..." class="form-control" />
                </div>
                <div class="col-auto">
                    <div style="position:relative;">
                        <input id="category-filter" type="text" placeholder="Search categories..." class="form-control" autocomplete="off">
                        <select id="category-select" name="category_id" class="form-select mt-2" style="min-width:240px; max-width:420px;">
                            <option value="">All categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (isset($categoryId) && $categoryId == $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                    <a href="{{ route('products.index') }}" class="btn btn-link">Reset</a>
                </div>
            </form>
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Supplies</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                @else
                                    <div style="width: 50px; height: 50px; background: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($product->supplies_count > 0)
                                    <span class="badge bg-info">Has Supplies</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td>
                                <span class="badge badge-category badge-{{ $product->category }}">
                                    {{ $product->category_name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $product->item_type === 'sellable' ? 'bg-primary' : 'bg-info' }}">
                                    {{ $product->item_type === 'sellable' ? 'POS Item' : 'Inventory Item' }}
                                </span>
                            </td>
                            <td>₱{{ number_format($product->price, 2) }}</td>
                            <td>
                                @php
                                    // hide stock display for supply category items and for sellable items (managed in POS)
                                    $isSupply = ($product->category ?? '') === 'supply';
                                    $isSellable = $product->item_type === 'sellable';
                                    $isInventoryLike = $product->item_type === 'inventory' && !$isSupply;
                                @endphp
                                @if($isSupply || $isSellable)
                                    <span class="text-muted">-</span>
                                @elseif($isInventoryLike)
                                    <span class="{{ $product->stock <= $product->low_stock_threshold ? 'text-danger' : 'text-success' }}">
                                        {{ $product->stock }}
                                        @if($product->stock <= $product->low_stock_threshold)
                                            <i class="fas fa-exclamation-triangle"></i>
                                        @endif
                                    </span>
                                    <small class="text-muted d-block">Low at {{ $product->low_stock_threshold }}</small>
                                @else
                                    <span class="text-muted">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->check() && auth()->user()->isAdmin())
                                    <a href="{{ route('products.supplies.edit', $product) }}" class="btn btn-outline-secondary" title="Manage Supplies">
                                        <i class="fas fa-layer-group"></i>
                                    </a>
                                    @endif
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" data-confirm="Are you sure you want to delete this product?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
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
            <script>
            // client-side category filter: type to narrow options
            (function(){
                const input = document.getElementById('category-filter');
                const select = document.getElementById('category-select');
                if (!input || !select) return;

                // keep original options
                const options = Array.from(select.options).map(o => ({ value: o.value, text: o.text }));

                input.addEventListener('input', function() {
                    const q = this.value.toLowerCase();
                    // rebuild options
                    select.innerHTML = '';
                    const allOpt = document.createElement('option'); allOpt.value=''; allOpt.text='All categories'; select.appendChild(allOpt);
                    options.forEach(opt => {
                        if (!q || opt.text.toLowerCase().includes(q)) {
                            const el = document.createElement('option');
                            el.value = opt.value; el.text = opt.text; select.appendChild(el);
                        }
                    });
                });
            })();
            </script>
            
            <div class="mt-3">
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h5>No products found</h5>
                <p class="text-muted">Start by adding your first product</p>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
