<?php

namespace App\Listeners;

use App\Events\JournalEntryPosted;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateAccountTransactions implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(JournalEntryPosted $event): void
    {
        $journalEntry = $event->journalEntry;

        try {
            DB::transaction(function () use ($journalEntry) {
                // احذف أي معاملات قديمة مرتبطة بالقيد
                AccountTransaction::where('journal_entry_id', $journalEntry->id)->delete();

                foreach ($journalEntry->details as $detail) {
                    $lastTransaction = AccountTransaction::where('account_id', $detail->account_id)
                        ->orderBy('id', 'desc') // الأحدث
                        ->lockForUpdate()
                        ->first();

                    $lastBalance = $lastTransaction
                        ? $lastTransaction->balance
                        : ($detail->account->opening_balance ?? 0);

                    $newBalance = $lastBalance + $detail->debit - $detail->credit;

                    AccountTransaction::create([
                        'account_id' => $detail->account_id,
                        'journal_entry_id' => $journalEntry->id,
                        'journal_entry_detail_id' => $detail->id,
                        'transaction_date' => $journalEntry->entry_date,
                        'description' => $detail->statement ?? $journalEntry->description,
                        'debit' => $detail->debit,
                        'credit' => $detail->credit,
                        'balance' => $newBalance,
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to create account transactions', [
                'journal_entry_id' => $journalEntry->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
