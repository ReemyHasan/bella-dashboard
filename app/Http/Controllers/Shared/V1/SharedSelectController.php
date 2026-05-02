<?php

namespace App\Http\Controllers\Shared\V1;

use App\Http\Controllers\Controller;
use App\Services\Shared\SharedInfoService;

class SharedSelectController extends Controller
{
    public function __construct(private SharedInfoService $sharedInfoService) {}



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
}
