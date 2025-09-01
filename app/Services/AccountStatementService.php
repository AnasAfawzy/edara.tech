<?php

namespace App\Services;

use App\Models\Account;
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
        $openingBalance = 0;
        if ($startDate) {
            $lastTransaction = $this->repository->getLastTransactionBefore($account, $startDate);
            if ($lastTransaction) {
                $openingBalance = $lastTransaction->balance;
            }
        }

        $transactions = $this->repository->getTransactions($account, $startDate, $endDate);
        $totals = $this->repository->getTotals($account, $startDate, $endDate);

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
}
