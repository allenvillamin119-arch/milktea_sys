<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Welcome/Index page
Route::get('/', function () {
    return view('index');
})->name('index');

// Authentication routes (disable public registration)
Auth::routes(['register' => false]);

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard (Admin only)
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('admin')->name('dashboard');
    
    // Admin user management
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset');
    });
    
    // Products (Admin only)
    Route::middleware(['admin'])->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::resource('products', ProductController::class);
        // Admin routes to manage product supplies (which are supply-type products)
        Route::get('/products/{product}/supplies', [App\Http\Controllers\ProductSupplyController::class, 'edit'])->name('products.supplies.edit');
        Route::post('/products/{product}/supplies', [App\Http\Controllers\ProductSupplyController::class, 'update'])->name('products.supplies.update');
        Route::get('/products/{product}/supplies/export', [App\Http\Controllers\ProductSupplyController::class, 'export'])->name('products.supplies.export');
        Route::post('/products/{product}/supplies/import', [App\Http\Controllers\ProductSupplyController::class, 'import'])->name('products.supplies.import');
    });
    
    // Inventory Management (Admin only)
    Route::middleware(['admin'])->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/stock-in', [InventoryController::class, 'stockIn'])->name('stock-in');
        Route::post('/stock-in', [InventoryController::class, 'stockInStore']);
        Route::get('/stock-out', [InventoryController::class, 'stockOut'])->name('stock-out');
        Route::post('/stock-out', [InventoryController::class, 'stockOutStore']);
        Route::get('/history', [InventoryController::class, 'history'])->name('history');
        Route::get('/{product}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{product}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{product}', [InventoryController::class, 'destroy'])->name('destroy');
    });
    
    // POS System (Cashier)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/products', [POSController::class, 'getProducts'])->name('products');
        Route::post('/process', [POSController::class, 'processTransaction'])->name('process');
        Route::post('/void', [POSController::class, 'voidTransaction'])->name('void');
        Route::get('/receipt/{id}', [POSController::class, 'receipt'])->name('receipt');
        Route::get('/print-receipt/{id}', [POSController::class, 'printReceipt'])->name('print-receipt');
    });
    
    // Reports (Admin only)
    Route::middleware(['admin'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'salesIndex'])->name('sales');
        Route::match(['get', 'post'], '/sales/generate', [ReportController::class, 'salesReport'])->name('sales.generate');
        Route::get('/sales/export-csv', [ReportController::class, 'salesCsv'])->name('sales.csv');
        Route::get('/inventory', [ReportController::class, 'inventoryIndex'])->name('inventory');
        Route::match(['get', 'post'], '/inventory/generate', [ReportController::class, 'inventoryReport'])->name('inventory.generate');
        Route::get('/inventory/export-csv', [ReportController::class, 'inventoryCsv'])->name('inventory.csv');
    });
});
