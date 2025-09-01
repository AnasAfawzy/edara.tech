<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'exchange_rate',
        'is_default'
    ];

    // إضافة scope للعملة الافتراضية
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // دالة للحصول على العملة الافتراضية
    public static function getDefault()
    {
        return static::where('is_default', true)->first() ?? static::first();
    }

    public function cashVaults()
    {
        return $this->hasMany(CashVault::class, 'currency_id');
    }

    public function JournalEntry()
    {
        return $this->hasMany(JournalEntry::class, 'currency_id');
    }
}
