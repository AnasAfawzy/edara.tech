<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'branch',
        'account_number',
        'currency_id',
        'account_id',
        'balance',
        'status',
        'swift_code',

    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function journalEntryDetails()
    {
        return $this->hasMany(JournalEntryDetail::class, 'account_id');
    }

    public function journalEntries()
    {
        return $this->hasManyThrough(
            JournalEntry::class,
            JournalEntryDetail::class,
            'account_id',        // foreign key في journal_entry_details
            'id',                // primary key في journal_entries
            'account_id',        // العمود في banks
            'journal_entry_id'   // العمود في journal_entry_details
        );
    }
}
