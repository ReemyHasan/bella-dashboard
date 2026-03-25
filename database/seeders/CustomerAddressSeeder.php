<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Address;
use App\Models\Customer;
use App\Models\DashUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class CustomerAddressSeeder extends Seeder
{

    public function run(): void
    {
        // -----------------------
        // CREATE ADDRESSES
        // -----------------------
        $addresses = [
            ['name' => 'الحي الأول، شارع الملك', 'region_id' => 1],
            ['name' => 'الحي الثاني، شارع الجامعة', 'region_id' => 2],
            ['name' => 'الحي الثالث، شارع الصناعة', 'region_id' => 3],
        ];

        foreach ($addresses as $addr) {
            Address::create($addr);
        }

        // -----------------------
        // CREATE CUSTOMERS
        // -----------------------
        $customers = [
            [
                'first_name' => 'أحمد',
                'last_name' => 'محمد',
                'user_name' => 'ahmed_m',
                'password' => Hash::make('password123'),
                'mobile' => '0591111111',
                'created_by_id' => 1,
                'created_by_type' => DashUser::class,

            ],
            [
                'first_name' => 'سارة',
                'last_name' => 'علي',
                'user_name' => 'sara_a',
                'password' => Hash::make('password123'),
                'mobile' => '0592222222',
                'created_by_id' => 1,
                'created_by_type' => DashUser::class,

            ],
            [
                'first_name' => 'يوسف',
                'last_name' => 'خالد',
                'user_name' => 'youssef_k',
                'password' => Hash::make('password123'),
                'mobile' => '0593333333',
                'created_by_id' => 1,
                'created_by_type' => DashUser::class,
            ],
        ];

        foreach ($customers as $custData) {
            $customer = Customer::create($custData);

            // -----------------------
            // LINK ADDRESSES TO CUSTOMER
            // -----------------------
            // Assign first 1 or 2 addresses randomly
            $assignedAddresses = Address::inRandomOrder()->take(rand(1, 2))->get();

            foreach ($assignedAddresses as $address) {
                $customer->addresses()->attach($address->id, [
                    'extra_details' => 'شقة عشوائية',
                    'is_main' => rand(0, 1)
                ]);
            }
        }
    }
}
