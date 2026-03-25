<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [

            // Zone 1
            ['zone_id' => 1, 'name' => 'دمشق', 'symbol' => 'M1'],
            ['zone_id' => 1, 'name' => 'حمص', 'symbol' => 'M2'],
            ['zone_id' => 1, 'name' => 'حماة', 'symbol' => 'M3'],
            ['zone_id' => 1, 'name' => 'حلب', 'symbol' => 'M4'],
            ['zone_id' => 1, 'name' => 'طرطوس', 'symbol' => 'M5'],
            ['zone_id' => 1, 'name' => 'اللاذقية', 'symbol' => 'M6'],
            ['zone_id' => 1, 'name' => 'دير الزور', 'symbol' => 'M7'],
            ['zone_id' => 1, 'name' => 'درعا', 'symbol' => 'M8'],
            ['zone_id' => 1, 'name' => 'السويداء', 'symbol' => 'M9'],
            ['zone_id' => 1, 'name' => 'القنيطرة', 'symbol' => 'M10'],
            ['zone_id' => 1, 'name' => 'الحسكة', 'symbol' => 'M11'],
            ['zone_id' => 1, 'name' => 'القامشلي', 'symbol' => 'M12'],
            ['zone_id' => 1, 'name' => 'الرقة', 'symbol' => 'M13'],

            // Zone 5
            ['zone_id' => 5, 'name' => 'بيروت', 'symbol' => 'L1'],
            ['zone_id' => 5, 'name' => 'بعلبك الهرمل', 'symbol' => 'L2'],
            ['zone_id' => 5, 'name' => 'البقاع', 'symbol' => 'BQ'],
            ['zone_id' => 5, 'name' => 'طرابلس / عكار', 'symbol' => 'L4'],
            ['zone_id' => 5, 'name' => 'الجنوب / النبطية', 'symbol' => 'GG'],
            ['zone_id' => 5, 'name' => 'جبل لبنان', 'symbol' => 'GL'],

            // Zone 6
            ['zone_id' => 6, 'name' => 'عمّان', 'symbol' => 'R1'],
            ['zone_id' => 6, 'name' => 'إربد', 'symbol' => 'R2'],

            // Zone 2
            ['zone_id' => 2, 'name' => 'ريف طرطوس', 'symbol' => 'AR3'],
            ['zone_id' => 2, 'name' => 'ريف اللاذقية', 'symbol' => 'AR2'],
            ['zone_id' => 2, 'name' => 'ريف حلب', 'symbol' => 'AR4'],
            ['zone_id' => 2, 'name' => 'ريف دمشق', 'symbol' => 'AR1'],
            ['zone_id' => 2, 'name' => 'ريف حمص', 'symbol' => 'HO'],
            ['zone_id' => 2, 'name' => 'ريف دير الزور', 'symbol' => 'DZ'],
            ['zone_id' => 2, 'name' => 'ريف الرقة', 'symbol' => 'RQ'],
            ['zone_id' => 2, 'name' => 'ريف القنيطرة', 'symbol' => 'QN'],
            ['zone_id' => 2, 'name' => 'ريف حماه', 'symbol' => 'HA'],
            ['zone_id' => 2, 'name' => 'ريف درعا', 'symbol' => 'DR'],
            ['zone_id' => 2, 'name' => 'ريف القامشلي', 'symbol' => 'QA'],
            ['zone_id' => 2, 'name' => 'ريف الحسكة', 'symbol' => 'HS'],

            // Zone 3
            ['zone_id' => 3, 'name' => 'ادلب', 'symbol' => 'ED1'],

            // Zone 4
            ['zone_id' => 4, 'name' => 'ريف ادلب', 'symbol' => 'ED'],
        ];

        City::insert($cities);
    }
}
