<?php

namespace App\Models;

use App\Helpers\FinancialYearHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_date',
        'description',
        'currency_id',
        'financial_year_id',
        'entry_number',
        'source_type',
        'source_id',
        'reverses_entry_id',
        'reversed_by_entry_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    protected $attributes = [
        'source_type' => 'manual',
        'source_id' => 0,
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

    public function reversingEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'reverses_entry_id');
    }

    public function originalEntry()
    {
        return $this->hasOne(JournalEntry::class, 'reversed_by_entry_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all of the attachments for the journal entry.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // التحقق من كون القيد يدوي
    public function isManual(): bool
    {
        return $this->source_type === 'manual';
    }

    // الحصول على نوع المصدر بشكل واضح
    public function getSourceTypeDisplayAttribute(): string
    {
        return match($this->source_type) {
            'manual' => __('Manual Entry'),
            'invoice' => __('Invoice'),
            'payment' => __('Payment'),
            'receipt' => __('Receipt'),
            default => ucfirst($this->source_type)
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journalEntry) {
            if (Auth::check()) {
                $journalEntry->created_by = Auth::id();
            }
            // تحديد السنة المالية إذا لم تكن محددة
            if (!$journalEntry->financial_year_id) {
                $journalEntry->financial_year_id = FinancialYearHelper::assignFinancialYear($journalEntry->entry_date);
            }

            // تحديد نوع المصدر إذا لم يكن محدد
            if (!$journalEntry->source_type) {
                $journalEntry->source_type = 'manual';
            }

            // تحديد معرف المصدر إذا لم يكن محدد
            if ($journalEntry->source_id === null) {
                $journalEntry->source_id = 0;
            }
        });

        static::updating(function ($journalEntry) {
            if (Auth::check()) {
                $journalEntry->updated_by = Auth::id();
            }
        });
    }
}
