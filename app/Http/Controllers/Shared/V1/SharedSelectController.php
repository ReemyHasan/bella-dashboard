<?php

namespace App\Http\Controllers\Shared\V1;

use App\Http\Controllers\Controller;
use App\Services\Shared\SharedInfoService;
use Illuminate\Http\Request;

class SharedSelectController extends Controller
{
    public function __construct(private SharedInfoService $sharedInfoService) {}



    public function selectCustomers(Request $request)
    {
        $search = $request->input('search');

        $returned = $this->sharedInfoService->selectCustomers($search);
        return response()->format($returned, 'messages.success', 200);
    }
    public function selectMarketerInfo($marketeId = null)
    {
        $returned = $this->sharedInfoService->selectMarketerInfo($marketeId);
        return response()->format($returned, 'messages.success', 200);
    }
    public function selectAddressInfo($addressId)
    {

        $returned = $this->sharedInfoService->selectAddressInfo($addressId);
        return response()->format($returned, 'messages.success', 200);
    }

    public function selectZoneProducts($zoneId)
    {

        $returned = $this->sharedInfoService->selectZoneProducts($zoneId);
        return response()->format($returned, 'messages.success', 200);
    }


    public function selectZoneOffers($zoneId)
    {

        $returned = $this->sharedInfoService->selectZoneOffers($zoneId);
        return response()->format($returned, 'messages.success', 200);
    }

    public function warehouseProducts($warehouseId)
    {

        $returned = $this->sharedInfoService->warehouseProducts($warehouseId);
        return response()->format($returned, 'messages.success', 200);
    }

    public function warehouseOffers($warehouseId)
    {

        $returned = $this->sharedInfoService->warehouseOffers($warehouseId);
        return response()->format($returned, 'messages.success', 200);
    }

    public function customerAddresses($customerId)
    {

        $returned = $this->sharedInfoService->customerAddresses($customerId);
        return response()->format($returned, 'messages.success', 200);
    }

    public function selectAvailableAppUserRequestTypes()
    {

        $returned = $this->sharedInfoService->selectAvailableAppUserRequestTypes();
        return response()->format($returned, 'messages.success', 200);
    }

    public function selectAvailableWarehouseMen()
    {

        $returned = $this->sharedInfoService->selectAvailableWarehouseMen();
        return response()->format($returned, 'messages.success', 200);
    }
    public function selectAvailableSubteams($teamId)
    {
        $subteams = $this->sharedInfoService->selectAvailableSubteams($teamId);

        $returnedData = $subteams->map(fn($subteam) => [
            'key' => $subteam?->id,
            'value' => $subteam?->name . ($subteam->is_direct == 1 ? '(Direct)' : '(SubTeam)')
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
    public function selectAvailableCompetitions(Request $request)
    {
        $marketer_id = $request->input('marketer_id');

        $status = $request->input('status');


        $competitions = $this->sharedInfoService->selectAvailableCompetitions(
            $marketer_id,
            $status,
        );

        $returnedData = $competitions->map(fn($competition) => [
            'key' => $competition?->id,
            'value' => $competition?->name

        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }


    public function selectAvailableWarehouses(Request $request)
    {
        $zone = $request->input('zone');
        $is_main = $request->input('is_main');

        $warehouses = $this->sharedInfoService->selectAvailableWarehouses($zone, $is_main);

        $returnedData = $warehouses->map(fn($warehouse) => [
            'key' => $warehouse?->id,
            'value' => $warehouse?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
