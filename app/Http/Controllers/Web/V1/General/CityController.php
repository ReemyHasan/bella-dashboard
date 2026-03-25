<?php

namespace App\Http\Controllers\Web\V1\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\General\CityRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\DashUser\CityResource;
use App\Models\City;
use App\Services\DashUser\CityService;

class CityController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_cities', only: ['index']),
            new Middleware('permission:view_city_by_id', only: ['show']),
            new Middleware('permission:create_city', only: ['store']),
            new Middleware('permission:update_city', only: ['update']),
            new Middleware('permission:delete_city', only: ['destroy']),

        ];
    }

    public function __construct(private CityService $cityService) {}

    public function index(Request $request)
    {
        $cities = $this->cityService->list($request);
        return response()->format($this->returnPaginatedResponse($cities, CityResource::collection($cities)), 'messages.success', 200);
    }

    public function store(CityRequest $request)
    {
        $city = $this->cityService->create($request->validated());
        return response()->format(new CityResource($city),  __('messages.created_successfully',  ['item' => __('constants.city')]), 201);
    }

    public function update(CityRequest $request, City $city)
    {
        $city = $this->cityService->update($city, $request->validated());
        return response()->format(new CityResource($city),  __('messages.updated_successfully',  ['item' => __('constants.city')]), 200);
    }
    public function show(City $city)
    {
        $city = $this->cityService->show($city);
        return response()->format(new CityResource($city), 'messages.success', 200);
    }
    public function destroy(City $city)
    {
        $returned = $this->cityService->delete($city);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.city')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.city')]), 200);
    }

    public function selectAvailable(Request $request)
    {
        $zone= $request->input('zone');
        $cities = $this->cityService->selectAvailable($zone);

        $returnedData = $cities->map(fn($city) => [
            'key' => $city?->id,
            'value' => $city?->name . '(' . $city?->symbol . ')'
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
