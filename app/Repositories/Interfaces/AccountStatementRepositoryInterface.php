<?php

namespace App\Repositories\Interfaces;

use App\Models\Account;
use Illuminate\Pagination\LengthAwarePaginator;

interface AccountStatementRepositoryInterface
{
    public function getTransactions(Account $account, ?string $startDate, ?string $endDate, int $perPage = 25): LengthAwarePaginator;

    public function getLastTransactionBefore(Account $account, string $date);

    public function getTotals(Account $account, ?string $startDate, ?string $endDate): array;
}
