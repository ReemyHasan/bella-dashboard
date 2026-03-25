<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\SubCategory;
use App\Traits\HandlesImageUpload;
use Illuminate\Support\Facades\DB;

class SubCategoryService
{
    use HandlesImageUpload;

    public function list($request)
    {
        return SubCategory::with('mainCategory')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {

        if (isset($data['image_path']) && $data['image_path'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image_path'] = $this->uploadImage($data['image_path'], 'sub_categories');
        } else {
            $data['image_path'] = null;
        }

        return DB::transaction(function () use ($data) {
            $subCategory = SubCategory::create([
                'name' => $data['name'],
                'image_path' => $data['image_path'],
                'active' => $data['active'],
                'main_category_id' => $data['main_category_id']

            ]);
            $subCategory->load('mainCategory');

            return $subCategory;
        });
    }

    public function update(SubCategory $subCategory, array $data)
    {

        $removeFlag = "image_path_remove";

        if (!empty($data[$removeFlag])) {
            $this->deleteImage($subCategory->image_path);
            $data['image_path'] = null;
        } else if (isset($data['image_path']) && $data['image_path'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image_path'] = $this->updateImage($data['image_path'], $subCategory->image_path, 'sub_categories');
        } else {
            $data['image_path'] = $subCategory->image_path;
        }

        return DB::transaction(function () use ($subCategory, $data) {
            $subCategory->update([
                'name' => $data['name'],
                'image_path' => $data['image_path'],
                'active' => $data['active'],
                'main_category_id' => $data['main_category_id']

            ]);

            $subCategory->load('mainCategory');

            return $subCategory;
        });
    }
    public function show(SubCategory $subCategory)
    {
        $subCategory->load('mainCategory');
        return $subCategory;
    }

    public function delete(SubCategory $subCategory)
    {
        $this->deleteImage($subCategory->image_path);
        return $subCategory->delete();
    }


    public function selectAvailable($main = null)
    {

        $subCategories = SubCategory::when(!is_null($main), function ($query) use ($main) {
            $query->where('main_category_id', $main);
        })->where('active', true)->orderBy('id')->get([
            'id',
            'name',
            'main_category_id',
        ]);

        return $subCategories;
    }
}
