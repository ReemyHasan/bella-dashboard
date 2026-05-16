<?php

namespace App\Services\Mobile;

use App\Enums\HandOverStatus;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use App\Models\WarehouseHandover;
use Illuminate\Support\Facades\DB;

class WarehouseHandoverService
{
    private function isKeeper($user)
    {
        if (!$user->hasRole('Warehouse Keeper')) {
            throw new CustomException('ليس لديك صلاحية للوصول لهذه الموارد');
        }
    }

    private function canAccessHandover(WarehouseHandover $handover, $user)
    {
        $this->isKeeper($user);
        $canAccess =  $handover->providerWarehouse?->keeper_id === $user->id
            || $handover->requesterWarehouse?->keeper_id === $user->id;

        if (!$canAccess) {
            throw new CustomException('ليس لديك صلاحية للوصول لهذه الموارد');
        }
    }
    public function list($request)
    {
        $user = auth()->user();

        $query = WarehouseHandover::with('requesterWarehouse', 'providerWarehouse')
            ->where(function ($q) use ($user) {

                $q->whereHas('providerWarehouse', function ($w) use ($user) {
                    $w->where('keeper_id', $user->id);
                })
                    ->orWhereHas('requesterWarehouse', function ($w) use ($user) {
                        $w->where('keeper_id', $user->id);
                    });
            });

        return $query->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = auth()->user();

        $this->isKeeper($user);
        $warehouse = Warehouse::where('keeper_id', $user->id)->first();
        if (!$warehouse) {
            throw new CustomException('ليس لديك مستودع.');
        }

        return DB::transaction(function () use ($data, $warehouse, $user) {

            $handover = WarehouseHandover::create([
                'requester_warehouse_id' => $warehouse->id,
                'provider_warehouse_id'  => $data['provider_warehouse_id'],
                'status' => HandOverStatus::pending->value,
                'requested_by' => $user->id,
                'notes' => $data['notes'] ?? null
            ]);

            foreach ($data['products'] as $product) {

                $handover->items()->create([
                    'product_id' => $product['product_id'],
                    'requested_quantity' => $product['quantity'],
                ]);
            }

            return true;
        });
    }

    public function update(WarehouseHandover $handover, array $data)
    {
        $user = auth()->user();
        $this->isKeeper($user);

        $warehouse = $handover->requesterWarehouse;
        if (
            $warehouse->keeper_id !== $user->id
        ) {
            throw new CustomException('ليس لديك صلاحية للوصول لهذه الموارد');
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

            return true;
        });
    }
    public function show(WarehouseHandover $warehouseHandover)
    {
        $user = auth()->user();
        $this->isKeeper($user);

        $this->canAccessHandover($warehouseHandover, $user);

        $warehouseHandover->load('requesterWarehouse', 'providerWarehouse', 'items.product.mainCategory', 'items.product.subCategory', 'requester', 'responder');

        return $warehouseHandover;
    }


    public function shipHandover(WarehouseHandover $handover)
    {
        $user = auth()->user();
        $this->isKeeper($user);

        $warehouse = $handover->providerWarehouse;
        if (
            $warehouse->keeper_id !== $user->id
        ) {
            throw new CustomException('المستودع المزود هو المسؤول عن عملية النقل.');
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

            return true;
        });
    }

    public function completeHandover(WarehouseHandover $handover)
    {
        $user = auth()->user();
        $this->isKeeper($user);

        if (
            $handover->requesterWarehouse?->keeper_id !== $user->id
        ) {
            throw new CustomException('المستودع الطالب هو المسؤول عن عملية الموافقة على وصول الشحنة.');
        }

        if ($handover->status !== HandOverStatus::in_transit->value) {
            throw new CustomException('لا يمكن إكمال هذا الطلب, يجب أن يكون في حالة نقل من المستودع المقدم.');
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

            return true;
        });
    }
}
