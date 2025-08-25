<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;

class JournalEntrySeeder extends Seeder
{
    public function run(): void
    {
        $currency = Currency::firstOrCreate(
            ['code' => 'EGP'],
            ['name' => 'Egyptian Pound']
        );

        $account_1 = Account::where('code', '01020101')->first();
        $account_2 = Account::where('code', '01020102')->first();

        if (!$account_1 || !$account_2) {
            $this->command->error("Accounts not found in the accounts table (01020101, 01020102)");
            return;
        }

        // Manual Entry
        $journalEntry = JournalEntry::create([
            'entry_number' => 'JV-0001',
            'entry_date'   => now()->toDateString(),
            'currency_id'  => $currency->id,
            'source_id'    => 0,
            'source_type'  => 'manual',
            'description'  => 'قيد يومية يدوي',
        ]);

        JournalEntryDetail::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id'       => $account_1->id,
            'debit'            => 5000,
            'credit'           => 0,
        ]);

        JournalEntryDetail::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id'       => $account_2->id,
            'debit'            => 0,
            'credit'           => 5000,
        ]);

        $this->command->info("Journal entry created successfully");
    }
}
