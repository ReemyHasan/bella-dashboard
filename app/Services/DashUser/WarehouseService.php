<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\DashUser;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    public function list($request)
    {
        $user = Auth::user();

        $query = Warehouse::with('zone', 'keeper')
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest();

        // If user doesn't have full_view permission
        // if (!$user->hasPermission('full_view_warehouses')) {
        //     $query->where('keeper_id', $user->id);
        // }

        return $query->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function warehouseProducts($request, Warehouse $warehouse)
    {
        $user = Auth::user();
        // if (!$user->hasPermission('full_view_warehouses') && $warehouse->keeper_id !== $user->id) {
        //     abort(403, __('messages.unauthorized_action'));
        // }
        return ProductWarehouse::with(
            [
                'product.mainCategory',
                'product.subCategory',
                'product.mainImage'
            ]
        )->where('warehouse_id', $warehouse->id)->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->paginate($request->input('per_page') ?? PaginationEnum::GeneralPagination->value);


        // return Product::query()
        //     ->select('products.*', 'product_warehouses.quantity as warehouse_quantity' , 'product_warehouses.quantity as warehouse_quantity')
        //     ->join('product_warehouses', function ($join) use ($warehouse) {
        //         $join->on('products.id', '=', 'product_warehouses.product_id')
        //             ->where('product_warehouses.warehouse_id', $warehouse->id);
        //     })->filterBy($request->all())
        //     ->sortBy($request->get('sort', ['created_at' => 'desc']))
        //     ->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $warehouse = Warehouse::create([
                'name' => $data['name'],
                'active' => $data['active'],
                'is_main' => $data['is_main'],
                'zone_id' => $data['zone_id'],
                'keeper_id' => $data['keeper_id']

            ]);
            // if (isset($data['keeper_id'])) {
            //     $appUser = AppUser::findOrFail($data['keeper_id']);
            //     $appUser->update([
            //         'is_warehouse_man' => true,
            //         'is_delivery_man' => true,
            //         'warehouse_id' => $warehouse->id

            //     ]);
            // }
            $warehouse->load('zone', 'keeper');


            return $warehouse;
        });
    }

    public function update(Warehouse $warehouse, array $data)
    {
        return DB::transaction(function () use ($warehouse, $data) {
            $oldKeeper = $warehouse->keeper;
            $oldKeeperId = $warehouse->keeper_id;

            $warehouse->update([
                'name' => $data['name'],
                'active' => $data['active'],
                'is_main' => $data['is_main'],
                'zone_id' => $data['zone_id'],
                'keeper_id' => $data['keeper_id']
            ]);

            // if (isset($data['keeper_id']) && $data['keeper_id'] != $oldKeeperId) {
            //     if ($oldKeeper)
            //         $oldKeeper->update([
            //             'is_warehouse_man' => false,
            //             'is_delivery_man' => false,

            //         ]);
            //     $appUser = AppUser::findOrFail($data['keeper_id']);
            //     $appUser->update([
            //         'is_warehouse_man' => true,
            //         'is_delivery_man' => true,
            //         'warehouse_id' => $warehouse->id

            //     ]);
            // }

            $warehouse->load('zone', 'keeper');

            return $warehouse;
        });
    }
    public function show(Warehouse $warehouse)
    {
        $user = Auth::user();

        if (!$user->hasPermission('full_view_warehouses') && $warehouse->keeper_id !== $user->id) {
            abort(403, __('messages.unauthorized_action'));
        }
        $warehouse->load('zone', 'keeper');
        return $warehouse;
    }

    public function delete(Warehouse $warehouse)
    {
        return $warehouse->delete();
    }


    public function selectAvailable($zone = null, $is_main = null)
    {

        $warehouses = Warehouse::when(!is_null($zone), function ($query) use ($zone) {
            $query->where('zone_id', $zone);
        })->when(!is_null($is_main), function ($query) use ($is_main) {
            $query->where('is_main', $is_main);
        })->where('active', true)->orderBy('id')->get([
            'id',
            'name',
            'active',
            'is_main',
            'zone_id'
        ]);

        return $warehouses;
    }

    public function updateWarehouseProducts(
        Warehouse $warehouse,
        array $items,
        array $deletedItems = []
    ) {
        DB::transaction(function () use ($warehouse, $items, $deletedItems) {

            $items = collect($items)->keyBy('product_id');

            $existing = ProductWarehouse::where('warehouse_id', $warehouse->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            if (!empty($deletedItems)) {

                $toDelete = $existing->whereIn('product_id', $deletedItems);

                foreach ($toDelete as $productWarehouse) {

                    // 🔒 Optional safety check
                    if ($productWarehouse->reserved_quantity > 0) {
                        throw new CustomException('لا يمكن حذف منتج لديه كمية محجوزة.');
                    }

                    $productWarehouse->delete();
                }
            }

            foreach ($items as $productId => $item) {

                if ($existing->has($productId)) {

                    $productWarehouse = $existing[$productId];

                    // Optional: skip if same quantity
                    if ($productWarehouse->quantity != $item['quantity']) {
                        $productWarehouse->update([
                            'quantity' => $item['quantity']
                        ]);
                    }

                    continue;
                }

                ProductWarehouse::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id'   => $productId,
                    'quantity'     => $item['quantity'],
                ]);
            }
        });
    }
}
