<?php

namespace App\Models;

use App\Helpers\FinancialYearHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'date',
        'description',
        'currency_id',
        'financial_year_id',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function details()
    {
        return $this->hasMany(JournalEntryDetail::class);
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journalEntry) {
            if (!$journalEntry->financial_year_id) {
                $journalEntry->financial_year_id = FinancialYearHelper::assignFinancialYear($journalEntry->date);
            }
        });
    }
}
