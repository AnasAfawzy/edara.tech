<?php
// filepath: d:\xampp\htdocs\edara.tech_old\database\seeders\UnitsSeeder.php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $user = Auth::id();
        $units = [
            [
                'name'       => 'قطعة',
                'name_en'    => 'Piece',
                'symbol'     => 'pc',
                'notes'      => 'الوحدة الأساسية للمنتجات الفردية',
                'status'     => 1,
                'created_by' => $user,
                'updated_by' => $user,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'كيلوجرام',
                'name_en'    => 'Kilogram',
                'symbol'     => 'kg',
                'notes'      => 'للوزن',
                'status'     => 1,
                'created_by' => $user,
                'updated_by' => $user,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'لتر',
                'name_en'    => 'Liter',
                'symbol'     => 'L',
                'notes'      => 'للسوائل',
                'status'     => 1,
                'created_by' => $user,
                'updated_by' => $user,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'علبة',
                'name_en'    => 'Box',
                'symbol'     => 'box',
                'notes'      => 'للعبوات',
                'status'     => 1,
                'created_by' => $user,
                'updated_by' => $user,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        Unit::insert($units);
    }
}
