<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Module;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // التأكد من وجود الدور admin وإنشاؤه إذا لم يكن موجوداً
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // ربط جميع الصلاحيات بالدور admin دائماً
        $permissions = Permission::pluck('name')->toArray();
        $adminRole->syncPermissions($permissions);

        // ربط جميع الموديولات بدور الأدمن
        $modules = Module::pluck('id')->toArray();
        $adminRole->modules()->sync($modules);

        // ربط المستخدم admin@admin.com بالدور admin إذا كان موجوداً
        $user = User::where('email', 'admin@admin.com')->first();
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        // $this->call([
        //     AccountSeeder::class,
        //     CashVaultPerantAccountSeeder::class,
        //     CashVaultTestSeeder::class,
        //     CostCenterSeeder::class,
        //     JournalEntrySeeder::class,
        //     JournalEntryTestSeeder::class,
        //     BankSeeder::class,
        // ]);
    }
}