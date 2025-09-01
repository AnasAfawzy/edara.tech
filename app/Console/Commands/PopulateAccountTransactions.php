<?php

namespace App\Console\Commands;

use App\Events\JournalEntryPosted;
use App\Models\JournalEntry;
use App\Models\AccountTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateAccountTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:account-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the account_transactions table from existing journal entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Populating account transactions...');

        // 1. Clear the table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AccountTransaction::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->info('Cleared the account_transactions table.');

        // 2. Get all posted journal entries
        $journalEntries = JournalEntry::where('status', JournalEntry::STATUS_POSTED)
            ->with('details')
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $this->info('Found ' . $journalEntries->count() . ' posted journal entries to process.');

        $progressBar = $this->output->createProgressBar($journalEntries->count());
        $progressBar->start();

        // 3. Dispatch events
        foreach ($journalEntries as $journalEntry) {
            try {
                event(new JournalEntryPosted($journalEntry));
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error('\nError processing Journal Entry ID: ' . $journalEntry->id . ' - ' . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->info('\nAccount transactions populated successfully.');

        return 0;
    }
}