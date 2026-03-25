<?php

namespace App\Http\Controllers\Web\V1\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\General\RegionRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\DashUser\RegionResource;
use App\Models\Region;
use App\Services\DashUser\RegionService;

class RegionController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_regions', only: ['index']),
            new Middleware('permission:view_region_by_id', only: ['show']),
            new Middleware('permission:create_region', only: ['store']),
            new Middleware('permission:update_region', only: ['update']),
            new Middleware('permission:delete_region', only: ['destroy']),

        ];
    }

    public function __construct(private RegionService $regionService) {}

    public function index(Request $request)
    {
        $regions = $this->regionService->list($request);
        return response()->format($this->returnPaginatedResponse($regions, RegionResource::collection($regions)), 'messages.success', 200);
    }

    public function store(RegionRequest $request)
    {
        $region = $this->regionService->create($request->validated());
        return response()->format(new RegionResource($region),  __('messages.created_successfully',  ['item' => __('constants.region')]), 201);
    }

    public function update(RegionRequest $request, Region $region)
    {
        $region = $this->regionService->update($region, $request->validated());
        return response()->format(new RegionResource($region),  __('messages.updated_successfully',  ['item' => __('constants.region')]), 200);
    }
    public function show(Region $region)
    {
        $region = $this->regionService->show($region);
        return response()->format(new RegionResource($region), 'messages.success', 200);
    }
    public function destroy(Region $region)
    {
        $returned = $this->regionService->delete($region);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.region')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.region')]), 200);
    }

    public function selectAvailable(Request $request)
    {
        $city= $request->input('city');
        $warehouse= $request->input('warehouse');
        $regions = $this->regionService->selectAvailable($city, $warehouse);

        $returnedData = $regions->map(fn($region) => [
            'key' => $region?->id,
            'value' => $region?->name . '(' . $region?->symbol . ')'
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
