<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;

class CashVaultPerantAccountSeeder extends Seeder
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function run(): void
    {
        DB::beginTransaction();

        try {
            $accountData = [
                'name'    => 'Cash Vault',
                'ownerEl' => 216,
            ];

            $accountInfo = $this->accountService->generateAccountData($accountData);

            $account = $this->accountService->create($accountInfo);

            DB::commit();

            $this->command->info("✅ The top-level parent account has been created: ID = {$account->id}, Name = {$account->name}");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Failed to create the top-level parent account: " . $e->getMessage());
        }
    }
}
