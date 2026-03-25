<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::create([
            'name' => 'ليرة سورية',
            'symbol' => 'ل.س',
            'is_main' => true,
            'exchange_value' => 1,
        ]);

        Currency::create([
            'name' => 'دولار',
            'symbol' => '$',
            'is_main' => false,
            'exchange_value' => 11000,
        ]);


        Currency::create([
            'name' => 'دينار عراقي',
            'symbol' => 'IQD',
            'is_main' => false,
            'exchange_value' => 10,
        ]);

        Currency::create([
            'name' => 'دينار أردني',
            'symbol' => 'JOD',
            'is_main' => false,
            'exchange_value' => 13000,
        ]);

        Currency::create([
            'name' => 'ليرة تركي',
            'symbol' => 'TR',
            'is_main' => false,
            'exchange_value' => 300,
        ]);
    }
}
