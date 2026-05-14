<?php

namespace App\Http\Controllers\Mobile\V1\AppUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\AppUser\WarehouseHandoverRequest;
use App\Http\Resources\Mobile\WarehouseHandoverResource;
use App\Models\WarehouseHandover;
use App\Services\Mobile\WarehouseHandoverService;
use Illuminate\Http\Request;

class WarehouseHandoverController extends Controller
{


    public function __construct(private WarehouseHandoverService $warehouseHandoverService) {}

    public function index(Request $request)
    {
        $warehouseHandovers = $this->warehouseHandoverService->list($request);
        return response()->format($this->returnPaginatedResponse($warehouseHandovers, WarehouseHandoverResource::collection($warehouseHandovers)), 'messages.success', 200);
    }

    public function store(WarehouseHandoverRequest $request)
    {
        $warehouseHandover = $this->warehouseHandoverService->create($request->validated());
        return response()->format(new WarehouseHandoverResource($warehouseHandover),  __('messages.created_successfully',  ['item' => __('constants.warehouse_handover')]), 201);
    }

    public function update(WarehouseHandoverRequest $request, WarehouseHandover $warehouseHandover)
    {
        $warehouseHandover = $this->warehouseHandoverService->update($warehouseHandover, $request->validated());
        return response()->format(new WarehouseHandoverResource($warehouseHandover),  __('messages.updated_successfully',  ['item' => __('constants.warehouse_handover')]), 200);
    }
    public function show(WarehouseHandover $warehouseHandover)
    {
        $warehouseHandover = $this->warehouseHandoverService->show($warehouseHandover);
        return response()->format(new WarehouseHandoverResource($warehouseHandover), 'messages.success', 200);
    }


    public function shipHandover(
        WarehouseHandover $warehouseHandover
    ) {
        $handover = $this->warehouseHandoverService->shipHandover(
            $warehouseHandover
        );

        return response()->format(
            new WarehouseHandoverResource($handover),
            __('messages.updated_successfully', [
                'item' => __('constants.warehouse_handover')
            ]),
            200
        );
    }
    public function completeHandover(
        WarehouseHandover $warehouseHandover
    ) {
        $handover = $this->warehouseHandoverService->completeHandover(
            $warehouseHandover
        );

        return response()->format(
            new WarehouseHandoverResource($handover),
            __('messages.updated_successfully', [
                'item' => __('constants.warehouse_handover')
            ]),
            200
        );
    }
}
