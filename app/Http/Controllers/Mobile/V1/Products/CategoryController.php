<?php

namespace App\Http\Controllers\Mobile\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\CategoryResource;
use App\Models\MainCategory;
use App\Services\Mobile\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CategoryController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
        ];
    }

    public function __construct(private CategoryService $categoryService) {}

    public function mainCategoriesList(Request $request)
    {
        $mainCategories = $this->categoryService->mainCategoriesList($request);
        return response()->format(CategoryResource::collection($mainCategories), 'messages.success', 200);
    }

    public function subCategoriesList(Request $request, MainCategory $mainCategory)
    {
        $subCategories = $this->categoryService->subCategoriesList($mainCategory, $request);
        return response()->format(CategoryResource::collection($subCategories), 'messages.success', 200);
    }

    public function brandList(Request $request)
    {
        $brands = $this->categoryService->brandList($request);
        return response()->format(CategoryResource::collection($brands), 'messages.success', 200);
    }
}
