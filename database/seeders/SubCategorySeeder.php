<?php

namespace Database\Seeders;

use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        SubCategory::create([
            'name' => 'زيوت',
            'active' => true,
            'main_category_id' => 1
        ]);
        SubCategory::create([
            'name' => 'سيرومات',
            'active' => true,
            'main_category_id' => 1
        ]);
        SubCategory::create([
            'name' => 'شامبو',
            'active' => true,
            'main_category_id' => 1
        ]);
        SubCategory::create([
            'name' => 'كريمات & جل',
            'active' => true,
            'main_category_id' => 1
        ]);
        SubCategory::create([
            'name' => 'أدوات',
            'active' => true,
            'main_category_id' => 1
        ]);

        ////////////////////
        SubCategory::create([
            'name' => 'كبسولات',
            'active' => true,
            'main_category_id' => 2
        ]);
        SubCategory::create([
            'name' => 'أعشاب',
            'active' => true,
            'main_category_id' => 2
        ]);

        SubCategory::create([
            'name' => 'كريمات',
            'active' => true,
            'main_category_id' => 2
        ]);
        SubCategory::create([
            'name' => 'مشدات',
            'active' => true,
            'main_category_id' => 2
        ]);
        //////////////////////////
        SubCategory::create([
            'name' => 'كبسولات',
            'active' => true,
            'main_category_id' => 3
        ]);
        SubCategory::create([
            'name' => 'أعشاب',
            'active' => true,
            'main_category_id' => 3
        ]);
        //////////////////////////
        SubCategory::create([
            'name' => 'رجالية',
            'active' => true,
            'main_category_id' => 4
        ]);
        SubCategory::create([
            'name' => 'نسائية',
            'active' => true,
            'main_category_id' => 4
        ]);
        //////////////////////////

        SubCategory::create([
            'name' => 'كبسولات',
            'active' => true,
            'main_category_id' => 5
        ]);
        SubCategory::create([
            'name' => 'كريمات',
            'active' => true,
            'main_category_id' => 5
        ]);
        SubCategory::create([
            'name' => 'أعشاب',
            'active' => true,
            'main_category_id' => 5
        ]);
        SubCategory::create([
            'name' => 'زيوت',
            'active' => true,
            'main_category_id' => 5
        ]);
        SubCategory::create([
            'name' => 'صوابين',
            'active' => true,
            'main_category_id' => 5
        ]);
        //////////////////////////

        SubCategory::create([
            'name' => 'كريمات',
            'active' => true,
            'main_category_id' => 6
        ]);
        SubCategory::create([
            'name' => 'سيرومات',
            'active' => true,
            'main_category_id' => 6
        ]);
        SubCategory::create([
            'name' => 'تونر',
            'active' => true,
            'main_category_id' => 6
        ]);
        SubCategory::create([
            'name' => 'صوابين & غسول',
            'active' => true,
            'main_category_id' => 6
        ]);
        SubCategory::create([
            'name' => 'مكياج & عناية',
            'active' => true,
            'main_category_id' => 6
        ]);


        //////////////////////////
        SubCategory::create([
            'name' => 'كبسولات',
            'active' => true,
            'main_category_id' => 7
        ]);
        //////////////////////////
        SubCategory::create([
            'name' => 'بخاخ',
            'active' => true,
            'main_category_id' => 1
        ]);

        //////////////////////////
        SubCategory::create([
            'name' => 'مكياج',
            'active' => true,
            'main_category_id' => 8
        ]);

        //////////////////////////
        SubCategory::create([
            'name' => 'أدوات',
            'active' => true,
            'main_category_id' => 7
        ]);
        //////////////////////////

        SubCategory::create([
            'name' => 'كبسولات',
            'active' => true,
            'main_category_id' => 8
        ]);

        SubCategory::create([
            'name' => 'كريمات',
            'active' => true,
            'main_category_id' => 8
        ]);
        SubCategory::create([
            'name' => 'اعشاب',
            'active' => true,
            'main_category_id' => 8
        ]);
        SubCategory::create([
            'name' => 'سيرومات & زيوت',
            'active' => true,
            'main_category_id' => 8
        ]);
        SubCategory::create([
            'name' => 'منوعة',
            'active' => true,
            'main_category_id' => 8
        ]);
    }
}
