<?php

namespace App\Http\Controllers\Web\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Products\AdjustmentRequest;
use App\Http\Requests\DashUser\Products\ProductImageRequest;
use App\Http\Requests\DashUser\Products\ProductRequest;
use App\Http\Requests\DashUser\Products\ProductZonePriceSyncRequest;
use App\Http\Resources\DashUser\ProductResource;
use App\Http\Resources\DashUser\ProductWarehouseResource;
use App\Models\Product;
use App\Services\DashUser\ProductService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_products', only: ['index']),
            new Middleware('permission:view_product_by_id', only: ['show']),
            new Middleware('permission:create_product', only: ['store']),
            new Middleware('permission:update_product', only: ['update', 'syncImages', 'syncZonePrices']),
            new Middleware('permission:delete_product', only: ['destroy']),

        ];
    }

    public function __construct(private ProductService $productService) {}

    public function index(Request $request)
    {
        $products = $this->productService->list($request);
        return response()->format($this->returnPaginatedResponse($products, ProductResource::collection($products)), 'messages.success', 200);
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->create($request->validated());
        return response()->format(new ProductResource($product),  __('messages.created_successfully',  ['item' => __('constants.product')]), 201);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product = $this->productService->update($product, $request->validated());
        return response()->format(new ProductResource($product),  __('messages.updated_successfully',  ['item' => __('constants.product')]), 200);
    }

    public function syncImages(ProductImageRequest $request, Product $product)
    {
        $product = $this->productService->syncImages($request->validated(), $product);
        return response()->format(new ProductResource($product),  __('messages.updated_successfully',  ['item' => __('constants.product')]), 200);
    }

    public function syncZonePrices(ProductZonePriceSyncRequest $request, Product $product)
    {
        $product = $this->productService->syncZonePrices($request->validated(), $product);
        return response()->format(new ProductResource($product),  __('messages.updated_successfully',  ['item' => __('constants.product')]), 200);
    }
    public function show(Product $product)
    {
        $product = $this->productService->show($product);
        return response()->format(new ProductResource($product), 'messages.success', 200);
    }
    public function destroy(Product $product)
    {
        $returned = $this->productService->delete($product);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.product')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.product')]), 200);
    }

    public function selectAvailable(Request $request)
    {
        $mainCategory = $request->input('mainCategory');
        $subCategory = $request->input('subCategory');

        $products = $this->productService->selectAvailable($mainCategory, $subCategory);

        $returnedData = $products->map(fn($product) => [
            'key' => $product?->id,
            'value' => $product?->name . "-" . $product?->mainCategory?->name . "-" .
                $product?->subCategory?->name . "-" . $product?->size . "-" . $product?->country_of_origin
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }


    public function productWarehouses(Request $request, Product $product)
    {
        $productWarehouses = $this->productService->productWarehouses($request, $product);
        return response()->format($this->returnPaginatedResponse($productWarehouses, ProductWarehouseResource::collection($productWarehouses)), 'messages.success', 200);
    }


    public function applyAdjustment(AdjustmentRequest $request)
    {
        $this->productService->applyAdjustment($request->validated());
        return response()->format(null,  __('messages.all_updated_successfully',  ['item' => __('constants.products')]), 200);
    }

    public function removeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['exists:products,id']
        ]);
        $this->productService->removeAdjustment($validated);
        return response()->format(null,  __('messages.all_updated_successfully',  ['item' => __('constants.products')]), 200);
    }
}
