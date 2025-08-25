<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\CashVaultService;

class CashVaultTestSeeder extends Seeder
{
    protected CashVaultService $cashVaultService;

    public function __construct(CashVaultService $cashVaultService)
    {
        $this->cashVaultService = $cashVaultService;
    }

    public function run(): void
    {
        $data = [
            'name'     => 'خزنة تجريبية',
            'currency_id' => 1,
            'balance'  => 0,
            'status'   => 'active',
        ];

        try {
            $cashVault = $this->cashVaultService->createVault($data);
            $this->command->info("Test cash vault created successfully: ID = {$cashVault->id}");
        } catch (\Exception $e) {
            $this->command->error("Failed to create the test cash vault: " . $e->getMessage());
        }
    }
}
