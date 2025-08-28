<?php

namespace App\Helpers;

use App\Models\FinancialYear;
use Carbon\Carbon;

class FinancialYearHelper
{
    /**
     * تحديد السنة المالية بناءً على التاريخ
     */
    public static function assignFinancialYear($date): ?int
    {
        $date = Carbon::parse($date);

        // البحث عن السنة المالية التي تحتوي على هذا التاريخ
        $financialYear = FinancialYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        // إذا لم توجد سنة مالية مناسبة، استخدم السنة النشطة
        if (!$financialYear) {
            $financialYear = FinancialYear::getActive();
        }

        return $financialYear ? $financialYear->id : null;
    }

    /**
     * الحصول على السنة المالية النشطة
     */
    public static function getActiveFinancialYear(): ?FinancialYear
    {
        return FinancialYear::getActive();
    }

    /**
     * التحقق من أن التاريخ ضمن سنة مالية نشطة
     */
    public static function isDateInActiveYear($date): bool
    {
        $activeYear = self::getActiveFinancialYear();

        if (!$activeYear) {
            return false;
        }

        return $activeYear->containsDate($date);
    }

    /**
     * الحصول على السنة المالية بناءً على التاريخ
     */
    public static function getFinancialYearByDate($date): ?FinancialYear
    {
        return FinancialYear::getByDate($date);
    }
}
