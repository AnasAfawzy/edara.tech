<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FinancialYearPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'view financial years',
            'create financial years',
            'edit financial years',
            'delete financial years',
            'activate financial years',
            'close financial years'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
    }
}
