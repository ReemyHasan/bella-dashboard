<?php

namespace App\Services\Mobile;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\Brand;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Traits\HandlesImageUpload;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    use HandlesImageUpload;

    public function mainCategoriesList($request)
    {
        return MainCategory::filterBy($request->all())->where('active', true)
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->get();
    }

    
    public function subCategoriesList($mainCategory, $request)
    {
        return SubCategory::filterBy($request->all())->where('main_category_id', $mainCategory->id)->where('active', true)
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->get();
    }

     public function brandList($request)
    {
        return Brand::filterBy($request->all())->where('active', true)
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->get();
    }
}
