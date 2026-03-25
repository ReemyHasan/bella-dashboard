<?php

namespace App\Http\Controllers\Web\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Products\SubCategoryRequest;
use App\Http\Resources\DashUser\SubCategoryResource;
use App\Models\SubCategory;
use App\Services\DashUser\SubCategoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SubCategoryController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_sub_categories', only: ['index']),
            new Middleware('permission:view_sub_category_by_id', only: ['show']),
            new Middleware('permission:create_sub_category', only: ['store']),
            new Middleware('permission:update_sub_category', only: ['update']),
            new Middleware('permission:delete_sub_category', only: ['destroy']),

        ];
    }

    public function __construct(private SubCategoryService $subCategoryService) {}

    public function index(Request $request)
    {
        $subCategories = $this->subCategoryService->list($request);
        return response()->format($this->returnPaginatedResponse($subCategories, SubCategoryResource::collection($subCategories)), 'messages.success', 200);
    }

    public function store(SubCategoryRequest $request)
    {
        $subCategory = $this->subCategoryService->create($request->validated());
        return response()->format(new SubCategoryResource($subCategory),  __('messages.created_successfully',  ['item' => __('constants.sub_category')]), 201);
    }

    public function update(SubCategoryRequest $request, SubCategory $subCategory)
    {
        $subCategory = $this->subCategoryService->update($subCategory, $request->validated());
        return response()->format(new SubCategoryResource($subCategory),  __('messages.updated_successfully',  ['item' => __('constants.sub_category')]), 200);
    }
    public function show(SubCategory $subCategory)
    {
        $subCategory = $this->subCategoryService->show($subCategory);
        return response()->format(new SubCategoryResource($subCategory), 'messages.success', 200);
    }
    public function destroy(SubCategory $subCategory)
    {
        $returned = $this->subCategoryService->delete($subCategory);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.sub_category')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.sub_category')]), 200);
    }

    public function selectAvailable(Request $request)
    {
        $main= $request->input('main_category');
        $subCategories = $this->subCategoryService->selectAvailable($main);

        $returnedData = $subCategories->map(fn($subCategory) => [
            'key' => $subCategory?->id,
            'value' => $subCategory?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
