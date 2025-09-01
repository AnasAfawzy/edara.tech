<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Repositories\Interfaces\AccountStatementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AccountStatementRepository implements AccountStatementRepositoryInterface
{
    public function getTransactions(Account $account, ?string $startDate, ?string $endDate, int $perPage = 25): LengthAwarePaginator
    {
        $query = AccountTransaction::where('account_id', $account->id);

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        return $query->orderBy('transaction_date')->orderBy('id')->paginate($perPage);
    }

    public function getLastTransactionBefore(Account $account, string $date)
    {
        return AccountTransaction::where('account_id', $account->id)
            ->where('transaction_date', '<', $date)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function getTotals(Account $account, ?string $startDate, ?string $endDate): array
    {
        $query = AccountTransaction::where('account_id', $account->id)
            ->select(DB::raw('SUM(debit) as total_debit, SUM(credit) as total_credit'));

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        $result = $query->first();

        return [
            'total_debit' => $result->total_debit ?? 0,
            'total_credit' => $result->total_credit ?? 0,
        ];
    }
}
