<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\JournalEntryService;

class JournalEntryTestSeeder extends Seeder
{
    protected JournalEntryService $journalEntryService;

    public function __construct(JournalEntryService $journalEntryService)
    {
        $this->journalEntryService = $journalEntryService;
    }

    public function run(): void
    {
        $entryData = [
            'entry_number' => 'JV-0004',
            'entry_date'   => now()->toDateString(),
            'description'    => 'تحويل من بنك QNB إلى بنك اسكندريه',
            'currency_id'  => 1,

        ];

        $details = [
            ['account_id' => 14, 'debit' => 1000, 'credit' => 0, 'cost_center' => 'CC-1'],
            ['account_id' => 15, 'debit' => 0,    'credit' => 1000, 'cost_center' => 'CC-2'],
        ];

        $journalEntry = $this->journalEntryService->createEntry($entryData, $details, 'Invoice', 5);

        $this->command->info("✅ Test journal entry created: #{$journalEntry->entry_number}");
    }
}
