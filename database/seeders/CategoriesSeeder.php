<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Category;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            ['إلكترونيات', 'Electronics', 'هواتف، لابتوبات، أجهزة ذكية'],
            ['ملابس رجالي', 'Men Clothing', 'قمصان، بناطيل، بدلات'],
            ['ملابس حريمي', 'Women Clothing', 'فساتين، عبايات، ملابس رياضية'],
            ['ملابس أطفال', 'Kids Clothing', 'ملابس للأولاد والبنات'],
            ['أحذية', 'Shoes', 'أحذية رجالي، حريمي، أطفال'],
            ['حقائب', 'Bags', 'شنط يد، ظهر، سفر'],
            ['ساعات وإكسسوارات', 'Watches & Accessories', 'ساعات رجالي وحريمي، نظارات شمسية'],
            ['أجهزة منزلية', 'Home Appliances', 'ثلاجات، غسالات، بوتاجازات'],
            ['أثاث', 'Furniture', 'غرف نوم، سفرة، كراسي، مكاتب'],
            ['أدوات مطبخ', 'Kitchenware', 'أواني، أدوات طهي، أطباق'],
            ['بقالة', 'Groceries', 'أرز، سكر، زيت، مكرونة'],
            ['مشروبات', 'Beverages', 'عصائر، مياه، مشروبات غازية'],
            ['لحوم ودواجن', 'Meat & Poultry', 'لحوم طازجة ومجمدة'],
            ['أسماك', 'Seafood', 'أسماك، جمبري، تونة'],
            ['فواكه', 'Fruits', 'تفاح، موز، برتقال'],
            ['خضروات', 'Vegetables', 'طماطم، خيار، بطاطس'],
            ['مخبوزات', 'Bakery', 'خبز، كيك، بسكويت'],
            ['حلويات', 'Sweets', 'شوكولاتة، حلويات شرقية وغربية'],
            ['معلبات', 'Canned Food', 'تونة، فول، صلصة، ذرة'],
            ['ألبان وأجبان', 'Dairy & Cheese', 'لبن، زبادي، أجبان'],
            ['مستلزمات أطفال', 'Baby Products', 'حفاضات، حليب أطفال، ألعاب'],
            ['ألعاب أطفال', 'Toys', 'ألعاب تعليمية وترفيهية'],
            ['مستحضرات تجميل', 'Cosmetics', 'مكياج، عطور، عناية بالبشرة'],
            ['منتجات شعر', 'Hair Care', 'شامبو، بلسم، صبغات'],
            ['منتجات عناية شخصية', 'Personal Care', 'صابون، معجون أسنان، مزيل عرق'],
            ['منظفات منزلية', 'Cleaning Supplies', 'مساحيق، مطهرات، صابون أطباق'],
            ['أدوات مكتبية', 'Stationery', 'أقلام، دفاتر، طابعات'],
            ['كتب', 'Books', 'روايات، تعليمية، أطفال'],
            ['أجهزة كمبيوتر', 'Computers', 'PC, لابتوبات، ملحقات'],
            ['هواتف محمولة', 'Mobile Phones', 'أندرويد وآيفون'],
            ['أجهزة لوحية', 'Tablets', 'iPad وأجهزة تابلت أخرى'],
            ['شاشات وتلفزيونات', 'TV & Screens', 'LED, Smart TV'],
            ['كاميرات', 'Cameras', 'كاميرات ديجيتال واحترافية'],
            ['أدوات رياضية', 'Sports Equipment', 'أدوات جيم، كرة، مضارب'],
            ['معدات سفر وتخييم', 'Travel & Camping', 'شنط سفر، خيام، أدوات'],
            ['سيارات ولوازمها', 'Cars & Accessories', 'قطع غيار، كماليات'],
            ['دراجات ولوازمها', 'Bikes & Accessories', 'دراجات هوائية ونارية'],
            ['مجوهرات', 'Jewelry', 'ذهب، فضة، إكسسوارات'],
            ['معدات طبية', 'Medical Supplies', 'أجهزة قياس، كمامات، أدوات إسعاف'],
            ['أدوية', 'Medicines', 'أدوية وصيدلية'],
            ['معدات صناعية', 'Industrial Equipment', 'أدوات ومعدات مصانع'],
            ['خدمات رقمية', 'Digital Services', 'اشتراكات، كروت شحن'],
            ['ألعاب فيديو', 'Video Games', 'بلايستيشن، Xbox، PC Games'],
            ['موسيقى وأفلام', 'Music & Movies', 'CD, DVD, BluRay'],
            ['هدايا وتحف', 'Gifts & Antiques', 'هدايا للمناسبات وتحف فنية'],
            ['زراعة وحدائق', 'Gardening', 'بذور، نباتات، أدوات حدائق'],
            ['حيوانات أليفة', 'Pets', 'طعام وأدوات للقطط والكلاب'],
            ['معدات بناء', 'Construction Materials', 'أسمنت، حديد، خشب'],
            ['دهانات وديكور', 'Paints & Decoration', 'دهانات وأدوات تشطيب'],
            ['خدمات أخرى', 'Other Services', 'تصنيفات متنوعة إضافية'],
        ];

        $data = [];
        foreach ($categories as $cat) {
            $data[] = [
                'name'       => $cat[0],
                'name_en'    => $cat[1],
                'notes'      => $cat[2],
                'status'     => 1,
                'created_by' => Auth::id() ?? 1,
                'updated_by' => Auth::id() ?? 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Category::insert($data);
    }
}
