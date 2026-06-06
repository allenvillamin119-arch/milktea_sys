<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'category',
        'type',
        'item_type',
        'price',
        'stock',
        'low_stock_threshold',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Check if this is a sellable product (milktea, coffee, topping, etc.)
     */
    public function isProduct(): bool
    {
        return $this->type === 'product';
    }

    /**
     * Check if this is a supply item (cups, straws, etc.)
     */
    public function isSupply(): bool
    {
        return $this->type === 'supply';
    }

    /**
     * Check if this product is sellable in POS
     */
    public function isSellable(): bool
    {
        return $this->isProduct() && $this->item_type === 'sellable' && $this->is_active;
    }

    /**
     * Scope to get only sellable products
     */
    public function scopeSellable($query)
    {
        return $query->where('type', 'product')
                    ->where('item_type', 'sellable')
                    ->where('is_active', true);
    }

    /**
     * Scope to get only supply items
     */
    public function scopeSupplies($query)
    {
        return $query->where('type', 'supply')
                    ->where('is_active', true);
    }

    public function categoryModel()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getCategoryNameAttribute()
    {
        return optional($this->categoryModel)->name ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return 'Out of Stock';
        }

        if ($this->stock <= $this->low_stock_threshold) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Supplies (products) used by this product when sold.
     * This is a self-referencing many-to-many using product_supplies pivot.
     * pivot 'quantity' is units of supply consumed per 1 unit sold.
     */
    public function supplies()
    {
        return $this->belongsToMany(Product::class, 'product_supplies', 'product_id', 'supply_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
