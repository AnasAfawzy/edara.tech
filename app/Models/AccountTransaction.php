<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'journal_entry_id',
        'journal_entry_detail_id',
        'transaction_date',
        'description',
        'debit',
        'credit',
        'balance',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function journalEntryDetail()
    {
        return $this->belongsTo(JournalEntryDetail::class);
    }
}