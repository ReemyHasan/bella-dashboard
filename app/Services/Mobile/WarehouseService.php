<?php

namespace App\Services\Mobile;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\Offer;
use App\Models\OfferWarehouse;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    private function allowUser()
    {
        $user = Auth::user();

        if (!$user->hasRole('Team Manager') && !$user->hasRole('Team Leader') && !$user->is_warehouse_man) {
            throw new CustomException('لا يمكن رؤية معلومات المستودعات إلا من قبل مدير أو أمين مستودع');
        }
    }
    public function list($request)
    {
        $this->allowUser();
        $query = Warehouse::with('zone', 'keeper')
            ->where('active', true)
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest();

        return $query->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function warehouseProducts($request, Warehouse $warehouse)
    {
        $this->allowUser();
        return ProductWarehouse::with(
            [
                'product.mainCategory',
                'product.subCategory',
                'product.mainImage'
            ]
        )->where('warehouse_id', $warehouse->id)->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->paginate($request->input('per_page') ?? PaginationEnum::GeneralPagination->value);
    }

    public function warehouseOffers($request, Warehouse $warehouse)
    {
        $this->allowUser();
        return OfferWarehouse::with(
            [
                'offer.mainImage'
            ]
        )->where('warehouse_id', $warehouse->id)->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->paginate($request->input('per_page') ?? PaginationEnum::GeneralPagination->value);
    }

    public function showOffer(Offer $offer)
    {
        $offer->load([
            'images',
            'tags',
            'zonePrices.zone.currency',
            'offerProducts.product.mainImage',
            'offerWarehouses.warehouse'
        ]);
        return $offer;
    }
}
