<?php

namespace App\Http\Controllers\Web\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Products\BrandRequest;
use App\Http\Resources\DashUser\BrandResource;
use App\Models\Brand;
use App\Services\DashUser\BrandService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BrandController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_brands', only: ['index']),
            new Middleware('permission:view_brand_by_id', only: ['show']),
            new Middleware('permission:create_brand', only: ['store']),
            new Middleware('permission:update_brand', only: ['update']),
            new Middleware('permission:delete_brand', only: ['destroy']),

        ];
    }

    public function __construct(private BrandService $brandService) {}

    public function index(Request $request)
    {
        $brands = $this->brandService->list($request);
        return response()->format($this->returnPaginatedResponse($brands, BrandResource::collection($brands)), 'messages.success', 200);
    }

    public function store(BrandRequest $request)
    {
        $brand = $this->brandService->create($request->validated());
        return response()->format(new BrandResource($brand),  __('messages.created_successfully',  ['item' => __('constants.brand')]), 201);
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $brand = $this->brandService->update($brand, $request->validated());
        return response()->format(new BrandResource($brand),  __('messages.updated_successfully',  ['item' => __('constants.brand')]), 200);
    }
    public function show(Brand $brand)
    {
        $brand = $this->brandService->show($brand);
        return response()->format(new BrandResource($brand), 'messages.success', 200);
    }
    public function destroy(Brand $brand)
    {
        $returned = $this->brandService->delete($brand);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.brand')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.brand')]), 200);
    }

    public function selectAvailable()
    {
        $brands = $this->brandService->selectAvailable();

        $returnedData = $brands->map(fn($brand) => [
            'key' => $brand?->id,
            'value' => $brand?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
