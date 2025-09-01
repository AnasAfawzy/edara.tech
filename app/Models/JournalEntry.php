<?php

namespace App\Models;

use App\Helpers\FinancialYearHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class JournalEntry extends Model
{
    // Journal Entry Statuses
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_POSTED = 'posted';
    const STATUS_REVERSED = 'reversed';
    const STATUS_CANCELLED = 'cancelled';

    // Source Types
    const SOURCE_MANUAL = 'manual';
    const SOURCE_OPENING_BALANCE = 'opening_balance';
    const SOURCE_INVOICE = 'invoice';
    const SOURCE_PAYMENT = 'payment';
    const SOURCE_RECEIPT = 'receipt';
    const SOURCE_SYSTEM = 'system';

    protected $fillable = [
        'entry_date',
        'description',
        'currency_id',
        'financial_year_id',
        'entry_number',
        'reference_number',
        'source_type',
        'source_id',
        'total_debit',
        'total_credit',
        'reverses_entry_id',
        'reversed_by_entry_id',
        'created_by',
        'updated_by',
        'status',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    protected $attributes = [
        'source_type' => 'manual',
        'source_id' => 0,
        'total_debit' => 0.00,
        'total_credit' => 0.00,
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
        return $this->source_type === self::SOURCE_MANUAL;
    }

    // التحقق من كون القيد افتتاحي
    public function isOpeningBalance(): bool
    {
        return $this->source_type === self::SOURCE_OPENING_BALANCE;
    }

    // التحقق من كون القيد متوازن
    public function getIsBalancedAttribute(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    // الحصول على الفرق بين المدين والدائن
    public function getDifferenceAttribute(): float
    {
        return abs($this->total_debit - $this->total_credit);
    }

    // الحصول على نوع المصدر بشكل واضح
    public function getSourceTypeDisplayAttribute(): string
    {
        return match ($this->source_type) {
            self::SOURCE_MANUAL => __('Manual Entry'),
            self::SOURCE_OPENING_BALANCE => __('Opening Balance'),
            self::SOURCE_INVOICE => __('Invoice'),
            self::SOURCE_PAYMENT => __('Payment'),
            self::SOURCE_RECEIPT => __('Receipt'),
            self::SOURCE_SYSTEM => __('System Generated'),
            default => ucfirst($this->source_type)
        };
    }

    // الحصول على حالة القيد بشكل واضح
    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_POSTED => __('Posted'),
            self::STATUS_REVERSED => __('Reversed'),
            self::STATUS_CANCELLED => __('Cancelled'),
            default => ucfirst($this->status)
        };
    }

    // الحصول على لون حالة القيد للعرض
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_POSTED => 'success',
            self::STATUS_REVERSED => 'danger',
            self::STATUS_CANCELLED => 'dark',
            default => 'secondary'
        };
    }

    // Scopes للبحث والفلترة
    public function scopeBySourceType($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForFinancialYear($query, $financialYearId)
    {
        return $query->where('financial_year_id', $financialYearId);
    }

    public function scopePosted($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeOpeningBalance($query)
    {
        return $query->where('source_type', self::SOURCE_OPENING_BALANCE);
    }

    public function scopeManual($query)
    {
        return $query->where('source_type', self::SOURCE_MANUAL);
    }

    public function scopeBalanced($query)
    {
        return $query->whereRaw('ABS(total_debit - total_credit) < 0.01');
    }

    public function scopeUnbalanced($query)
    {
        return $query->whereRaw('ABS(total_debit - total_credit) >= 0.01');
    }

    // دالة لحساب المجاميع من التفاصيل
    public function calculateTotalsFromDetails()
    {
        $totalDebit = $this->details()->sum('debit');
        $totalCredit = $this->details()->sum('credit');

        $this->update([
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit
        ]);

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => abs($totalDebit - $totalCredit)
        ];
    }

    // دالة للتحقق من صحة القيد
    public function validate(): array
    {
        $errors = [];

        // التحقق من التوازن
        if (!$this->is_balanced) {
            $errors[] = __('Journal entry is not balanced. Difference: :diff', [
                'diff' => number_format($this->difference, 2)
            ]);
        }

        // التحقق من وجود تفاصيل
        if ($this->details()->count() === 0) {
            $errors[] = __('Journal entry must have at least one detail.');
        }

        // التحقق من التاريخ في السنة المالية
        if ($this->financial_year_id) {
            $financialYear = $this->financialYear;
            if (
                $financialYear &&
                ($this->entry_date < $financialYear->start_date ||
                    $this->entry_date > $financialYear->end_date)
            ) {
                $errors[] = __('Entry date must be within the financial year period.');
            }
        }

        return $errors;
    }

    // دالة لترحيل القيد
    public function post(): bool
    {
        $validationErrors = $this->validate();

        if (!empty($validationErrors)) {
            return false;
        }

        $this->update(['status' => self::STATUS_POSTED]);
        return true;
    }

    // دالة لإلغاء القيد
    public function cancel(): bool
    {
        if ($this->status === self::STATUS_POSTED) {
            return false; // لا يمكن إلغاء قيد مرحل
        }

        $this->update(['status' => self::STATUS_CANCELLED]);
        return true;
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
                $journalEntry->source_type = self::SOURCE_MANUAL;
            }

            // تحديد معرف المصدر إذا لم يكن محدد
            if ($journalEntry->source_id === null) {
                $journalEntry->source_id = 0;
            }

            // تحديد الحالة الافتراضية
            if (!$journalEntry->status) {
                $journalEntry->status = $journalEntry->source_type === self::SOURCE_OPENING_BALANCE
                    ? self::STATUS_POSTED
                    : self::STATUS_PENDING;
            }

            // توليد رقم مرجعي إذا لم يكن موجود
            if (!$journalEntry->reference_number) {
                $journalEntry->reference_number = static::generateReferenceNumber($journalEntry->source_type);
            }
        });

        static::updating(function ($journalEntry) {
            if (Auth::check()) {
                $journalEntry->updated_by = Auth::id();
            }
        });

        static::saved(function ($journalEntry) {
            // إعادة حساب المجاميع عند الحفظ
            if ($journalEntry->details()->exists()) {
                $journalEntry->calculateTotalsFromDetails();
            }
        });
    }

    // دالة لتوليد رقم مرجعي
    protected static function generateReferenceNumber($sourceType): string
    {
        $prefix = match ($sourceType) {
            self::SOURCE_OPENING_BALANCE => 'OB',
            self::SOURCE_MANUAL => 'JE',
            self::SOURCE_INVOICE => 'INV',
            self::SOURCE_PAYMENT => 'PAY',
            self::SOURCE_RECEIPT => 'REC',
            default => 'JE'
        };

        $year = date('Y');
        $lastEntry = static::where('source_type', $sourceType)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastEntry ?
            (int) substr($lastEntry->reference_number, -4) + 1 :
            1;

        return $prefix . '-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
