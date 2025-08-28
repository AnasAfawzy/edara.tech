<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'exchange_rate',
    ];

    public function cashVaults()
    {
        return $this->hasMany(CashVault::class, 'currency_id');
    }

    public function JournalEntry()
    {
        return $this->hasMany(JournalEntry::class, 'currency_id');
    }


}
