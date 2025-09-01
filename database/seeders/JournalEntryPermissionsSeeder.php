<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Module;

class JournalEntryPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Creating permissions for Journal Entries workflow...');

        // Using singular form to match existing convention
        $permissions = [
            'view journal entry',
            'create journal entry',
            'edit journal entry',
            'delete journal entry',
            'submit journal entry',
            'approve journal entry',
            'reject journal entry',
            'post journal entry',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Assign to a default role if needed, e.g., 'admin'
        // This part can be customized based on the application's needs.
        // For example, giving all new permissions to the super-admin role.
        $adminRole = Role::where('name', 'Admin')->orWhere('name', 'Super Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
            $this->command->info('Journal Entry workflow permissions assigned to ' . $adminRole->name . ' role.');
        }

        $this->command->info('Journal Entry workflow permissions created/updated successfully.');
    }
}