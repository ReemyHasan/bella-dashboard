<?php

namespace App\Http\Controllers\Web\V1\Warehouses;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Warehouses\ApproveWarehouseHandoverRequest;
use App\Http\Requests\DashUser\Warehouses\RejectWarehouseHandoverRequest;
use App\Http\Requests\DashUser\Warehouses\WarehouseHandoverRequest;
use App\Http\Resources\DashUser\WarehouseHandoverResource;
use App\Models\WarehouseHandover;
use App\Services\DashUser\WarehouseHandoverService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WarehouseHandoverController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_handover_requests', only: ['index']),
            new Middleware('permission:view_handover_request_by_id', only: ['show']),
            new Middleware('permission:create_handover_request', only: ['store']),
            new Middleware('permission:update_handover_request', only: ['update']),
            new Middleware('permission:delete_handover_request', only: ['destroy']),
        ];
    }

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
    public function destroy(WarehouseHandover $warehouseHandover)
    {
        $returned = $this->warehouseHandoverService->delete($warehouseHandover);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.warehouse_handover')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.warehouse_handover')]), 200);
    }

    public function approve(
        ApproveWarehouseHandoverRequest $request,
        WarehouseHandover $warehouseHandover
    ) {
        $handover = $this->warehouseHandoverService->approveRequest(
            $warehouseHandover,
            $request->validated()['items']
        );

        return response()->format(
            new WarehouseHandoverResource($handover),
            __('messages.updated_successfully', [
                'item' => __('constants.warehouse_handover')
            ]),
            200
        );
    }
    public function reject(
        RejectWarehouseHandoverRequest $request,
        WarehouseHandover $warehouseHandover
    ) {
        $handover = $this->warehouseHandoverService->rejectRequest(
            $warehouseHandover,
            $request->input('reason')
        );

        return response()->format(
            new WarehouseHandoverResource($handover),
            __('messages.updated_successfully', [
                'item' => __('constants.warehouse_handover')
            ]),
            200
        );
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
