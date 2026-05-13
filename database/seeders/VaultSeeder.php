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
        PaymentMethod::updateOrCreate([
            'name_en' => 'Cash',
        ], [
            'name_ar' => 'كاش باليد',
            'required_fields' => []
        ]);

        PaymentMethod::updateOrCreate([
            'name_en' => 'Money Transfer',
        ], [
            'name_ar' => 'حوالة',
            'required_fields' => [
                [
                    'key' => 'receiver_name',
                    'label' => 'اسم المستقبل الثلاثي',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'key' => 'receiver_number',
                    'label' => 'رقم هاتف المستقبل',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'key' => 'destination',
                    'label' => 'الوجهة',
                    'type' => 'text',
                    'required' => true,
                ],
                // [
                //     'key' => 'receipt_image',
                //     'label' => 'صورة الإيصال',
                //     'type' => 'image',
                //     'required' => false,
                // ]
            ]
        ]);


        PaymentMethod::updateOrCreate([
            'name_en' => 'Units Transfer',
        ], [
            'name_ar' => 'رصيد وحدات',
            'required_fields' => [
                [
                    'key' => 'receiver_number',
                    'label' => 'رقم هاتف المستقبل',
                    'type' => 'text',
                    'required' => true,
                ],
                // [
                //     'key' => 'transfer_code',
                //     'label' => 'رمز التحويل',
                //     'type' => 'text',
                //     'required' => true,
                // ],
            ]
        ]);

        PaymentMethod::updateOrCreate([
            'name_en' => 'Sham Cash',
        ], [
            'name_ar' => 'شام كاش',
            'required_fields' => [
                // [
                //     'key' => 'receiver_number',
                //     'label' => 'رقم هاتف المستقبل',
                //     'type' => 'text',
                //     'required' => true,
                // ],
                [
                    'key' => 'qr_image',
                    'label' => 'QR صورة',
                    'type' => 'image',
                    'required' => false,
                ],
                [
                    'key' => 'link_or_qr_code',
                    'label' => 'رابط او QR كود',
                    'type' => 'text',
                    'required' => false,
                ],
                // [
                //     'key' => 'receipt_image',
                //     'label' => 'صورة الإشعار',
                //     'type' => 'image',
                //     'required' => false,
                // ],
            ]
        ]);

        $delivery = PaymentMethod::where('name_en', 'Delivery')->first();

        if ($delivery) {
            $delivery->delete();
        }
        UserRequestType::updateOrCreate([
            'name' => 'سلفة'
        ], []);

        UserRequestType::updateOrCreate([
            'name' => 'شكوى'
        ], []);
        UserRequestType::updateOrCreate([
            'name' => 'ترقية'
        ], []);
    }
}
