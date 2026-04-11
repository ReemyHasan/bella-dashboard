<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\Brand;
use App\Traits\HandlesImageUpload;
use Illuminate\Support\Facades\DB;

class BrandService
{
    use HandlesImageUpload;

    public function list($request)
    {
        return Brand::filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {

        if (isset($data['image_path']) && $data['image_path'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image_path'] = $this->uploadImage($data['image_path'], 'brands');
        } else {
            $data['image_path'] = null;
        }

        return DB::transaction(function () use ($data) {
            $brand = Brand::create([
                'name' => $data['name'],
                'image_path' => $data['image_path'],
                'active' => $data['active']
            ]);
            return $brand;
        });
    }

    public function update(Brand $brand, array $data)
    {

        $removeFlag = "image_path_remove";

        if (!empty($data[$removeFlag])) {
            $this->deleteImage($brand->image_path);
            $data['image_path'] = null;
        } else if (isset($data['image_path']) && $data['image_path'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image_path'] = $this->updateImage($data['image_path'], $brand->image_path, 'brands');
        } else {
            $data['image_path'] = $brand->image_path;
        }

        return DB::transaction(function () use ($brand, $data) {
            $brand->update([
                'name' => $data['name'],
                'image_path' => $data['image_path'],
                'active' => $data['active']
            ]);

            return $brand;
        });
    }
    public function show(Brand $brand)
    {
        return $brand;
    }

    public function delete(Brand $brand)
    {
        if ($brand->products()->exists()) {
            throw new CustomException('لا يمكن حذف الماركة, يوجد منتجات مرتبطة بها.');
        }
        $this->deleteImage($brand->image_path);

        return $brand->delete();
    }


    public function selectAvailable()
    {

        $brands = Brand::orderBy('id')->where('active', true)->get([
            'id',
            'name'
        ]);

        return $brands;
    }
}
