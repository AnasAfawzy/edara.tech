<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningStock extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'opening_date',
        'notes',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'opening_date' => 'date',
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($openingStock) {
            $openingStock->total_cost = $openingStock->quantity * $openingStock->unit_cost;
        });
    }
}
