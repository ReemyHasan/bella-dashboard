<?php

namespace App\Http\Controllers\Web\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Products\MainCategoryRequest;
use App\Http\Resources\DashUser\MainCategoryResource;
use App\Models\MainCategory;
use App\Services\DashUser\MainCategoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MainCategoryController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_main_categories', only: ['index']),
            new Middleware('permission:view_main_category_by_id', only: ['show']),
            new Middleware('permission:create_main_category', only: ['store']),
            new Middleware('permission:update_main_category', only: ['update']),
            new Middleware('permission:delete_main_category', only: ['destroy']),

        ];
    }

    public function __construct(private MainCategoryService $mainCategoryService) {}

    public function index(Request $request)
    {
        $mainCategories = $this->mainCategoryService->list($request);
        return response()->format($this->returnPaginatedResponse($mainCategories, MainCategoryResource::collection($mainCategories)), 'messages.success', 200);
    }

    public function store(MainCategoryRequest $request)
    {
        $mainCategory = $this->mainCategoryService->create($request->validated());
        return response()->format(new MainCategoryResource($mainCategory),  __('messages.created_successfully',  ['item' => __('constants.main_category')]), 201);
    }

    public function update(MainCategoryRequest $request, MainCategory $mainCategory)
    {
        $mainCategory = $this->mainCategoryService->update($mainCategory, $request->validated());
        return response()->format(new MainCategoryResource($mainCategory),  __('messages.updated_successfully',  ['item' => __('constants.main_category')]), 200);
    }
    public function show(MainCategory $mainCategory)
    {
        $mainCategory = $this->mainCategoryService->show($mainCategory);
        return response()->format(new MainCategoryResource($mainCategory), 'messages.success', 200);
    }
    public function destroy(MainCategory $mainCategory)
    {
        $returned = $this->mainCategoryService->delete($mainCategory);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.main_category')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.main_category')]), 200);
    }

    public function selectAvailable()
    {
        $mainCategories = $this->mainCategoryService->selectAvailable();

        $returnedData = $mainCategories->map(fn($mainCategory) => [
            'key' => $mainCategory?->id,
            'value' => $mainCategory?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
