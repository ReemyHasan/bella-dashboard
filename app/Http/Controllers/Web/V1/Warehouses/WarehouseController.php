<?php

namespace App\Http\Controllers\Web\V1\Warehouses;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Warehouses\WarehouseRequest;
use App\Http\Resources\DashUser\ProductResource;
use App\Http\Resources\DashUser\WarehouseProductResource;
use App\Http\Resources\DashUser\WarehouseResource;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use App\Services\DashUser\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WarehouseController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_warehouses', only: ['index', 'warehouseProducts']),
            new Middleware('permission:view_warehouse_by_id', only: ['show']),
            new Middleware('permission:create_warehouse', only: ['store']),
            new Middleware('permission:update_warehouse', only: ['update']),
            new Middleware('permission:delete_warehouse', only: ['destroy']),

        ];
    }

    public function __construct(private WarehouseService $warehouseService) {}

    public function index(Request $request)
    {
        $warehouses = $this->warehouseService->list($request);
        return response()->format($this->returnPaginatedResponse($warehouses, WarehouseResource::collection($warehouses)), 'messages.success', 200);
    }

    public function store(WarehouseRequest $request)
    {
        $warehouse = $this->warehouseService->create($request->validated());
        return response()->format(new WarehouseResource($warehouse),  __('messages.created_successfully',  ['item' => __('constants.warehouse')]), 201);
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse = $this->warehouseService->update($warehouse, $request->validated());
        return response()->format(new WarehouseResource($warehouse),  __('messages.updated_successfully',  ['item' => __('constants.warehouse')]), 200);
    }
    public function show(Warehouse $warehouse)
    {
        $warehouse = $this->warehouseService->show($warehouse);
        return response()->format(new WarehouseResource($warehouse), 'messages.success', 200);
    }
    public function destroy(Warehouse $warehouse)
    {
        $returned = $this->warehouseService->delete($warehouse);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.warehouse')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.warehouse')]), 200);
    }

    public function warehouseProducts(Request $request, Warehouse $warehouse)
    {
        $warehouseProducts = $this->warehouseService->warehouseProducts($request, $warehouse);
        // dd($warehouseProducts);
        return response()->format($this->returnPaginatedResponse($warehouseProducts, WarehouseProductResource::collection($warehouseProducts)), 'messages.success', 200);
    }

    public function selectAvailable(Request $request)
    {
        $zone = $request->input('zone');
        $is_main = $request->input('is_main');

        $warehouses = $this->warehouseService->selectAvailable($zone, $is_main);

        $returnedData = $warehouses->map(fn($warehouse) => [
            'key' => $warehouse?->id,
            'value' => $warehouse?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
