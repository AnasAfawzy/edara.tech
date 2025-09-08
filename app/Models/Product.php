<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'code',
        'barcode',
        'category_id',
        'brand_id',
        'unit_id',
        'description',
        'notes',
        'image',
        'purchase_price',
        'sale_price',
        'min_stock',
        'max_stock',
        'current_stock',
        'reorder_level',
        'has_expiry',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'min_stock' => 'decimal:3',
        'max_stock' => 'decimal:3',
        'current_stock' => 'decimal:3',
        'reorder_level' => 'decimal:3',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the category that owns the product
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand that owns the product
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the unit that owns the product
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user who created this product
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated this product
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope for inactive products
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock')
            ->whereNotNull('min_stock');
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return $this->is_active ? __('Active') : __('Inactive');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->is_active ? 'bg-label-success' : 'bg-label-secondary';
    }

    /**
     * Get profit margin
     */
    public function getProfitMarginAttribute()
    {
        if ($this->purchase_price > 0) {
            return (($this->sale_price - $this->purchase_price) / $this->purchase_price) * 100;
        }
        return 0;
    }

    /**
     * Get profit amount
     */
    public function getProfitAmountAttribute()
    {
        return $this->sale_price - $this->purchase_price;
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }
        return asset('assets/images/placeholder-product.png');
    }

    /**
     * Check if product is low stock
     */
    public function getIsLowStockAttribute()
    {
        return $this->min_stock && $this->current_stock <= $this->min_stock;
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute()
    {
        if ($this->is_low_stock) {
            return 'low';
        } elseif ($this->max_stock && $this->current_stock >= $this->max_stock) {
            return 'high';
        }
        return 'normal';
    }

    /**
     * Get stock status badge class
     */
    public function getStockStatusBadgeClassAttribute()
    {
        switch ($this->stock_status) {
            case 'low':
                return 'bg-label-danger';
            case 'high':
                return 'bg-label-warning';
            default:
                return 'bg-label-success';
        }
    }

    public function openingStocks()
    {
        return $this->hasMany(OpeningStock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function currentOpeningStock()
    {
        return $this->hasOne(OpeningStock::class)->where('is_active', true)->latest('opening_date');
    }
}
