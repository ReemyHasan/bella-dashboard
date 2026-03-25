<?php

namespace App\Http\Controllers\Web\V1\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\General\ZoneRequest;
use App\Http\Resources\DashUser\ZoneResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\Zone;
use App\Services\DashUser\ZoneService;

class ZoneController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_zones', only: ['index']),
            new Middleware('permission:view_zone_by_id', only: ['show']),
            new Middleware('permission:create_zone', only: ['store']),
            new Middleware('permission:update_zone', only: ['update']),
            new Middleware('permission:delete_zone', only: ['destroy']),

        ];
    }

    public function __construct(private ZoneService $zoneService) {}

    public function index(Request $request)
    {
        $zones = $this->zoneService->list($request);
        return response()->format($this->returnPaginatedResponse($zones, ZoneResource::collection($zones)), 'messages.success', 200);
    }

    public function store(ZoneRequest $request)
    {
        $zone = $this->zoneService->create($request->validated());
        return response()->format(new ZoneResource($zone),  __('messages.created_successfully',  ['item' => __('constants.zone')]), 201);
    }

    public function update(ZoneRequest $request, Zone $zone)
    {
        $zone = $this->zoneService->update($zone, $request->validated());
        return response()->format(new ZoneResource($zone),  __('messages.updated_successfully',  ['item' => __('constants.zone')]), 200);
    }
    public function show(Zone $zone)
    {
        $zone = $this->zoneService->show($zone);
        return response()->format(new ZoneResource($zone), 'messages.success', 200);
    }
    public function destroy(Zone $zone)
    {
        $returned = $this->zoneService->delete($zone);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.zone')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.zone')]), 200);
    }

    public function selectAvailable()
    {
        $currencies = $this->zoneService->selectAvailable();

        $returnedData = $currencies->map(fn($zone) => [
            'key' => $zone?->id,
            'value' => $zone?->name . '(' . $zone?->symbol . ')'
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }

    public function selectAvailableTips(Request $request)
    {
        $zone = $request->input('zone');

        $tips = $this->zoneService->selectAvailableTips($zone);

        $returnedData = $tips->map(fn($tip) => [
            'key' => $tip?->id,
            'value' => $tip?->amount
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
