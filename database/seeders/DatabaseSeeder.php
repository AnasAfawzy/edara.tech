<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // $user = User::factory()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@admin.com',
        //     'password' => Hash::make('password'),
        // ]);
        // $user->assignRole('admin');

        $this->call([
            AccountSeeder::class,
            CashVaultPerantAccountSeeder::class,
            CashVaultTestSeeder::class,
            CostCenterSeeder::class,
            JournalEntrySeeder::class,
            JournalEntryTestSeeder::class,
            BankSeeder::class,
        ]);
    }
}
