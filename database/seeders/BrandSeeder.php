<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $brands = [
            ['سامسونج', 'Samsung', 'إلكترونيات وهواتف ذكية'],
            ['آبل', 'Apple', 'أجهزة آيفون، ماك، آيباد'],
            ['نايكي', 'Nike', 'ملابس وأحذية رياضية'],
            ['أديداس', 'Adidas', 'ملابس وأحذية رياضية'],
            ['كوكاكولا', 'Coca-Cola', 'مشروبات غازية'],
            ['بيبسي', 'Pepsi', 'مشروبات غازية'],
            ['سوني', 'Sony', 'إلكترونيات وكاميرات وألعاب بلايستيشن'],
            ['باناسونيك', 'Panasonic', 'أجهزة منزلية وإلكترونيات'],
            ['إل جي', 'LG', 'إلكترونيات وأجهزة منزلية'],
            ['تويوتا', 'Toyota', 'سيارات ومركبات'],
        ];

        $data = [];
        foreach ($brands as $brand) {
            $data[] = [
                'name'       => $brand[0],
                'name_en'    => $brand[1],
                'notes'      => $brand[2],
                'status'     => 1,
                'created_by' => Auth::id() ?? 1,
                'updated_by' => Auth::id() ?? 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Brand::insert($data);
    }
}
