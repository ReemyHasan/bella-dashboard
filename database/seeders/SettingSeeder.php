<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [

            [
                'field' => 'app_name',
                'type' => 'string',
                'value' => 'Loreen'
            ],

            [
                'field' => 'offer_general_image',
                'type' => 'image',
                'value' => ''
            ],

            [
                'field' => 'support_phone',
                'type' => 'string',
                'value' => '+000000000'
            ],

            [
                'field' => 'support_email',
                'type' => 'string',
                'value' => 'support@example.com'
            ],

        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['field' => $setting['field']],
                $setting
            );
        }
    }
}
