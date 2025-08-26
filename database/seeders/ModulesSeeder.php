<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModulesSeeder extends Seeder
{
    public function run()
    {
        // Main Data Parent
        $mainData = Module::create([
            'name' => 'main_data',
            'label' => 'Main Data',
            'icon' => 'fa-solid fa-database',
            'route' => null,
            'parent_id' => null,
            'show_in_sidebar' => true,
        ]);

        // Main Data Children
        Module::create([
            'name' => 'currency',
            'label' => 'Currency',
            'icon' => null,
            'route' => 'currencies.index',
            'parent_id' => $mainData->id,
            'show_in_sidebar' => true,

        ]);
        Module::create([
            'name' => 'banks',
            'label' => 'Banks',
            'icon' => null,
            'route' => 'banks.index',
            'parent_id' => $mainData->id,
            'show_in_sidebar' => true,
        ]);
        Module::create([
            'name' => 'cash_vaults',
            'label' => 'Cash Vaults',
            'icon' => null,
            'route' => 'cash-vaults.index',
            'parent_id' => $mainData->id,
            'show_in_sidebar' => true,
        ]);
        Module::create([
            'name' => 'accounts',
            'label' => 'Accounts Tree',
            'icon' => null,
            'route' => 'accounts.index',
            'parent_id' => $mainData->id,
            'show_in_sidebar' => true,
        ]);
        Module::create([
            'name' => 'cost_centers',
            'label' => 'Cost Centers',
            'icon' => null,
            'route' => 'cost-centers.index',
            'parent_id' => $mainData->id,
            'show_in_sidebar' => true,
        ]);

        // Settings Parent
        $settings = Module::create([
            'name' => 'settings',
            'label' => 'Settings',
            'icon' => 'fa-solid fa-gear',
            'route' => null,
            'parent_id' => null,
            'show_in_sidebar' => true,
        ]);

        // Settings Children
        Module::create([
            'name' => 'accounting_settings',
            'label' => 'Accounts Settings',
            'icon' => null,
            'route' => 'accounting-settings.index',
            'parent_id' => $settings->id,
            'show_in_sidebar' => true,
        ]);
        Module::create([
            'name' => 'system_settings',
            'label' => 'System Settings',
            'icon' => null,
            'route' => 'settings.index',
            'parent_id' => $settings->id,
            'show_in_sidebar' => true,
        ]);
        Module::create([
            'name' => 'users',
            'label' => 'المستخدمون',
            'icon' => null,
            'route' => 'users.index',
            'parent_id' => $settings->id,
            'show_in_sidebar' => true,
        ]);
        Module::create([
            'name' => 'roles',
            'label' => 'الأدوار',
            'icon' => null,
            'route' => 'roles.index',
            'parent_id' => $settings->id,
            'show_in_sidebar' => true,
        ]);
        Module::create([
            'name' => 'permissions',
            'label' => 'الصلاحيات',
            'icon' => null,
            'route' => 'permissions.index',
            'parent_id' => $settings->id,
            'show_in_sidebar' => true,
        ]);
    }
}
