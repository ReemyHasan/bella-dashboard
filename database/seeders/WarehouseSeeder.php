<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::create([
            'name' => 'مستودع دمشق الرئيسي',
            'active' => true,
            'is_main' => true,
            'zone_id' => 1
        ]);


        Warehouse::create([
            'name' => 'مستودع بيروت',
            'active' => true,
            'is_main' => false,
            'zone_id' => 2
        ]);
        Warehouse::create([
            'name' => 'مستودع حمص',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع اللاذقية',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع طرطوس',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع درعا',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع حماه',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);


        Warehouse::create([
            'name' => 'مستودع دير الزور',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);


        Warehouse::create([
            'name' => 'مستودع حلب',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع السويداء',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع القنيطرة',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع القامشلي',
            'active' => true,
            'is_main' => false,
            'zone_id' => 3
        ]);

        Warehouse::create([
            'name' => 'مستودع الحسكة',
            'active' => true,
            'is_main' => false,
            'zone_id' => 3
        ]);

        Warehouse::create([
            'name' => 'مستودع الرقة',
            'active' => true,
            'is_main' => false,
            'zone_id' => 3
        ]);

        Warehouse::create([
            'name' => 'مستودع إدلب',
            'active' => true,
            'is_main' => false,
            'zone_id' => 4
        ]);


        Warehouse::create([
            'name' => 'مستودع ديرعطية',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع القطيفة',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع مصياف',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);


        Warehouse::create([
            'name' => 'مستودع منبج',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع شهبا',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع عبد الرزاق',
            'active' => true,
            'is_main' => false,
            'zone_id' => 2
        ]);

        Warehouse::create([
            'name' => 'مستودع بانياس',
            'active' => true,
            'is_main' => true,
            'zone_id' => 1
        ]);


        Warehouse::create([
            'name' => 'مستودع قطنا حسن',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع الزبداني احمد صطوف',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);


        Warehouse::create([
            'name' => 'مستودع الريف الجنوبي محمد الرحمون',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع عدرا',
            'active' => false,
            'is_main' => false,
            'zone_id' => 1
        ]);


        Warehouse::create([
            'name' => 'مستودع دمشق المدينة سيف',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع الغوطة صبحي',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع التل علي رامز',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع قدسيا وسام',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);


        Warehouse::create([
            'name' => 'العراق',
            'active' => true,
            'is_main' => false,
            'zone_id' => 5
        ]);

        Warehouse::create([
            'name' => 'الاردن',
            'active' => true,
            'is_main' => false,
            'zone_id' => 6
        ]);

        Warehouse::create([
            'name' => 'مستودع الحذف',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'مستودع التلف',
            'active' => true,
            'is_main' => false,
            'zone_id' => 1
        ]);

        Warehouse::create([
            'name' => 'ريف حلب',
            'active' => true,
            'is_main' => false,
            'zone_id' => 2
        ]);
    }
}
