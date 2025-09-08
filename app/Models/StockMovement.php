<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'movement_type',
        'transaction_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'current_balance',
        'reference_type',
        'reference_id',
        'reference_number',
        'movement_date',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'movement_date' => 'date',
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'current_balance' => 'decimal:3'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($stockMovement) {
            $stockMovement->total_cost = $stockMovement->quantity * $stockMovement->unit_cost;
        });
    }
}
