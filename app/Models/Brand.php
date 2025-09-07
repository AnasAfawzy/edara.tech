<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

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
     * Get the products in this brand
     */
    // public function products()
    // {
    //     return $this->hasMany(\App\Models\Product::class, 'brand_id');
    // }

    /**
     * Get the user who created this brand
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated this brand
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope for active brands
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for inactive brands
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
}
