<?php

namespace App\Http\Controllers\Mobile\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\ProductDetailsResources;
use App\Http\Resources\Mobile\ProductListResource;
use App\Models\Product;
use App\Services\Mobile\ProductService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            // new Middleware('permission:view_all_products', only: ['index']),

        ];
    }

    public function __construct(private ProductService $productService) {}

    public function index(Request $request)
    {
        $products = $this->productService->list($request);
        return response()->format($this->returnPaginatedResponse($products, ProductListResource::collection($products)), 'messages.success', 200);
    }

   
    public function show(Product $product)
    {
        $product = $this->productService->show($product);
        return response()->format(new ProductDetailsResources($product), 'messages.success', 200);
    }

    
    public function markAsImportant(Product $product)
    {
        $this->productService->markAsImportant($product);
        return response()->format(null, 'messages.success', 200);
    }

    public function selectAvailable(Request $request)
    {
        $search = $request->input('search');

        $products = $this->productService->selectAvailable($search);

        $returnedData = $products->map(fn($product) => [
            'key' => $product?->id,
            'value' => $product?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }


}
