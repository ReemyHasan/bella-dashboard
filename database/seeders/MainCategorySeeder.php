<?php

namespace Database\Seeders;

use App\Models\MainCategory;
use App\Traits\HandlesImageUpload;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class MainCategorySeeder extends Seeder
{
    use HandlesImageUpload;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $mainCategory1ImgPath = public_path('serv_images/main_categories/hair_products.jpg');
        $mainCategory1Uploaded = new UploadedFile(
            $mainCategory1ImgPath,
            'hair_products.jpg',
            mime_content_type($mainCategory1ImgPath),
            null,
            true
        );
        $mainCategory1Stored = $this->uploadImage($mainCategory1Uploaded, 'main_categories');


        MainCategory::create([
            'name' => 'منتجات الشعر',
            'image_path' => $mainCategory1Stored,
            'active' => true
        ]);

        //////////////

        $mainCategory2ImgPath = public_path('serv_images/main_categories/diet_products.jpg');
        $mainCategory2Uploaded = new UploadedFile(
            $mainCategory2ImgPath,
            'diet_products.jpg',
            mime_content_type($mainCategory2ImgPath),
            null,
            true
        );
        $mainCategory2Stored = $this->uploadImage($mainCategory2Uploaded, 'main_categories');


        MainCategory::create([
            'name' => 'منتجات التنحيف',
            'image_path' => $mainCategory2Stored,
            'active' => true
        ]);

        //////////////

        $mainCategory3ImgPath = public_path('serv_images/main_categories/regim_products.jpg');
        $mainCategory3Uploaded = new UploadedFile(
            $mainCategory3ImgPath,
            'regim_products.jpg',
            mime_content_type($mainCategory3ImgPath),
            null,
            true
        );
        $mainCategory3Stored = $this->uploadImage($mainCategory3Uploaded, 'main_categories');


        MainCategory::create([
            'name' => 'منتجات التسمين',
            'image_path' => $mainCategory3Stored,
            'active' => true
        ]);

        //////////////

        $mainCategory4ImgPath = public_path('serv_images/main_categories/marital_products.jpg');
        $mainCategory4Uploaded = new UploadedFile(
            $mainCategory4ImgPath,
            'marital_products.jpg',
            mime_content_type($mainCategory4ImgPath),
            null,
            true
        );
        $mainCategory4Stored = $this->uploadImage($mainCategory4Uploaded, 'main_categories');


        MainCategory::create([
            'name' => 'منتجات زوجية',
            'image_path' => $mainCategory4Stored,
            'active' => true
        ]);


        //////////////

        $mainCategory5ImgPath = public_path('serv_images/main_categories/healing_products.jpg');
        $mainCategory5Uploaded = new UploadedFile(
            $mainCategory5ImgPath,
            'healing_products.jpg',
            mime_content_type($mainCategory5ImgPath),
            null,
            true
        );
        $mainCategory5Stored = $this->uploadImage($mainCategory5Uploaded, 'main_categories');


        MainCategory::create([
            'name' => 'منتجات علاجية',
            'image_path' => $mainCategory5Stored,
            'active' => true
        ]);

        //////////////

        $mainCategory6ImgPath = public_path('serv_images/main_categories/skin_products.jpg');
        $mainCategory6Uploaded = new UploadedFile(
            $mainCategory6ImgPath,
            'skin_products.jpg',
            mime_content_type($mainCategory6ImgPath),
            null,
            true
        );
        $mainCategory6Stored = $this->uploadImage($mainCategory6Uploaded, 'main_categories');


        MainCategory::create([
            'name' => 'منتجات البشرة',
            'image_path' => $mainCategory6Stored,
            'active' => true
        ]);

        //////////////
        $mainCategory7ImgPath = public_path('serv_images/main_categories/diverse.jpg');
        $mainCategory7Uploaded = new UploadedFile(
            $mainCategory7ImgPath,
            'diverse.jpg',
            mime_content_type($mainCategory7ImgPath),
            null,
            true
        );
        $mainCategory7Stored = $this->uploadImage($mainCategory7Uploaded, 'main_categories');


        MainCategory::create([
            'name' => 'منوعة',
            'image_path' => $mainCategory7Stored,
            'active' => false
        ]);


        //////////////
        $mainCategory8ImgPath = public_path('serv_images/main_categories/clean.jpg');
        $mainCategory8Uploaded = new UploadedFile(
            $mainCategory8ImgPath,
            'clean.jpg',
            mime_content_type($mainCategory8ImgPath),
            null,
            true
        );
        $mainCategory8Stored = $this->uploadImage($mainCategory8Uploaded, 'main_categories');


        MainCategory::create([
            'name' => 'التصافي',
            'image_path' => $mainCategory8Stored,
            'active' => true
        ]);
    }
}
