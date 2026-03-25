<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\UserRequestType;
use App\Models\Vault;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vault::create([]);
        PaymentMethod::create([
            'name_en' => 'Cash',
            'name_ar' => 'كاش باليد'
        ]);

        PaymentMethod::create([
            'name_en' => 'Money Transfer',
            'name_ar' => 'حوالة'
        ]);


        PaymentMethod::create([
            'name_en' => 'Units Transfer',
            'name_ar' => 'رصيد وحدات'
        ]);

        PaymentMethod::create([
            'name_en' => 'Delivery',
            'name_ar' => 'توصيل'
        ]);

        // PaymentMethod::create([
        //     'name_en' => 'Purchase Products',
        //     'name_ar' => 'شراء مواد'
        // ]);


        UserRequestType::create([
            'name' => 'سلفة'

        ]);

         UserRequestType::create([
            'name' => 'شكوى'
        ]);
         UserRequestType::create([
            'name' => 'ترقية'
        ]);
    }
}
