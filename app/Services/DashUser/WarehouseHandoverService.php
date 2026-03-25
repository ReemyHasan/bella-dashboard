<?php

namespace App\Services\DashUser;

use App\Enums\HandOverStatus;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Http\Resources\DashUser\WarehouseHandoverResource;
use App\Http\Resources\DashUser\WarehouseProductResource;
use App\Http\Resources\DashUser\WarehouseResource;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use App\Models\WarehouseHandover;
use App\Models\WarehouseHandoverItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseHandoverService
{
    private function canAccessHandover(WarehouseHandover $handover, $user): bool
    {
        if ($user->hasPermission('full_access_handover_requests')) {
            return true;
        }

        return $handover->providerWarehouse?->keeper_id === $user->id
            || $handover->requesterWarehouse?->keeper_id === $user->id;
    }
    public function list($request)
    {
        $user = Auth::user();

        $query = WarehouseHandover::with('requesterWarehouse', 'providerWarehouse')
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest();

        if (!$user->hasPermission('full_access_handover_requests')) {

            $query->where(function ($q) use ($user) {

                $q->whereHas('providerWarehouse', function ($w) use ($user) {
                    $w->where('keeper_id', $user->id);
                })
                    ->orWhereHas('requesterWarehouse', function ($w) use ($user) {
                        $w->where('keeper_id', $user->id);
                    });
            });
        }
        return $query->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = Auth::user();

        $warehouse = Warehouse::findOrFail($data['requester_warehouse_id']);

        if (
            !$user->hasPermission('full_access_handover_requests') &&
            $warehouse->keeper_id !== $user->id
        ) {
            abort(403, __('messages.unauthorized_action'));
        }
        return DB::transaction(function () use ($data) {

            $handover = WarehouseHandover::create([
                'requester_warehouse_id' => $data['requester_warehouse_id'],
                'provider_warehouse_id'  => $data['provider_warehouse_id'],
                'status' => HandOverStatus::pending->value,
                'requested_by' => auth()->id(),
                'notes' => $data['notes'] ?? null
            ]);

            foreach ($data['products'] as $product) {

                $handover->items()->create([
                    'product_id' => $product['product_id'],
                    'requested_quantity' => $product['quantity'],
                ]);
            }

            return $handover->load('requesterWarehouse', 'providerWarehouse', 'items.product.mainCategory', 'items.product.subCategory', 'requester', 'responder');
        });
    }

    public function update(WarehouseHandover $handover, array $data)
    {
        $user = Auth::user();

        $warehouse = $handover->requesterWarehouse;
        if (
            !$user->hasPermission('full_access_handover_requests') &&
            $warehouse->keeper_id !== $user->id
        ) {
            abort(403, __('messages.unauthorized_action'));
        }
        if ($handover->status !== HandOverStatus::pending->value) {
            throw new CustomException('الطلب لا يمكن تعديله');
        }

        return DB::transaction(function () use ($handover, $data) {

            $handover->update([
                'notes' => $data['notes'] ?? $handover->notes
            ]);

            $handover->items()->delete();

            foreach ($data['products'] as $product) {

                $handover->items()->create([
                    'product_id' => $product['product_id'],
                    'requested_quantity' => $product['quantity']
                ]);
            }

            return $handover->load('requesterWarehouse', 'providerWarehouse', 'items.product.mainCategory', 'items.product.subCategory', 'requester', 'responder');
        });
    }
    public function show(WarehouseHandover $warehouseHandover)
    {
        $user = Auth::user();

        if (!$this->canAccessHandover($warehouseHandover, $user)) {
            abort(403, __('messages.unauthorized_action'));
        }
        $warehouseHandover->load('requesterWarehouse', 'providerWarehouse', 'items.product.mainCategory', 'items.product.subCategory', 'requester', 'responder');
        // dd($warehouseHandover);
        return $warehouseHandover;
    }


    public function delete(WarehouseHandover $handover)
    {
        $user = Auth::user();

        if (
            !$user->hasPermission('full_access_handover_requests') &&
            $handover->requested_by !== $user->id
        ) {
            abort(403, __('messages.unauthorized_action'));
        }

        if ($handover->status !== HandOverStatus::pending->value) {
            throw new CustomException('لا يمكن حذف الطلب بعد الموافقة عليه');
        }

        return $handover->delete();
    }

    public function approveRequest(WarehouseHandover $handover, array $items)
    {
        $user = Auth::user();

        $warehouse = $handover->providerWarehouse;
        if (
            !$user->hasPermission('full_access_handover_requests') &&
            $warehouse->keeper_id !== $user->id
        ) {
            abort(403, __('messages.unauthorized_action'));
        }
        if ($handover->status !== HandOverStatus::pending->value) {
            throw new CustomException('الطلب لا يمكن الموافقة عليه');
        }

        return DB::transaction(function () use ($handover, $items) {

            foreach ($items as $itemData) {

                $item = WarehouseHandoverItem::findOrFail($itemData['id']);

                $approvedQty = $itemData['approved_quantity'];

                $inventory = ProductWarehouse::where([
                    'warehouse_id' => $handover->provider_warehouse_id,
                    'product_id' => $item->product_id
                ])->lockForUpdate()->first();

                if (!$inventory || $inventory->available < $approvedQty) {
                    throw new CustomException('الكمية المطلوبة من المنتج غير متوفرة: معرف المنتج هو:  ' . $item->product_id);
                }

                $inventory->increment('reserved_quantity', $approvedQty);

                $item->update([
                    'approved_quantity' => $approvedQty
                ]);
            }

            $handover->update([
                'status' => HandOverStatus::approved->value,
                'responded_by' => auth()->id(),
                'approved_at' => now()
            ]);

            return $handover->load('requesterWarehouse', 'providerWarehouse', 'items.product.mainCategory', 'items.product.subCategory', 'requester', 'responder');
        });
    }

    public function rejectRequest(WarehouseHandover $handover, $reason = null)
    {
        $user = Auth::user();

        $warehouse = $handover->providerWarehouse;
        if (
            !$user->hasPermission('full_access_handover_requests') &&
            $warehouse->keeper_id !== $user->id
        ) {
            abort(403, __('messages.unauthorized_action'));
        }
        if ($handover->status !== HandOverStatus::pending->value) {
            throw new CustomException('لا يمكن رفض الطلب');
        }

        $handover->update([
            'status' => HandOverStatus::rejected->value,
            'responded_by' => auth()->id(),
            'notes' => $reason
        ]);

        return $handover->load('requesterWarehouse', 'providerWarehouse', 'items.product.mainCategory', 'items.product.subCategory', 'requester', 'responder');
    }

    public function shipHandover(WarehouseHandover $handover)
    {
        $user = Auth::user();

        $warehouse = $handover->providerWarehouse;
        if (
            !$user->hasPermission('full_access_handover_requests') &&
            $warehouse->keeper_id !== $user->id
        ) {
            abort(403, __('messages.unauthorized_action'));
        }
        if ($handover->status !== HandOverStatus::approved->value) {
            throw new CustomException('فقط الطلبات التي تم الموفقة عليها يمكن نقلها');
        }

        return DB::transaction(function () use ($handover) {

            foreach ($handover->items as $item) {

                $inventory = ProductWarehouse::where([
                    'warehouse_id' => $handover->provider_warehouse_id,
                    'product_id' => $item->product_id
                ])->lockForUpdate()->first();

                $inventory->decrement('reserved_quantity', $item->approved_quantity);
                $inventory->decrement('quantity', $item->approved_quantity);

                $item->update([
                    'delivered_quantity' => $item->approved_quantity
                ]);
            }

            $handover->update([
                'status' => HandOverStatus::in_transit->value
            ]);

            return $handover->load('requesterWarehouse', 'providerWarehouse', 'items.product.mainCategory', 'items.product.subCategory', 'requester', 'responder');
        });
    }

    public function completeHandover(WarehouseHandover $handover)
    {
        $user = Auth::user();

        if (
            !$user->hasPermission('full_access_handover_requests') &&
            $handover->requesterWarehouse?->keeper_id !== $user->id
        ) {
            abort(403, __('messages.unauthorized_action'));
        }

        if ($handover->status !== HandOverStatus::in_transit->value) {
            throw new CustomException('لا يمكن إكمال هذا الطلب');
        }

        return DB::transaction(function () use ($handover) {

            foreach ($handover->items as $item) {

                $inventory = ProductWarehouse::firstOrCreate(
                    [
                        'warehouse_id' => $handover->requester_warehouse_id,
                        'product_id' => $item->product_id
                    ],
                    [
                        'quantity' => 0,
                        'reserved_quantity' => 0
                    ]
                );

                $inventory->increment('quantity', $item->delivered_quantity);
            }

            $handover->update([
                'status' => HandOverStatus::completed->value,
                'completed_at' => now()
            ]);

            return $handover->load('requesterWarehouse', 'providerWarehouse', 'items.product.mainCategory', 'items.product.subCategory', 'requester', 'responder');
        });
    }

    // public function warehouseSummary($warehouseId)
    // {
    //     $user = Auth::user();

    //     $warehouse = Warehouse::with('keeper')->findOrFail($warehouseId);

    //     if (
    //         !$user->hasPermission('full_access_handover_requests') &&
    //         $warehouse->keeper_id !== $user->id
    //     ) {
    //         abort(403, __('messages.unauthorized_action'));
    //     }

    //     $incoming = WarehouseHandover::with('items.product')
    //         ->where('provider_warehouse_id', $warehouseId)
    //         ->whereIn('status', [
    //             HandOverStatus::approved->value,
    //             HandOverStatus::in_transit->value
    //         ])
    //         ->get();

    //     $outgoing = WarehouseHandover::with('items.product')
    //         ->where('requester_warehouse_id', $warehouseId)
    //         ->whereIn('status', [
    //             HandOverStatus::pending->value,
    //             HandOverStatus::approved->value
    //         ])
    //         ->get();

    //     return [
    //         'incoming_handovers' => WarehouseHandoverResource::collection($incoming),
    //         'outgoing_handovers' => WarehouseHandoverResource::collection($outgoing)
    //     ];
    // }
}
