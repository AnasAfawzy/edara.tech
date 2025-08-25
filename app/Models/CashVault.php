<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashVault extends Model
{
    protected $fillable = [
        'name',
        'account_id',
        'currency_id',
        'balance',
        'status',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function journalEntries()
    {
        return $this->hasManyThrough(
            JournalEntry::class,
            JournalEntryDetail::class,
            'account_id',
            'id',
            'account_id',
            'journal_entry_id'
        );
    }
}
