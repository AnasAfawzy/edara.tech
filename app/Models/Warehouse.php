<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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
     * Scope for active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1); // استخدام 1 بدلاً من 'active'
    }

    /**
     * Scope for inactive warehouses
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0); // استخدام 0 بدلاً من 'inactive'
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
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
     * Get status as string (for backward compatibility)
     */
    public function getStatusStringAttribute()
    {
        return $this->status ? 'active' : 'inactive';
    }

    /**
     * Check if warehouse is active
     */
    public function isActive()
    {
        return $this->status == 1;
    }

    /**
     * Check if warehouse is inactive
     */
    public function isInactive()
    {
        return $this->status == 0;
    }

    /**
     * Get the user who created this warehouse
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this warehouse
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
