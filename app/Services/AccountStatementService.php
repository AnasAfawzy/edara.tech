<?php

namespace App\Services;

use App\Models\Account;
use App\Models\OpeningBalance;
use App\Helpers\FinancialYearHelper;
use App\Repositories\Interfaces\AccountStatementRepositoryInterface;

class AccountStatementService
{
    protected $repository;

    public function __construct(AccountStatementRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getStatement(Account $account, ?string $startDate, ?string $endDate)
    {
        // 1. حساب الرصيد الافتتاحي الصحيح
        $openingBalance = $this->calculateOpeningBalance($account, $startDate);

        // 2. الحصول على المعاملات والمجاميع
        $transactions = $this->repository->getTransactions($account, $startDate, $endDate);
        $totals = $this->repository->getTotals($account, $startDate, $endDate);

        // 3. حساب الرصيد الختامي
        $closingBalance = $openingBalance + $totals['total_debit'] - $totals['total_credit'];

        return [
            'account' => $account,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'totalDebit' => $totals['total_debit'],
            'totalCredit' => $totals['total_credit'],
            'closingBalance' => $closingBalance,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * حساب الرصيد الافتتاحي الصحيح
     */
    private function calculateOpeningBalance(Account $account, ?string $startDate): float
    {
        // إذا لم يتم تحديد تاريخ بداية، ابدأ من الرصيد الافتتاحي فقط
        if (!$startDate) {
            return $this->getAccountOpeningBalance($account);
        }

        // 1. الحصول على الرصيد الافتتاحي من جدول opening_balances
        $initialOpeningBalance = $this->getAccountOpeningBalance($account);

        // 2. الحصول على آخر معاملة قبل تاريخ البداية
        $lastTransaction = $this->repository->getLastTransactionBefore($account, $startDate);

        // إذا لم توجد معاملات قبل التاريخ، استخدم الرصيد الافتتاحي فقط
        if (!$lastTransaction) {
            return $initialOpeningBalance;
        }

        // إذا وجدت معاملات، استخدم آخر رصيد (يفترض أنه يتضمن الرصيد الافتتاحي)
        return $lastTransaction->balance;
    }

    /**
     * الحصول على الرصيد الافتتاحي للحساب من جدول opening_balances
     */
    private function getAccountOpeningBalance(Account $account): float
    {
        // الحصول على السنة المالية الحالية أو المحددة
        $currentFinancialYear = FinancialYearHelper::getActiveFinancialYear();

        if (!$currentFinancialYear) {
            return 0;
        }

        $openingBalance = OpeningBalance::where('account_id', $account->id)
            ->where('financial_year_id', $currentFinancialYear->id)
            ->first();

        if (!$openingBalance) {
            return 0;
        }

        // الرصيد الافتتاحي = المدين - الدائن
        return $openingBalance->debit_balance - $openingBalance->credit_balance;
    }
}
