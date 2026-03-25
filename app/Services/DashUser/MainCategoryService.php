<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\MainCategory;
use App\Traits\HandlesImageUpload;
use Illuminate\Support\Facades\DB;

class MainCategoryService
{
    use HandlesImageUpload;

    public function list($request)
    {
        return MainCategory::filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {

        if (isset($data['image_path']) && $data['image_path'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image_path'] = $this->uploadImage($data['image_path'], 'main_categories');
        } else {
            $data['image_path'] = null;
        }

        return DB::transaction(function () use ($data) {
            $mainCategory = MainCategory::create([
                'name' => $data['name'],
                'image_path' => $data['image_path'],
                'active' => $data['active']
            ]);
            return $mainCategory;
        });
    }

    public function update(MainCategory $mainCategory, array $data)
    {

    $removeFlag = "image_path_remove";

            if (!empty($data[$removeFlag])) {
                $this->deleteImage($mainCategory->image_path);
                $data['image_path'] = null;
            } else if (isset($data['image_path']) && $data['image_path'] instanceof \Illuminate\Http\UploadedFile) {
                $data['image_path'] = $this->updateImage($data['image_path'], $mainCategory->image_path, 'main_categories');
            } else {
                $data['image_path'] = $mainCategory->image_path;
            }

        return DB::transaction(function () use ($mainCategory, $data) {
            $mainCategory->update([
                'name' => $data['name'],
                'image_path' => $data['image_path'],
                'active' => $data['active']
            ]);

            return $mainCategory;
        });
    }
    public function show(MainCategory $mainCategory)
    {
        return $mainCategory;
    }

    public function delete(MainCategory $mainCategory)
    {
        return $mainCategory->delete();
    }


    public function selectAvailable()
    {

        $mainCategories = MainCategory::orderBy('id')->where('active', true)->get([
            'id',
            'name'
        ]);

        return $mainCategories;
    }
}
