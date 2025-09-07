<?php

namespace App\Observers;

use App\Models\Module;
use Spatie\Permission\Models\Permission;

class ModuleObserver
{
    /**
     * Handle the Module "created" event.
     */
    public function created(Module $module): void
    {
        // الأكشنز الافتراضية
        $actions = ['view', 'create', 'edit', 'delete'];
        $module_name = str_replace(['_', '-'], ' ', strtolower($module->name));

        foreach ($actions as $action) {
            Permission::firstOrCreate([
                'name'       => "{$action} {$module_name}",
                'guard_name' => 'web',
                'module_id'  => $module->id,
            ]);
        }
    }

    /**
     * Handle the Module "updated" event.
     */
    public function updated(Module $module): void
    {
        //
    }

    /**
     * Handle the Module "deleted" event.
     */
    public function deleted(Module $module): void
    {
        //
    }

    /**
     * Handle the Module "restored" event.
     */
    public function restored(Module $module): void
    {
        //
    }

    /**
     * Handle the Module "force deleted" event.
     */
    public function forceDeleted(Module $module): void
    {
        //
    }
}
