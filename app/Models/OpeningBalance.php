<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'financial_year_id',
        'debit_balance',
        'credit_balance',
        'balance_date',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'balance_date' => 'date',
        'debit_balance' => 'decimal:2',
        'credit_balance' => 'decimal:2'
    ];

    /**
     * العلاقة مع الحساب
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * العلاقة مع السنة المالية
     */
    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ الرصيد
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع المستخدم الذي عدّل الرصيد
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * الحصول على الرصيد الصافي
     */
    public function getNetBalanceAttribute(): float
    {
        return $this->debit_balance - $this->credit_balance;
    }

    /**
     * تحديد نوع الرصيد (مدين/دائن/صفر)
     */
    public function getBalanceTypeAttribute(): string
    {
        if ($this->debit_balance > $this->credit_balance) {
            return 'debit';
        } elseif ($this->credit_balance > $this->debit_balance) {
            return 'credit';
        } else {
            return 'zero';
        }
    }

    /**
     * الحصول على الرصيد النشط (المبلغ الفعلي)
     */
    public function getActiveBalanceAttribute(): float
    {
        return abs($this->net_balance);
    }

    /**
     * تحديد ما إذا كان الرصيد متوازن أم لا
     */
    public function getIsBalancedAttribute(): bool
    {
        return abs($this->net_balance) < 0.01;
    }

    /**
     * Scope للبحث حسب السنة المالية
     */
    public function scopeForFinancialYear($query, $financialYearId)
    {
        return $query->where('financial_year_id', $financialYearId);
    }

    /**
     * Scope للبحث حسب نوع الرصيد
     */
    public function scopeWithBalanceType($query, $type)
    {
        switch ($type) {
            case 'debit':
                return $query->whereColumn('debit_balance', '>', 'credit_balance');
            case 'credit':
                return $query->whereColumn('credit_balance', '>', 'debit_balance');
            case 'zero':
                return $query->whereColumn('debit_balance', '=', 'credit_balance');
            default:
                return $query;
        }
    }

    /**
     * Scope للأرصدة غير الصفرية
     */
    public function scopeNonZero($query)
    {
        return $query->where(function ($q) {
            $q->where('debit_balance', '>', 0)
                ->orWhere('credit_balance', '>', 0);
        });
    }
}
