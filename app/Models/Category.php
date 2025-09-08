<?php

namespace App\Models;

use App\Models\{User, Product};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories'; // اسم الجدول الجديد

    protected $fillable = [
        'name',
        'name_en',
        'notes',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the products in this category
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get the user who created this category
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this category
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for inactive categories
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return $this->status ? __('Active') : __('Inactive');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->status ? 'bg-label-success' : 'bg-label-secondary';
    }

    /**
     * Check if category is active
     */
    public function isActive()
    {
        return $this->status == 1;
    }

    /**
     * Check if category is inactive
     */
    public function isInactive()
    {
        return $this->status == 0;
    }

    /**
     * Get products count
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }
}
