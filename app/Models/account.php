<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Parent account relation.
     */
    public function parent()
    {
        return $this->belongsTo(Account::class, 'ownerEl');
    }

    /**
     * Children accounts relation.
     */
    public function children()
    {
        return $this->hasMany(Account::class, 'ownerEl');
    }

    /**
     * Scope: main accounts (has_sub = true OR slave = false)
     */
    public function scopeMainAccounts($query)
    {
        return $query->where('has_sub', true)->orWhere('slave', false);
    }

    // backward-compatible alias for older scope name
    public function scopeMain_Accounts($query)
    {
        return $this->scopeMainAccounts($query);
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


    /**
     * العلاقة مع الأرصدة الافتتاحية
     */
    public function openingBalances()
    {
        return $this->hasMany(OpeningBalance::class);
    }

    /**
     * الحصول على الرصيد الافتتاحي لسنة مالية معينة
     */
    public function getOpeningBalanceForYear($financialYearId)
    {
        return $this->openingBalances()
            ->where('financial_year_id', $financialYearId)
            ->first();
    }

    /**
     * الحصول على الرصيد الافتتاحي للسنة المالية النشطة
     */
    public function getCurrentOpeningBalance()
    {
        $activeYear = FinancialYear::where('is_active', true)->first();

        if (!$activeYear) {
            return null;
        }

        return $this->getOpeningBalanceForYear($activeYear->id);
    }
}
