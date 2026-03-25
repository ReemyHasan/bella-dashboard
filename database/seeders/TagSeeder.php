<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'أورديناري', 'created_at' => now()],
            ['name' => 'كارسيل', 'created_at' => now()],
            ['name' => 'لابيلا', 'created_at' => now()],
            ['name' => 'لاروش', 'created_at' => now()],
            ['name' => 'جوسون', 'created_at' => now()],
            ['name' => 'منتج اورجينال', 'created_at' => now()],
            ['name' => 'ختم ليزري', 'created_at' => now()],

        ];


        Tag::insert($tags);
    }
}
