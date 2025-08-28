<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryDetail extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'statement',
        'debit',
        'credit',
        'cost_center_id' // غيرت من cost_center إلى cost_center_id
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    // Accessor للحصول على المبلغ (مدين أو دائن)
    public function getAmountAttribute()
    {
        return $this->debit > 0 ? $this->debit : $this->credit;
    }

    // Accessor لتحديد نوع العملية
    public function getTypeAttribute()
    {
        return $this->debit > 0 ? 'debit' : 'credit';
    }
}
