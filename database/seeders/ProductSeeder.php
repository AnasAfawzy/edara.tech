<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ===== Helpers =====
        $cat = fn($en, $ar = null) =>
        Category::where('name_en', $en)->value('id')
            ?? ($ar ? Category::where('name', $ar)->value('id') : null);

        $brand = fn($en, $ar = null) =>
        Brand::where('name_en', $en)->value('id')
            ?? ($ar ? Brand::where('name', $ar)->value('id') : null);

        $unit = fn($symbol) =>
        Unit::where('symbol', $symbol)->value('id')
            ?? Unit::where('name_en', $symbol)->value('id');

        $codeGen = function (string $prefix) {
            return strtoupper($prefix) . '-' . Str::upper(Str::random(6));
        };

        $barcodeGen = function () {
            // 13 digits pseudo EAN
            $digits = '';
            for ($i = 0; $i < 12; $i++) $digits .= random_int(0, 9);
            // simple checksum (not exact EAN, كفاية للاختبار)
            $sum = 0;
            for ($i = 0; $i < 12; $i++) $sum += ((($i + 1) % 2) == 0 ? 3 : 1) * intval($digits[$i]);
            $check = (10 - ($sum % 10)) % 10;
            return $digits . $check;
        };

        // ===== Resolve IDs we need =====
        $catIds = [
            'Electronics'        => $cat('Electronics', 'إلكترونيات'),
            'Mobile Phones'      => $cat('Mobile Phones', 'هواتف محمولة'),
            'Computers'          => $cat('Computers', 'أجهزة كمبيوتر'),
            'TV & Screens'       => $cat('TV & Screens', 'شاشات وتلفزيونات'),
            'Cameras'            => $cat('Cameras', 'كاميرات'),
            'Home Appliances'    => $cat('Home Appliances', 'أجهزة منزلية'),
            'Shoes'              => $cat('Shoes', 'أحذية'),
            'Sports Equipment'   => $cat('Sports Equipment', 'أدوات رياضية'),
            'Beverages'          => $cat('Beverages', 'مشروبات'),
            'Cars & Accessories' => $cat('Cars & Accessories', 'سيارات ولوازمها'),
        ];

        $brandIds = [
            'Samsung'   => $brand('Samsung', 'سامسونج'),
            'Apple'     => $brand('Apple', 'آبل'),
            'Sony'      => $brand('Sony', 'سوني'),
            'LG'        => $brand('LG', 'إل جي'),
            'Panasonic' => $brand('Panasonic', 'باناسونيك'),
            'Nike'      => $brand('Nike', 'نايكي'),
            'Adidas'    => $brand('Adidas', 'أديداس'),
            'Coca-Cola' => $brand('Coca-Cola', 'كوكاكولا'),
            'Pepsi'     => $brand('Pepsi', 'بيبسي'),
            'Toyota'    => $brand('Toyota', 'تويوتا'),
        ];

        $unitIds = [
            'pc'  => $unit('pc'),
            'L'   => $unit('L'),
            'kg'  => $unit('kg'),
            'box' => $unit('box'),
        ];

        // quick assert (لو حاجة ناقصة هنكمّل الباقي ونسيب المفقودين)
        $missing = [];
        foreach ($catIds as $k => $v) if (!$v) $missing[] = "Category:$k";
        foreach ($brandIds as $k => $v) if (!$v) $missing[] = "Brand:$k";
        foreach ($unitIds as $k => $v) if (!$v) $missing[] = "Unit:$k";
        if (!empty($missing)) {
            $this->command->warn('ProductSeeder warnings (missing refs): ' . implode(', ', $missing));
        }

        // ===== Product blueprints (name_ar, name_en, category, brand, unit, purchase, sale, notes) =====
        $products = [];

        // --- Samsung (Electronics / Phones / Screens / Accessories)
        $products = array_merge($products, [
            ['سامسونج جالكسي S24', 'Samsung Galaxy S24 128GB', 'Mobile Phones', 'Samsung', 'pc', 24000, 26999, '5G, AMOLED'],
            ['سامسونج جالكسي A55', 'Samsung Galaxy A55 256GB', 'Mobile Phones', 'Samsung', 'pc', 14000, 15999, 'Great value midrange'],
            ['سامسونج تاب S9', 'Samsung Galaxy Tab S9 11"', 'Electronics', 'Samsung', 'pc', 26000, 29999, 'Snapdragon, AMOLED'],
            ['شاشة سامسونج 55 4K', 'Samsung 55" 4K Crystal UHD', 'TV & Screens', 'Samsung', 'pc', 21000, 23999, 'UHD Smart TV'],
            ['سماعات جالكسي بدز 2 برو', 'Galaxy Buds2 Pro', 'Electronics', 'Samsung', 'pc', 6000, 6999, 'ANC earbuds'],
            ['شاحن سامسونج 25W', 'Samsung 25W USB-C Charger', 'Electronics', 'Samsung', 'pc', 600, 799, 'Fast charging'],
            ['ساعة جالكسي 6', 'Galaxy Watch6 44mm', 'Electronics', 'Samsung', 'pc', 8500, 9999, 'Wear OS'],
            ['SSD سامسونج 1TB', 'Samsung 990 EVO 1TB NVMe', 'Computers', 'Samsung', 'pc', 3300, 3999, 'PCIe 4.0'],
            ['شاشة سامسونج 27 144Hz', 'Samsung 27" 144Hz Monitor', 'Computers', 'Samsung', 'pc', 6500, 7599, 'Gaming monitor'],
            ['مايكروويف سامسونج 32L', 'Samsung Microwave 32L', 'Home Appliances', 'Samsung', 'pc', 5200, 5999, 'Ceramic Inside'],
        ]);

        // --- Apple
        $products = array_merge($products, [
            ['آيفون 15', 'iPhone 15 128GB', 'Mobile Phones', 'Apple', 'pc', 38000, 42999, 'A16, Dynamic Island'],
            ['آيفون 15 برو', 'iPhone 15 Pro 256GB', 'Mobile Phones', 'Apple', 'pc', 48000, 54999, 'A17 Pro'],
            ['آيباد إير 5', 'iPad Air (M1) 64GB', 'Electronics', 'Apple', 'pc', 22000, 24999, '10.9"'],
            ['ماك بوك إير', 'MacBook Air 13" M2 8/256', 'Computers', 'Apple', 'pc', 52000, 58999, 'M2, 13.6"'],
            ['ايربودز برو 2', 'AirPods Pro (2nd Gen)', 'Electronics', 'Apple', 'pc', 8500, 9990, 'ANC'],
            ['ساعة آبل 9', 'Apple Watch Series 9 45mm', 'Electronics', 'Apple', 'pc', 14500, 16999, 'S9 SiP'],
            ['شاحن آبل 96W', 'Apple 96W USB-C Power Adapter', 'Electronics', 'Apple', 'pc', 3300, 3899, 'Fast charge'],
            ['ماوس آبل', 'Apple Magic Mouse', 'Computers', 'Apple', 'pc', 2700, 3199, 'Bluetooth'],
            ['أيرتاج (4 قطع)', 'AirTag 4-pack', 'Electronics', 'Apple', 'pc', 2500, 2990, 'Find My'],
            ['كيبورد ماجيك', 'Apple Magic Keyboard', 'Computers', 'Apple', 'pc', 3200, 3799, 'Rechargeable'],
        ]);

        // --- Sony
        $products = array_merge($products, [
            ['بلايستيشن 5', 'PlayStation 5 Slim', 'Electronics', 'Sony', 'pc', 24500, 27999, 'Disc edition'],
            ['يد تحكم دوال سينس', 'DualSense Wireless Controller', 'Electronics', 'Sony', 'pc', 2200, 2699, 'For PS5'],
            ['سوني WH-1000XM5', 'Sony WH-1000XM5', 'Electronics', 'Sony', 'pc', 9800, 11499, 'ANC headphones'],
            ['كاميرا سوني A7 IV', 'Sony Alpha A7 IV Body', 'Cameras', 'Sony', 'pc', 82000, 94999, 'Full-frame'],
            ['عدسة 50mm سوني', 'Sony FE 50mm F1.8', 'Cameras', 'Sony', 'pc', 7800, 8999, 'Prime lens'],
            ['تلفزيون سوني 65 4K', 'Sony 65" BRAVIA 4K', 'TV & Screens', 'Sony', 'pc', 39000, 44999, 'Google TV'],
            ['سماعات سوني WF-C700N', 'Sony WF-C700N', 'Electronics', 'Sony', 'pc', 4200, 4999, 'ANC buds'],
            ['SD كارت 128GB', 'Sony SDXC 128GB UHS-I', 'Cameras', 'Sony', 'pc', 900, 1199, 'U3 V30'],
            ['ساوند بار سوني', 'Sony HT-S400 Soundbar', 'Electronics', 'Sony', 'pc', 5200, 5999, 'S-Force Pro'],
            ['شاحن كاميرا سوني', 'Sony NP-FZ100 Battery', 'Cameras', 'Sony', 'pc', 2100, 2599, 'Original'],
        ]);

        // --- LG
        $products = array_merge($products, [
            ['تلفزيون LG OLED 55', 'LG OLED C3 55"', 'TV & Screens', 'LG', 'pc', 52000, 59999, 'OLED evo'],
            ['غسالة LG 9Kg', 'LG Front Load 9Kg', 'Home Appliances', 'LG', 'pc', 21000, 23999, 'AI DD'],
            ['ثلاجة LG 18 قدم', 'LG 18ft Refrigerator', 'Home Appliances', 'LG', 'pc', 33000, 37999, 'Inverter'],
            ['شاشة LG 27 2K', 'LG 27" QHD Monitor', 'Computers', 'LG', 'pc', 6800, 7999, 'IPS 144Hz'],
            ['ساوند بار LG', 'LG SN5 Soundbar', 'Electronics', 'LG', 'pc', 5200, 5999, 'DTS Virtual:X'],
            ['مكيف LG 1.5 حصان', 'LG AC 1.5 HP', 'Home Appliances', 'LG', 'pc', 24500, 27999, 'Dual Inverter'],
            ['شاشة LG 34 UltraWide', 'LG 34" UltraWide', 'Computers', 'LG', 'pc', 14500, 16999, 'WFHD'],
            ['ميكروويف LG 42L', 'LG Microwave 42L', 'Home Appliances', 'LG', 'pc', 6200, 7299, 'Smart Inverter'],
            ['منقي هواء LG', 'LG Air Purifier', 'Home Appliances', 'LG', 'pc', 7800, 8999, 'HEPA'],
            ['ماوس LG', 'LG Wireless Mouse', 'Computers', 'LG', 'pc', 300, 499, '2.4G'],
        ]);

        // --- Panasonic
        $products = array_merge($products, [
            ['مايكروويف باناسونيك', 'Panasonic Microwave NN-SN966', 'Home Appliances', 'Panasonic', 'pc', 7200, 8499, 'Inverter'],
            ['مكانس باناسونيك', 'Panasonic Vacuum 2000W', 'Home Appliances', 'Panasonic', 'pc', 5200, 5999, 'Bagless'],
            ['بطاريات AA (4)', 'Panasonic AA Batteries (4)', 'Electronics', 'Panasonic', 'box', 90, 149, 'Alkaline'],
            ['تليفون لاسلكي', 'Panasonic Cordless Phone', 'Electronics', 'Panasonic', 'pc', 850, 1099, 'DECT'],
            ['خلاط باناسونيك', 'Panasonic Blender 600W', 'Home Appliances', 'Panasonic', 'pc', 1200, 1499, 'Glass jar'],
            ['مكواة بخار', 'Panasonic Steam Iron', 'Home Appliances', 'Panasonic', 'pc', 900, 1199, 'Anti-drip'],
            ['شاشة 24 بوصة', 'Panasonic 24" HD TV', 'TV & Screens', 'Panasonic', 'pc', 3800, 4499, 'Smart'],
            ['سخان مياه', 'Panasonic Water Heater 50L', 'Home Appliances', 'Panasonic', 'pc', 4500, 5299, 'Vertical'],
            ['سماعات أذن', 'Panasonic In-Ear Earphones', 'Electronics', 'Panasonic', 'pc', 120, 199, 'Wired'],
            ['محمصة خبز', 'Panasonic Toaster', 'Home Appliances', 'Panasonic', 'pc', 700, 999, '2-slice'],
        ]);

        // --- Nike (Shoes / Sports)
        $products = array_merge($products, [
            ['نايكي إير فورس 1', 'Nike Air Force 1', 'Shoes', 'Nike', 'pc', 3800, 4499, 'Lifestyle'],
            ['نايكي إير ماكس 97', 'Nike Air Max 97', 'Shoes', 'Nike', 'pc', 5200, 5999, 'Running'],
            ['نايكي رن', 'Nike Revolution 6', 'Shoes', 'Nike', 'pc', 2200, 2799, 'Running entry'],
            ['تيشيرت دراي-فت', 'Nike Dri-FIT Tee', 'Sports Equipment', 'Nike', 'pc', 450, 699, 'Training'],
            ['شورت تدريب', 'Nike Training Shorts', 'Sports Equipment', 'Nike', 'pc', 600, 899, 'Men'],
            ['حقيبة جيم', 'Nike Gym Duffel', 'Sports Equipment', 'Nike', 'pc', 900, 1299, 'Medium'],
            ['قبعة كاب', 'Nike Heritage Cap', 'Sports Equipment', 'Nike', 'pc', 300, 499, 'Adjustable'],
            ['شرابات رياضية (3)', 'Nike Socks (3 Pairs)', 'Sports Equipment', 'Nike', 'box', 120, 199, 'Cotton'],
            ['زجاجة ماء', 'Nike Water Bottle 750ml', 'Sports Equipment', 'Nike', 'pc', 120, 199, 'BPA-free'],
            ['حذاء كرة قدم', 'Nike Mercurial Vapor', 'Shoes', 'Nike', 'pc', 4200, 4999, 'FG'],
        ]);

        // --- Adidas (Shoes / Sports)
        $products = array_merge($products, [
            ['أديداس ألترا بوست', 'Adidas Ultraboost 22', 'Shoes', 'Adidas', 'pc', 5200, 5999, 'Running'],
            ['سوبر ستار', 'Adidas Superstar', 'Shoes', 'Adidas', 'pc', 3600, 4299, 'Classic'],
            ['ستان سميث', 'Adidas Stan Smith', 'Shoes', 'Adidas', 'pc', 3300, 3999, 'Tennis classic'],
            ['تيشيرت 3-سترايبس', 'Adidas 3-Stripes Tee', 'Sports Equipment', 'Adidas', 'pc', 400, 649, 'Cotton'],
            ['شورت رياضي', 'Adidas Training Shorts', 'Sports Equipment', 'Adidas', 'pc', 550, 799, 'AEROREADY'],
            ['جاكيت رياضي', 'Adidas Track Jacket', 'Sports Equipment', 'Adidas', 'pc', 1200, 1599, 'Primegreen'],
            ['حقيبة ظهر', 'Adidas Backpack', 'Sports Equipment', 'Adidas', 'pc', 600, 899, 'Daily'],
            ['شرابات (3)', 'Adidas Socks (3 Pairs)', 'Sports Equipment', 'Adidas', 'box', 100, 179, 'Crew'],
            ['زجاجة ماء', 'Adidas Water Bottle 750ml', 'Sports Equipment', 'Adidas', 'pc', 100, 169, 'BPA-free'],
            ['كرة قدم', 'Adidas Soccer Ball', 'Sports Equipment', 'Adidas', 'pc', 450, 699, 'Size 5'],
        ]);

        // --- Beverages (Coca-Cola & Pepsi) - many SKUs to reach 100
        $beverageVariants = [
            // size (L), sale price, notes
            ['0.25', 12, '250ml can', 'pc'],
            ['0.33', 15, '330ml can', 'pc'],
            ['0.5', 22, '500ml bottle', 'pc'],
            ['1', 30, '1L bottle', 'pc'],
            ['2.25', 45, '2.25L bottle', 'pc'],
        ];
        $beveragePacks = [
            // pack name, unit=box, sale price
            ['Pack of 6 (330ml)', 85],
            ['Pack of 12 (330ml)', 165],
            ['Pack of 24 (330ml)', 320],
        ];

        foreach (['Coca-Cola', 'Pepsi'] as $bevBrand) {
            foreach ($beverageVariants as [$liters, $sale, $note, $unitSym]) {
                $products[] = [
                    $bevBrand === 'Coca-Cola' ? "كوكاكولا {$liters} لتر" : "بيبسي {$liters} لتر",
                    $bevBrand . ' ' . $liters . 'L',
                    'Beverages',
                    $bevBrand,
                    $unitSym,
                    max(1, $sale - 5),
                    $sale,
                    $note
                ];
            }
            foreach ($beveragePacks as [$packName, $sale]) {
                $products[] = [
                    $bevBrand === 'Coca-Cola' ? "كرتونة {$packName} كوكاكولا" : "كرتونة {$packName} بيبسي",
                    $bevBrand . ' ' . $packName,
                    'Beverages',
                    $bevBrand,
                    'box',
                    max(1, $sale - 20),
                    $sale,
                    'Assorted cans pack'
                ];
            }
        }

        // --- Toyota (Car accessories & fluids)
        $products = array_merge($products, [
            ['فلتر زيت تويوتا', 'Toyota Oil Filter 90915-YZZE1', 'Cars & Accessories', 'Toyota', 'pc', 180, 260, 'Genuine'],
            ['فلتر هواء تويوتا', 'Toyota Air Filter', 'Cars & Accessories', 'Toyota', 'pc', 220, 320, 'Genuine'],
            ['فلتر تكييف', 'Toyota Cabin Air Filter', 'Cars & Accessories', 'Toyota', 'pc', 200, 300, 'Carbon'],
            ['زيت محرك 5W-30 1L', 'Toyota Motor Oil 5W-30 1L', 'Cars & Accessories', 'Toyota', 'L', 180, 240, 'Synthetic'],
            ['سائل تبريد 1L', 'Toyota Coolant 1L', 'Cars & Accessories', 'Toyota', 'L', 120, 180, 'Red'],
            ['بوجيهات (قطعة)', 'Toyota Spark Plug', 'Cars & Accessories', 'Toyota', 'pc', 90, 140, 'Iridium'],
            ['مساحات زجاج', 'Toyota Wiper Blade 24"', 'Cars & Accessories', 'Toyota', 'pc', 120, 180, 'Driver side'],
            ['بطارية 55Ah', 'Toyota 55Ah Battery', 'Cars & Accessories', 'Toyota', 'pc', 1800, 2199, 'Maintenance-free'],
            ['فحمات فرامل أمامي', 'Toyota Front Brake Pads', 'Cars & Accessories', 'Toyota', 'box', 700, 899, 'Genuine'],
            ['سير مجموعة', 'Toyota Drive Belt', 'Cars & Accessories', 'Toyota', 'pc', 350, 499, 'OEM'],
        ]);

        // لحد هنا عندنا ~100 منتج. هنحوّلهم إلى صفوف للإدخال.
        $rows = [];
        foreach ($products as $p) {
            [$nameAr, $nameEn, $catKey, $brandKey, $unitSym, $purchase, $sale, $notes] = $p;

            $category_id = $catIds[$catKey] ?? null;
            $brand_id    = $brandIds[$brandKey] ?? null;
            $unit_id     = $unitIds[$unitSym] ?? null;

            // لو حاجة أساسية ناقصة، نتخطى المنتج ده بدل ما نفشل السييد
            if (!$category_id || !$brand_id || !$unit_id) {
                continue;
            }

            $rows[] = [
                'name'          => $nameAr,
                'name_en'       => $nameEn,
                'code'          => $codeGen(Str::slug($brandKey . '-' . $nameEn)),
                'barcode'       => $barcodeGen(),
                'category_id'   => $category_id,
                'brand_id'      => $brand_id,
                'unit_id'       => $unit_id,
                'description'   => $notes,
                'notes'         => $notes,
                'image'         => null, // تقدر تضيف صور لاحقًا
                'purchase_price' => $purchase,
                'sale_price'    => $sale,
                'min_stock'     => 5,
                'max_stock'     => 200,
                'current_stock' => random_int(5, 120),
                'reorder_level' => 10,
                'has_expiry'    => in_array($catKey, ['Beverages']) ? 1 : 0,
                'is_active'     => 1,
                'created_by'    => 1,
                'updated_by'    => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        // لو أكتر من 100، قصّهم لـ 100 بالظبط
        if (count($rows) > 100) {
            $rows = array_slice($rows, 0, 100);
        }

        // إدخال مجمّع
        if (!empty($rows)) {
            // chunk لو كبير
            foreach (array_chunk($rows, 50) as $chunk) {
                Product::insert($chunk);
            }
        }
    }
}
