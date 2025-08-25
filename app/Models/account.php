<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\PseudoTypes\True_;

class Account extends Model
{
    protected $fillable = [
        'name',
        'position',
        'ownerEl',
        'slave',
        'code',
        'level',
        'creditor',
        'debtor',
        'has_sub',
        'is_sub'
    ];

    protected $casts = [
        'has_sub' => 'boolean',
        'is_sub' => 'boolean',
        'slave' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'ownerEl');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'ownerEl');
    }

    public function scopeMain_Accounts($query)
    {
        return $query->where('has_sub', true)->orWhere('slave', false);
    }

    // تفاصيل القيود المرتبطة بالحساب
    public function journalEntryDetails()
    {
        return $this->hasMany(JournalEntryDetail::class, 'account_id');
    }

    public function banks()
    {
        return $this->hasMany(Bank::class, 'account_id');
    }

    // القيود نفسها (من غير المرور على التفاصيل)
    public function journalEntries()
    {
        return $this->hasManyThrough(
            JournalEntry::class,
            JournalEntryDetail::class,
            'account_id',        // FK في JournalEntryDetail
            'id',                // PK في JournalEntry
            'id',                // PK في Account
            'journal_entry_id'   // FK في JournalEntryDetail
        );
    }
}
