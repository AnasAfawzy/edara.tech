<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModulesSeeder extends Seeder
{
    private array $moduleIds = [];

    public function run(): void
    {
        $modules = config('modules');

        foreach ($modules as $module) {
            $this->createOrUpdateModule($module);
        }
    }

    private function createOrUpdateModule(array $data): void
    {
        // هات الـ parent_id لو موجود
        $parentId = null;
        if (!empty($data['parent']) && isset($this->moduleIds[$data['parent']])) {
            $parentId = $this->moduleIds[$data['parent']];
        }

        $module = Module::updateOrCreate(
            ['name' => $data['name']], // نستخدم name مفتاح فريد
            [
                'label' => $data['label'],
                'icon' => $data['icon'] ?? null,
                'route' => $data['route'] ?? null,
                'parent_id' => $parentId,
                'show_in_sidebar' => $data['show_in_sidebar'] ?? true,
            ]
        );

        // خزّن الـ id علشان اللي بعده يعرف parent_id
        $this->moduleIds[$data['name']] = $module->id;
    }
}
