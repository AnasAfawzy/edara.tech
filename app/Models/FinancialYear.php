<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FinancialYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_closed'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
    ];

    // الحصول على السنة المالية النشطة
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    // التحقق من وجود تاريخ ضمن السنة المالية
    public function containsDate($date)
    {
        $date = Carbon::parse($date);
        return $date->between($this->start_date, $this->end_date);
    }

    // الحصول على السنة المالية بناءً على التاريخ
    public static function getByDate($date)
    {
        $date = Carbon::parse($date);
        return self::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    // العلاقات
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
