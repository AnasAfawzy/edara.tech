<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Module;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $actions = ['view', 'create', 'edit', 'delete'];
        $modules = \App\Models\Module::pluck('name')->toArray();

        foreach ($modules as $module) {
            // تأكد أن $module small وبدون مسافات أو كابيتال في الداتا بيز
            $cleanName = str_replace(['_', '-'], ' ', strtolower($module));
            foreach ($actions as $action) {
                \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => "{$action} {$cleanName}",
                    'guard_name' => 'web',
                ]);
            }
        }
    }
}
