<?php

namespace App\Helpers;

use App\Services\FinancialYearService;

class FinancialYearHelper
{
    public static function getActiveFinancialYear()
    {
        $service = app(FinancialYearService::class);
        return $service->getActiveFinancialYear();
    }

    public static function assignFinancialYear($date = null)
    {
        $service = app(FinancialYearService::class);
        return $service->assignFinancialYearId($date);
    }
}
