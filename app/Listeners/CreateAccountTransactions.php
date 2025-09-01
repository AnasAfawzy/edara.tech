<?php

namespace App\Listeners;

use App\Events\JournalEntryPosted;
use App\Models\AccountTransaction;
use App\Models\OpeningBalance;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

                    // حساب الرصيد الصحيح مع الأرصدة الافتتاحية
                    $correctBalance = $this->calculateCorrectBalance($detail->account_id, $journalEntry);

                    $transaction = AccountTransaction::create([
                        'account_id' => $detail->account_id,
                        'journal_entry_id' => $journalEntry->id,
                        'journal_entry_detail_id' => $detail->id,
                        'transaction_date' => $journalEntry->entry_date,
                        'debit' => $detail->debit,
                        'credit' => $detail->credit,
                        'balance' => $correctBalance + $detail->debit - $detail->credit,
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

    /**
     * حساب الرصيد الصحيح للحساب شامل الأرصدة الافتتاحية
     */
    private function calculateCorrectBalance($accountId, $journalEntry)
    {
        // الحصول على الرصيد الافتتاحي
        $openingBalance = OpeningBalance::where('account_id', $accountId)
            ->where('financial_year_id', $journalEntry->financial_year_id)
            ->first();

        $openingAmount = 0;
        if ($openingBalance) {
            $openingAmount = $openingBalance->debit_balance - $openingBalance->credit_balance;
        }

        // الحصول على آخر رصيد من المعاملات (باستثناء قيود الأرصدة الافتتاحية إذا كان هذا القيد ليس افتتاحي)
        $lastTransaction = AccountTransaction::where('account_id', $accountId)
            ->when($journalEntry->source_type !== JournalEntry::SOURCE_OPENING_BALANCE, function ($query) {
                // إذا لم يكن قيد افتتاحي، استبعد قيود الأرصدة الافتتاحية من الحساب
                $query->whereHas('journalEntry', function ($q) {
                    $q->where('source_type', '!=', JournalEntry::SOURCE_OPENING_BALANCE);
                });
            })
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $transactionBalance = $lastTransaction ? $lastTransaction->balance : 0;

        // إذا كان هذا قيد افتتاحي، فالرصيد السابق = 0
        if ($journalEntry->source_type === JournalEntry::SOURCE_OPENING_BALANCE) {
            return 0;
        }

        // إذا لم توجد معاملات سابقة، استخدم الرصيد الافتتاحي فقط
        if (!$lastTransaction) {
            return $openingAmount;
        }

        // إذا وجدت معاملات، استخدم آخر رصيد (الذي يفترض أن يتضمن الرصيد الافتتاحي بالفعل)
        return $transactionBalance;
    }
}
