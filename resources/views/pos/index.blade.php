@extends('layouts.main')

@section('title', 'POS System')
@section('page-title', 'Point of Sale - Multiple Orders')

@section('content')
<style>
    .order-tabs {
        gap: 5px;
        overflow-x: auto;
        padding-bottom: 10px;
        white-space: nowrap;
    }
    
    .order-tab {
        padding: 8px 15px;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s ease;
        min-width: 120px;
        text-align: center;
    }

    .product-card .card-title {
        min-height: 2.4em;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    .product-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 1px solid var(--border);
    }

    .product-card:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 12px 32px rgba(122, 75, 43, 0.15);
        border-color: rgba(122, 75, 43, 0.2);
    }

    .product-card:active {
        transform: translateY(-1px) scale(1.01);
    }

    .product-card img,
    .product-card .product-placeholder {
        max-width: 72px;
        max-height: 72px;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .product-card:hover img,
    .product-card:hover .product-placeholder {
        transform: scale(1.1) rotate(3deg);
    }

    /* Product grid animation */
    .product-item {
        animation: fadeInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        opacity: 0;
    }

    .product-item:nth-child(1) { animation-delay: 0.05s; }
    .product-item:nth-child(2) { animation-delay: 0.1s; }
    .product-item:nth-child(3) { animation-delay: 0.15s; }
    .product-item:nth-child(4) { animation-delay: 0.2s; }
    .product-item:nth-child(5) { animation-delay: 0.25s; }
    .product-item:nth-child(6) { animation-delay: 0.3s; }
    .product-item:nth-child(7) { animation-delay: 0.35s; }
    .product-item:nth-child(8) { animation-delay: 0.4s; }
    .product-item:nth-child(9) { animation-delay: 0.45s; }
    .product-item:nth-child(10) { animation-delay: 0.5s; }
    .product-item:nth-child(11) { animation-delay: 0.55s; }
    .product-item:nth-child(12) { animation-delay: 0.6s; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    #product-grid {
        align-items: stretch;
    }

    .pos-filter-tools {
        min-width: 0;
    }

    .cart-item {
        gap: 10px;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .pos-filter-tools,
        .pos-filter-tools .position-relative,
        .pos-filter-tools .btn-group {
            width: 100%;
        }

        .pos-filter-tools .btn-group {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: .25rem;
            -webkit-overflow-scrolling: touch;
        }

        .pos-filter-tools .btn-group .btn {
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .product-scroll {
            max-height: none;
            overflow-y: visible;
        }

        .cart-scroll,
        .queue-scroll {
            max-height: 360px;
        }
    }

    @media (max-width: 576px) {
        .order-tab {
            min-width: 96px;
            padding: 7px 10px;
        }

        #cart-items {
            max-height: 320px !important;
        }

        .product-card img,
        .product-card .product-placeholder {
            max-width: 58px;
            max-height: 58px;
        }

        .product-card .card-title {
            font-size: .86rem;
        }

        .product-card .card-text,
        .product-card small {
            font-size: .8rem;
        }

        .cart-item {
            align-items: flex-start !important;
        }

        .cart-item > .d-flex {
            width: 100%;
            justify-content: flex-start;
        }
    }
    
    .order-tab:hover {
        border-color: #6366f1;
        background: #f0f4ff;
    }
    
    .order-tab.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }
    
    .order-tab .badge {
        font-size: 11px;
        margin-left: 5px;
    }
    
    .order-queue-item {
        padding: 10px;
        border-left: 3px solid #6366f1;
        background: #f8f9fa;
        margin-bottom: 8px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .order-queue-item:hover {
        background: #e9ecef;
    }
    
    .order-queue-item.active {
        background: #e0e7ff;
        border-left-color: #4f46e5;
    }
    
    .order-status-badge {
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .cart-item {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }

    /* Scrolling helpers to keep POS layout usable on small screens / many items */
    .product-scroll { max-height: calc(100vh - 320px); overflow-y: auto; }
    .cart-scroll { max-height: calc(100vh - 360px); overflow-y: auto; padding: 0.75rem; }
    .queue-scroll { max-height: calc(100vh - 360px); overflow-y: auto; }
    @media (max-width: 768px) {
        .product-scroll { max-height: calc(100vh - 420px); }
        .cart-scroll, .queue-scroll { max-height: 320px; }
    }
</style>

<div class="row">
    <!-- Product Grid -->
    <div class="col-md-8">
        <!-- Order Tabs -->
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Orders</h5>
                    <button class="btn btn-sm btn-success" onclick="createNewOrder()">
                        <i class="fas fa-plus me-1"></i> New Order
                    </button>
                </div>
            </div>
            <div class="card-body pb-0">
                <div class="order-tabs d-flex" id="order-tabs">
                    <!-- Order tabs will be generated here -->
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">Products</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap pos-filter-tools">
                        <!-- Search Bar -->
                        <div class="position-relative" style="min-width: 200px;">
                            <input type="text" class="form-control form-control-sm" id="product-search" 
                                   placeholder="Search products..." style="padding-right: 30px;">
                            <i class="fas fa-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
                        </div>
                        <!-- Category Buttons -->
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary active" data-category="all">All</button>
                            @foreach($categories as $category)
                                <button class="btn btn-sm btn-outline-primary" data-category="{{ $category->slug }}">{{ $category->name }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body product-scroll">
                <div id="product-grid" class="row g-3">
                    @foreach($products as $category => $categoryProducts)
                        @foreach($categoryProducts as $product)
                            <div class="col-6 col-lg-3 product-item" data-category="{{ $category }}">
                                <div class="card product-card h-100" onclick="addToCart({{ $product->id }}, @js($product->name), {{ $product->price }}, {{ $product->stock }})">
                                    <div class="card-body text-center p-2">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" 
                                                 alt="{{ $product->name }}" 
                                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 8px;">
                                        @else
                                            <div style="width: 80px; height: 80px; background: #e9ecef; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                                <i class="fas fa-{{ $category == 'milk_tea' ? 'mug-hot' : ($category == 'topping' ? 'cookie' : 'box') }} fa-2x text-muted"></i>
                                            </div>
                                        @endif
                                        <h6 class="card-title mb-1">{{ $product->name }}</h6>
                                        <p class="card-text text-primary mb-1">₱{{ number_format($product->price, 2) }}</p>
                                        @php
                                            $isSellable = isset($product->item_type) && $product->item_type === 'sellable';
                                            $isSupply = isset($product->category) && $product->category === 'supply';
                                            $showStock = !$isSellable; // show stock for inventory and supplies
                                        @endphp
                                        <small class="text-muted">Stock: {{ $showStock ? $product->stock : '-' }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar: Active Order + Order Queue -->
    <div class="col-md-4">
        <!-- Active Cart -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order #<span id="current-order-id">1</span></h5>
                    <small id="order-time">00:00</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="cart-items" class="cart-scroll">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                        <p>Cart is empty</p>
                        <small>Click products to add</small>
                    </div>
                </div>
            </div>
            <div class="card-body border-top">
                <div class="row mb-2">
                    <div class="col-6">Subtotal:</div>
                    <div class="col-6 text-end">
                        <strong id="subtotal">₱0.00</strong>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">Total:</div>
                    <div class="col-6 text-end">
                        <strong class="text-primary fs-5" id="total">₱0.00</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select class="form-select" id="payment-method">
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="paymaya">PayMaya</option>
                    </select>
                </div>

                <div class="mb-3" id="cash-section">
                    <label class="form-label">Cash Received</label>
                    <input type="number" class="form-control" id="cash-received" 
                           placeholder="Enter amount" min="0" step="0.01">
                </div>

                <div class="row mb-3" id="change-section" style="display: none;">
                    <div class="col-6">Change:</div>
                    <div class="col-6 text-end">
                        <strong class="text-success" id="change">₱0.00</strong>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary py-2" id="checkout-btn" onclick="processCheckout()" disabled>
                        <i class="fas fa-cash-register me-2"></i> Process Payment
                    </button>
                    <button class="btn btn-warning py-2" id="hold-btn" onclick="holdOrder()">
                        <i class="fas fa-pause me-2"></i> Hold Order
                    </button>
                    <button class="btn btn-outline-danger py-2" data-bs-toggle="modal" data-bs-target="#voidModal">
                        <i class="fas fa-ban me-2"></i> Void Transaction
                    </button>
                </div>
            </div>
        </div>

        <!-- Order Queue -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Queue (<span id="queue-count">0</span>)</h5>
            </div>
            <div class="card-body">
                <div id="order-queue" class="queue-scroll">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No held orders</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Void Transaction Modal -->
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Void Transaction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Transaction Number</label>
                    <input type="text" class="form-control" id="void-transaction-number" placeholder="TXN-YYYYMMDD-0001">
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <textarea class="form-control" id="void-reason" rows="3" placeholder="Customer cancelled, wrong order, duplicate transaction..." maxlength="255"></textarea>
                </div>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Voiding restores product stock and records an inventory log.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" id="void-submit-btn" onclick="voidTransaction()">
                    <i class="fas fa-ban me-2"></i> Confirm Void
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Transaction Successful!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4>Transaction #<span id="modal-transaction-number"></span></h4>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><strong>Total Amount:</strong></div>
                    <div class="col-6 text-end"><span id="modal-total"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><strong>Cash Received:</strong></div>
                    <div class="col-6 text-end"><span id="modal-cash"></span></div>
                </div>
                <div class="row">
                    <div class="col-6"><strong>Change:</strong></div>
                    <div class="col-6 text-end text-success"><span id="modal-change"></span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" onclick="printReceipt()">
                    <i class="fas fa-print me-2"></i> Print Receipt
                </button>
                <button class="btn btn-primary" data-bs-dismiss="modal" onclick="startNewTransaction()">
                    New Transaction
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let orders = [];
let currentOrderId = null;
let lastTransactionId = null;
let nextOrderId = 1;

// Initialize first order
function initializeOrders() {
    createNewOrder();
}

// Create new order
function createNewOrder() {
    const orderId = nextOrderId++;
    orders.push({
        id: orderId,
        items: [],
        createdAt: new Date(),
        status: 'active'
    });
    switchToOrder(orderId);
}


// Switch between orders
function switchToOrder(orderId) {
    currentOrderId = orderId;
    renderOrderTabs();
    renderOrderQueue();
    renderCart();
    updateOrderTimer();
    resetPaymentFields();
}

// Reset payment inputs and UI for a fresh checkout
function resetPaymentFields() {
    const cashInput = document.getElementById('cash-received');
    if (cashInput) {
        cashInput.value = '';
    }

    const changeEl = document.getElementById('change');
    if (changeEl) {
        changeEl.textContent = '';
    }

    const changeSection = document.getElementById('change-section');
    if (changeSection) {
        changeSection.style.display = 'none';
    }

    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="fas fa-cash-register me-2"></i> Process Payment';
    }
}

// Hold current order
function holdOrder() {
    const currentOrder = orders.find(o => o.id === currentOrderId);
    if (!currentOrder || currentOrder.items.length === 0) return;

    currentOrder.status = 'held';

    // Switch to first active order or create new one
    const activeOrder = orders.find(o => o.status === 'active');
    if (activeOrder) {
        switchToOrder(activeOrder.id);
    } else {
        createNewOrder();
    }

    showNotification('Order #' + currentOrder.id + ' held');
}

// Render order tabs
function renderOrderTabs() {
    const tabsContainer = document.getElementById('order-tabs');
    let html = '';
    
    orders.forEach(order => {
        const itemCount = order.items.length;
        const isActive = order.status === 'active' && order.id === currentOrderId;
        const statusClass = isActive ? 'active' : (order.status === 'held' ? '' : '');
        
        html += `
            <div class="order-tab ${statusClass}" onclick="switchToOrder(${order.id})">
                Order #${order.id}
                ${itemCount > 0 ? `<span class="badge bg-danger">${itemCount}</span>` : ''}
            </div>
        `;
    });
    
    tabsContainer.innerHTML = html;
}

// Render order queue (held orders)
function renderOrderQueue() {
    const queueContainer = document.getElementById('order-queue');
    const heldOrders = orders.filter(o => o.status === 'held');
    const count = heldOrders.length;
    
    document.getElementById('queue-count').textContent = count;
    
    if (count === 0) {
        queueContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>No held orders</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    heldOrders.forEach(order => {
        const total = order.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        html += `
            <div class="order-queue-item ${currentOrderId === order.id ? 'active' : ''}" onclick="switchToOrder(${order.id})">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #${order.id}</strong>
                        <br>
                        <small class="text-muted">${order.items.length} items</small>
                    </div>
                    <div class="text-end">
                        <strong>₱${total.toFixed(2)}</strong>
                        <br>
                        <span class="badge bg-warning order-status-badge">Held</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    queueContainer.innerHTML = html;
}

// Search functionality
document.getElementById('product-search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const activeCategoryBtn = document.querySelector('.btn-group .btn.active');
    const activeCategory = activeCategoryBtn ? activeCategoryBtn.dataset.category : 'all';
    
    document.querySelectorAll('.product-item').forEach(item => {
        const productName = item.querySelector('.card-title').textContent.toLowerCase();
        const productCategory = item.dataset.category;
        
        const matchesSearch = productName.includes(searchTerm);
        const matchesCategory = activeCategory === 'all' || productCategory === activeCategory;
        
        item.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
    });
});

// Category filter
document.querySelectorAll('.btn-group .btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.dataset.category;
        const searchTerm = document.getElementById('product-search').value.toLowerCase().trim();
        
        document.querySelectorAll('.product-item').forEach(item => {
            const productName = item.querySelector('.card-title').textContent.toLowerCase();
            const productCategory = item.dataset.category;
            
            const matchesSearch = productName.includes(searchTerm);
            const matchesCategory = category === 'all' || productCategory === category;
            
            item.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
        });
    });
});

// Cash received input
document.getElementById('cash-received').addEventListener('input', function() {
    const cash = parseFloat(this.value) || 0;
    const currentOrder = orders.find(o => o.id === currentOrderId);
    const total = currentOrder ? currentOrder.items.reduce((sum, item) => sum + (item.price * item.quantity), 0) : 0;
    const change = cash - total;
    
    if (change >= 0) {
        document.getElementById('change').textContent = '₱' + change.toFixed(2);
        document.getElementById('change-section').style.display = 'flex';
        document.getElementById('checkout-btn').disabled = false;
    } else {
        document.getElementById('change-section').style.display = 'none';
        document.getElementById('checkout-btn').disabled = true;
    }
});

// Add to cart
function addToCart(productId, name, price, stock) {
    const currentOrder = orders.find(o => o.id === currentOrderId);
    if (!currentOrder) return;
    
    const existingItem = currentOrder.items.find(item => item.productId === productId);
    
    if (existingItem) {
        if (existingItem.quantity >= stock) {
            alert('Not enough stock available!');
            return;
        }
        existingItem.quantity++;
    } else {
        currentOrder.items.push({ productId, name, price, quantity: 1, stock });
    }
    
    renderCart();
    renderOrderTabs();
}

// Remove from cart
function removeFromCart(productId) {
    const currentOrder = orders.find(o => o.id === currentOrderId);
    if (!currentOrder) return;
    
    currentOrder.items = currentOrder.items.filter(item => item.productId !== productId);
    renderCart();
    renderOrderTabs();
}

// Update quantity
function updateQuantity(productId, change) {
    const currentOrder = orders.find(o => o.id === currentOrderId);
    if (!currentOrder) return;
    
    const item = currentOrder.items.find(item => item.productId === productId);
    if (item) {
        const newQty = item.quantity + change;
        if (newQty <= 0) {
            removeFromCart(productId);
        } else if (newQty <= item.stock) {
            item.quantity = newQty;
            renderCart();
            renderOrderTabs();
        } else {
            alert('Not enough stock available!');
        }
    }
}

// Render cart
function renderCart() {
    const currentOrder = orders.find(o => o.id === currentOrderId);
    if (!currentOrder) return;
    
    const cartContainer = document.getElementById('cart-items');
    document.getElementById('current-order-id').textContent = currentOrder.id;
    
    if (currentOrder.items.length === 0) {
        cartContainer.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <p>Cart is empty</p>
                <small>Click products to add</small>
            </div>
        `;
        document.getElementById('subtotal').textContent = '₱0.00';
        document.getElementById('total').textContent = '₱0.00';
        document.getElementById('checkout-btn').disabled = true;
        document.getElementById('hold-btn').disabled = currentOrder.items.length === 0;
        document.getElementById('change-section').style.display = 'none';
        return;
    }
    
    let html = '';
    currentOrder.items.forEach(item => {
        const itemTotal = item.price * item.quantity;
        html += `
            <div class="cart-item d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <strong>${item.name}</strong>
                    <br>
                    <small class="text-muted">₱${item.price.toFixed(2)} each</small>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.productId}, -1)">−</button>
                    <span class="mx-2" style="min-width: 25px; text-align: center;">${item.quantity}</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.productId}, 1)">+</button>
                </div>
                <div class="ms-3" style="min-width: 85px;">
                    <strong>₱${itemTotal.toFixed(2)}</strong>
                    <br>
                    <button class="btn btn-sm btn-outline-danger py-0" onclick="removeFromCart(${item.productId})">×</button>
                </div>
            </div>
        `;
    });
    
    cartContainer.innerHTML = html;
    
    const total = currentOrder.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('subtotal').textContent = '₱' + total.toFixed(2);
    document.getElementById('total').textContent = '₱' + total.toFixed(2);
    document.getElementById('hold-btn').disabled = currentOrder.items.length === 0;
    
    document.getElementById('cash-received').dispatchEvent(new Event('input'));
}

// Process checkout
function processCheckout() {
    const currentOrder = orders.find(o => o.id === currentOrderId);
    if (!currentOrder || currentOrder.items.length === 0) {
        alert('Cart is empty!');
        return;
    }
    
    const cashReceived = parseFloat(document.getElementById('cash-received').value);
    const total = currentOrder.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    if (!cashReceived || cashReceived < total) {
        alert('Insufficient payment amount!');
        return;
    }
    
    const paymentMethod = document.getElementById('payment-method').value;
    
    document.getElementById('checkout-btn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
    document.getElementById('checkout-btn').disabled = true;
    
    fetch('{{ route("pos.process") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            items: currentOrder.items.map(item => ({
                productId: item.productId,
                quantity: item.quantity
            })),
            cash_received: cashReceived,
            payment_method: paymentMethod
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            lastTransactionId = data.transaction.id;
            document.getElementById('modal-transaction-number').textContent = data.transaction.transaction_number;
            document.getElementById('modal-total').textContent = '₱' + parseFloat(data.transaction.total_amount).toFixed(2);
            document.getElementById('modal-cash').textContent = '₱' + parseFloat(data.transaction.cash_received).toFixed(2);
            document.getElementById('modal-change').textContent = '₱' + parseFloat(data.transaction.change).toFixed(2);
            
            new bootstrap.Modal(document.getElementById('successModal')).show();
        } else {
            alert(data.message || 'Transaction failed!');
            document.getElementById('checkout-btn').innerHTML = '<i class="fas fa-cash-register me-2"></i> Process Payment';
            document.getElementById('checkout-btn').disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        document.getElementById('checkout-btn').innerHTML = '<i class="fas fa-cash-register me-2"></i> Process Payment';
        document.getElementById('checkout-btn').disabled = false;
    });
}

// Void transaction
async function voidTransaction() {
    const transactionNumber = document.getElementById('void-transaction-number').value.trim();
    const voidReason = document.getElementById('void-reason').value.trim();
    const button = document.getElementById('void-submit-btn');

    if (!transactionNumber) {
        alert('Enter the transaction number.');
        return;
    }

    if (!voidReason) {
        alert('Enter the reason for voiding.');
        return;
    }

    if (!(await appConfirm('Void this transaction and restore stock?'))) {
        return;
    }

    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Voiding...';
    button.disabled = true;

    fetch('{{ route("pos.void") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            transaction_number: transactionNumber,
            void_reason: voidReason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('voidModal'));
            if (modal) {
                modal.hide();
            }

            document.getElementById('void-transaction-number').value = '';
            document.getElementById('void-reason').value = '';
            showNotification(data.message || 'Transaction voided successfully.');
        } else {
            alert(data.message || 'Void failed.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while voiding. Please try again.');
    })
    .finally(() => {
        button.innerHTML = '<i class="fas fa-ban me-2"></i> Confirm Void';
        button.disabled = false;
    });
}

// Print receipt
function printReceipt() {
    if (lastTransactionId) {
        window.open(`{{ route('pos.print-receipt', '') }}/${lastTransactionId}`, '_blank');
    }
}

// Start new transaction
function startNewTransaction() {
    const currentOrder = orders.find(o => o.id === currentOrderId);
    if (currentOrder) {
        // Remove completed order
        orders = orders.filter(o => o.id !== currentOrderId);
    }
    
    // Switch to first active order or create new one
    const activeOrder = orders.find(o => o.status === 'active');
    if (activeOrder) {
        switchToOrder(activeOrder.id);
    } else {
        createNewOrder();
    }
}

// Update order timer
function updateOrderTimer() {
    const currentOrder = orders.find(o => o.id === currentOrderId);
    if (!currentOrder) return;
    
    const now = new Date();
    const elapsed = Math.floor((now - currentOrder.createdAt) / 1000);
    const minutes = Math.floor(elapsed / 60);
    const seconds = elapsed % 60;
    
    document.getElementById('order-time').textContent = 
        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
}

// Show notification
function showNotification(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-info alert-dismissible fade show position-fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

// Initialize
initializeOrders();
setInterval(updateOrderTimer, 1000);
</script>
@endpush
