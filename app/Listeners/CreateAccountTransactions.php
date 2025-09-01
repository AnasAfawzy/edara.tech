<?php

namespace App\Listeners;

use App\Events\JournalEntryPosted;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateAccountTransactions
{
    public function __construct()
    {
        //
    }

    public function handle(JournalEntryPosted $event): void
    {
        $journalEntry = $event->journalEntry;

        Log::info("CreateAccountTransactions: Starting for journal entry {$journalEntry->id}");

        // التأكد من وجود التفاصيل
        if (!$journalEntry->relationLoaded('details') || $journalEntry->details->isEmpty()) {
            Log::info("CreateAccountTransactions: Loading details for entry {$journalEntry->id}");
            $journalEntry->load(['details.account']);
        }

        Log::info("CreateAccountTransactions: Processing " . $journalEntry->details->count() . " details");

        try {
            DB::transaction(function () use ($journalEntry) {
                // احذف أي معاملات قديمة مرتبطة بالقيد
                $deletedCount = AccountTransaction::where('journal_entry_id', $journalEntry->id)->delete();
                Log::info("CreateAccountTransactions: Deleted {$deletedCount} old transactions");

                foreach ($journalEntry->details as $detail) {
                    // التأكد من وجود الحساب
                    if (!$detail->account) {
                        Log::warning("CreateAccountTransactions: Account not found for detail", [
                            'detail_id' => $detail->id,
                            'account_id' => $detail->account_id
                        ]);
                        continue;
                    }

                    // تخطي التفاصيل التي ليس لها مبلغ
                    if ($detail->debit == 0 && $detail->credit == 0) {
                        Log::info("CreateAccountTransactions: Skipping detail {$detail->id} - zero amount");
                        continue;
                    }

                    // الحصول على آخر رصيد للحساب
                    $lastTransaction = AccountTransaction::where('account_id', $detail->account_id)
                        ->orderBy('transaction_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->lockForUpdate()
                        ->first();

                    $previousBalance = $lastTransaction ? $lastTransaction->balance : 0;

                    // حساب الرصيد الجديد (مدين يزيد الرصيد، دائن ينقص الرصيد)
                    $newBalance = $previousBalance + $detail->debit - $detail->credit;

                    $transaction = AccountTransaction::create([
                        'account_id' => $detail->account_id,
                        'journal_entry_id' => $journalEntry->id,
                        'journal_entry_detail_id' => $detail->id,
                        'transaction_date' => $journalEntry->entry_date,
                        'debit' => $detail->debit,
                        'credit' => $detail->credit,
                        'balance' => $newBalance,
                        'description' => $detail->statement ?: $journalEntry->description,
                    ]);

                    Log::info("CreateAccountTransactions: Created transaction {$transaction->id} for account {$detail->account_id}");
                }
            });

            Log::info("CreateAccountTransactions: Successfully processed entry {$journalEntry->id}");
        } catch (\Exception $e) {
            Log::error('CreateAccountTransactions: Error creating account transactions: ' . $e->getMessage(), [
                'journal_entry_id' => $journalEntry->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
