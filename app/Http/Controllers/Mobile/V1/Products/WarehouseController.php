<?php

namespace App\Http\Controllers\Mobile\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\OfferResource;
use App\Http\Resources\Mobile\OfferWarehouseResource;
use App\Http\Resources\Mobile\WarehouseProductResource;
use App\Http\Resources\Mobile\WarehouseResource;
use App\Models\Offer;
use App\Models\Warehouse;
use App\Services\Mobile\WarehouseService;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{

    public function __construct(private WarehouseService $warehouseService) {}

    public function index(Request $request)
    {
        $warehouses = $this->warehouseService->list($request);
        return response()->format($this->returnPaginatedResponse($warehouses, WarehouseResource::collection($warehouses)), 'messages.success', 200);
    }
    public function warehouseProducts(Request $request, Warehouse $warehouse)
    {
        $warehouseProducts = $this->warehouseService->warehouseProducts($request, $warehouse);
        return response()->format($this->returnPaginatedResponse($warehouseProducts, WarehouseProductResource::collection($warehouseProducts)), 'messages.success', 200);
    }
    public function warehouseOffers(Request $request, Warehouse $warehouse)
    {
        $warehouseOffers = $this->warehouseService->warehouseOffers($request, $warehouse);
        return response()->format($this->returnPaginatedResponse($warehouseOffers, OfferWarehouseResource::collection($warehouseOffers)), 'messages.success', 200);
    }

     public function showOffer(Offer $offer)
    {
        $offer = $this->warehouseService->showOffer($offer);
        return response()->format(new OfferResource($offer), 'messages.success', 200);
    }
}
