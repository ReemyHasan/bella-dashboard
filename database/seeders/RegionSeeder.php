<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            ['city_id' => 1, 'warehouse_id' => 1, 'name' => 'الميدان (حسن)', 'symbol' => 'حسن'],
            ['city_id' => 6, 'warehouse_id' => 1, 'name' => 'اللاذقية', 'symbol' => 'يازد'],
            ['city_id' => 23, 'warehouse_id' => 1, 'name' => 'جبلة', 'symbol' => 'يازد'],
            ['city_id' => 23, 'warehouse_id' => 1, 'name' => 'القرداحة', 'symbol' => 'يازد'],
            ['city_id' => 5, 'warehouse_id' => 1, 'name' => 'طرطوس', 'symbol' => 'جابر'],
            ['city_id' => 19, 'warehouse_id' => 1, 'name' => 'صافيتا', 'symbol' => 'جابر'],
            ['city_id' => 19, 'warehouse_id' => 1, 'name' => 'الدريكيش', 'symbol' => 'جابر'],
            ['city_id' => 19, 'warehouse_id' => 1, 'name' => 'الشيخ بدر', 'symbol' => 'جابر'],
            ['city_id' => 19, 'warehouse_id' => 1, 'name' => 'القدموس', 'symbol' => 'جابر'],
            ['city_id' => 19, 'warehouse_id' => 1, 'name' => 'بانياس', 'symbol' => 'جابر'],
            ['city_id' => 2, 'warehouse_id' => 1, 'name' => 'حمص', 'symbol' => 'عمار'],
            ['city_id' => 26, 'warehouse_id' => 1, 'name' => 'ريف حمص', 'symbol' => 'عمار'],
            ['city_id' => 8, 'warehouse_id' => 1, 'name' => 'درعا المدينة', 'symbol' => 'سلاف'],
            ['city_id' => 32, 'warehouse_id' => 1, 'name' => 'ريف درعا', 'symbol' => 'سلاف'],
            ['city_id' => 3, 'warehouse_id' => 1, 'name' => 'حماة المدينة', 'symbol' => 'عيسى'],
            ['city_id' => 31, 'warehouse_id' => 1, 'name' => 'مصياف', 'symbol' => 'مالك'],
            ['city_id' => 31, 'warehouse_id' => 1, 'name' => 'سلمية', 'symbol' => 'مالك'],
            ['city_id' => 31, 'warehouse_id' => 1, 'name' => 'محردة', 'symbol' => 'مالك'],
            ['city_id' => 31, 'warehouse_id' => 1, 'name' => 'سلحب', 'symbol' => 'مالك'],
            ['city_id' => 31, 'warehouse_id' => 1, 'name' => 'سقيلبية', 'symbol' => 'مالك'],
            ['city_id' => 7, 'warehouse_id' => 1, 'name' => 'دير الزور', 'symbol' => 'شهناز'],
            ['city_id' => 28, 'warehouse_id' => 1, 'name' => 'الريف الغربي', 'symbol' => 'شهناز'],
            ['city_id' => 4, 'warehouse_id' => 1, 'name' => 'حلب المدينة', 'symbol' => 'خالد'],
            ['city_id' => 21, 'warehouse_id' => 1, 'name' => 'ريف حلب (علي)', 'symbol' => '....'],
            ['city_id' => 21, 'warehouse_id' => 1, 'name' => 'منبج', 'symbol' => 'حمادي'],
            ['city_id' => 21, 'warehouse_id' => 1, 'name' => 'كوباني', 'symbol' => 'حمادي'],
            ['city_id' => 9, 'warehouse_id' => 1, 'name' => 'السويداء المدينة', 'symbol' => 'اياد'],
            ['city_id' => 9, 'warehouse_id' => 1, 'name' => 'شهبا', 'symbol' => 'عفاف'],
            ['city_id' => 10, 'warehouse_id' => 1, 'name' => 'القنيطرة المدينة', 'symbol' => 'ناصيف'],
            ['city_id' => 12, 'warehouse_id' => 1, 'name' => 'مدينة القامشلي', 'symbol' => 'عمر'],
            ['city_id' => 35, 'warehouse_id' => 1, 'name' => 'ريف القامشلي', 'symbol' => 'عمر'],
            ['city_id' => 11, 'warehouse_id' => 1, 'name' => 'الحسكة', 'symbol' => 'احمد'],
            ['city_id' => 13, 'warehouse_id' => 1, 'name' => 'الرقة', 'symbol' => 'مها'],
            ['city_id' => 29, 'warehouse_id' => 1, 'name' => 'الطبقة', 'symbol' => 'مها'],
            ['city_id' => 22, 'warehouse_id' => 1, 'name' => 'إدلب المدينة', 'symbol' => 'احمد المحيميد'],
            ['city_id' => 23, 'warehouse_id' => 1, 'name' => 'الريف الشمالي (دير عطية)', 'symbol' => 'سعد'],
            ['city_id' => 23, 'warehouse_id' => 1, 'name' => 'القلمون الغربي(صيدنايا)', 'symbol' => 'حسين'],
            ['city_id' => 23, 'warehouse_id' => 1, 'name' => 'القلمون الشرقي (القطيفة)', 'symbol' => 'حسين'],
            ['city_id' => 23, 'warehouse_id' => 1, 'name' => 'الريف الغربي (قطنا)', 'symbol' => 'حسن'],
            ['city_id' => 23, 'warehouse_id' => 1, 'name' => 'الريف الجنوبي', 'symbol' => 'محمد'],
            ['city_id' => 23, 'warehouse_id' => 1, 'name' => 'الريف الغربي (الزبداني)', 'symbol' => 'صطوف'],
            ['city_id' => 23, 'warehouse_id' => 1, 'name' => 'الريف الشرقي(الغوطة)', 'symbol' => 'صبحي'],
            ['city_id' => 1, 'warehouse_id' => 2, 'name' => 'دمر (وسام)', 'symbol' => 'وسام'],
            ['city_id' => 23, 'warehouse_id' => 2, 'name' => 'الريف الشرقي (طريق المطار)', 'symbol' => 'صبحي'],
            ['city_id' => 1, 'warehouse_id' => 2, 'name' => 'نهر عيشة (حسن)', 'symbol' => 'حسن'],
            ['city_id' => 1, 'warehouse_id' => 2, 'name' => 'كفرسوسة (حسن)', 'symbol' => 'حسن'],
            ['city_id' => 1, 'warehouse_id' => 2, 'name' => 'ركن الدين (وسام)', 'symbol' => 'وسام'],
            ['city_id' => 30, 'warehouse_id' => 2, 'name' => 'ريف القنيطرة', 'symbol' => 'ناصيف'],
            ['city_id' => 1, 'warehouse_id' => 2, 'name' => 'دمشق القديمة (وسام)', 'symbol' => 'وسام'],
            ['city_id' => 1, 'warehouse_id' => 2, 'name' => 'العباسيين (وسام)', 'symbol' => 'وسام'],
            ['city_id' => 1, 'warehouse_id' => 2, 'name' => 'المزه (سيف)', 'symbol' => 'سيف الدين'],
            ['city_id' => 1, 'warehouse_id' => 2, 'name' => 'البرامكة وما حولها (سيف)', 'symbol' => 'سيف الدين'],
            ['city_id' => 23, 'warehouse_id' => 2, 'name' => 'الريف الغربي (التل)', 'symbol' => 'علي'],
            ['city_id' => 23, 'warehouse_id' => 2, 'name' => 'الريف الشرقي (جرمانا)', 'symbol' => 'صبحي'],
            ['city_id' => 31, 'warehouse_id' => 3, 'name' => 'الريف الغربي', 'symbol' => 'مالك'],
            ['city_id' => 31, 'warehouse_id' => 3, 'name' => 'ام الطيور', 'symbol' => 'مالك'],
            ['city_id' => 23, 'warehouse_id' => 3, 'name' => 'ببيلا (محمد)', 'symbol' => 'محمد'],
            ['city_id' => 20, 'warehouse_id' => 3, 'name' => 'ريف اللاذقية', 'symbol' => 'يازد'],
            ['city_id' => 33, 'warehouse_id' => 3, 'name' => 'ريف ادلب الجنوبي الغربي', 'symbol' => 'احمد المحيميد'],
            ['city_id' => 33, 'warehouse_id' => 3, 'name' => 'ريف ادلب الشمالي الشرقي', 'symbol' => 'احمد المحيميد'],
            ['city_id' => 15, 'warehouse_id' => 3, 'name' => 'بيروت', 'symbol' => 'بيروت'],
            ['city_id' => 25, 'warehouse_id' => 3, 'name' => 'طرابلس', 'symbol' => 'طرابلس'],
            ['city_id' => 16, 'warehouse_id' => 3, 'name' => 'بعلبك', 'symbol' => 'بعلبك'],
            ['city_id' => 1, 'warehouse_id' => 3, 'name' => 'برزة (وسام)', 'symbol' => 'وسام'],
            ['city_id' => 23, 'warehouse_id' => 3, 'name' => 'عبد الرزاق', 'symbol' => 'تجربة'],
            ['city_id' => 18, 'warehouse_id' => 3, 'name' => 'إربد', 'symbol' => '0'],
            ['city_id' => 17, 'warehouse_id' => 3, 'name' => 'السلط', 'symbol' => 'موزع الاردن'],
            ['city_id' => 17, 'warehouse_id' => 3, 'name' => 'عمان', 'symbol' => 'موزع الاردن'],
            ['city_id' => 17, 'warehouse_id' => 3, 'name' => 'الكرك', 'symbol' => 'موزع الاردن'],
            ['city_id' => 17, 'warehouse_id' => 3, 'name' => 'الزرقاء', 'symbol' => 'موزع الاردن'],
            ['city_id' => 17, 'warehouse_id' => 4, 'name' => 'عجلون', 'symbol' => 'موزع الاردن'],
            ['city_id' => 17, 'warehouse_id' => 4, 'name' => 'جرش', 'symbol' => 'موزع الاردن'],
            ['city_id' => 17, 'warehouse_id' => 4, 'name' => 'الرصيفة', 'symbol' => 'موزع الاردن'],
            ['city_id' => 19, 'warehouse_id' => 4, 'name' => 'ريف طرطوس', 'symbol' => 'mms'],
            ['city_id' => 35, 'warehouse_id' => 4, 'name' => 'ريف الحسكة', 'symbol' => 'Mm'],
            ['city_id' => 1, 'warehouse_id' => 4, 'name' => 'مستودع الحذف', 'symbol' => '00000000'],
            ['city_id' => 25, 'warehouse_id' => 4, 'name' => 'عكار', 'symbol' => '33'],
            ['city_id' => 35, 'warehouse_id' => 4, 'name' => 'الجنوب', 'symbol' => '11'],
            ['city_id' => 35, 'warehouse_id' => 4, 'name' => 'النبطية', 'symbol' => '22'],
            ['city_id' => 24, 'warehouse_id' => 4, 'name' => 'البقاع', 'symbol' => '33'],
            ['city_id' => 24, 'warehouse_id' => 4, 'name' => 'زحلة', 'symbol' => '44'],
            ['city_id' => 35, 'warehouse_id' => 4, 'name' => 'جبل لبنان', 'symbol' => '55'],
            ['city_id' => 16, 'warehouse_id' => 4, 'name' => 'الهرمل', 'symbol' => '66'],
            ['city_id' => 1, 'warehouse_id' => 4, 'name' => 'عش الورور (علي)', 'symbol' => 'علي'],
            ['city_id' => 21, 'warehouse_id' => 5, 'name' => 'ريف حلب الغربي', 'symbol' => '..'],
            ['city_id' => 21, 'warehouse_id' => 5, 'name' => 'ريف حلب الشرقي', 'symbol' => '..'],
            ['city_id' => 21, 'warehouse_id' => 5, 'name' => 'ريف حلب الجنوبي', 'symbol' => '..'],
            ['city_id' => 21, 'warehouse_id' => 5, 'name' => 'ريف حلب الشمالي', 'symbol' => '..'],
        ];

        Region::insert($regions);
    }
}
