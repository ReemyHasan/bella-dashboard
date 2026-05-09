<?php

namespace App\Observers;

use App\Models\AppUser;
use App\Models\Warehouse;

class WarehouseObserver
{
    /**
     * Handle the Warehouse "created" event.
     */
    public function created(Warehouse $warehouse): void
    {
        if ($warehouse->keeper_id) {

            $keeper = AppUser::findOrFail($warehouse->keeper_id);

            if ($keeper) {
                $keeper->update([
                    'is_warehouse_man' => true,
                    'is_delivery_man' => true,
                    'warehouse_id' => $warehouse->id
                ]);

                $keeper->assignRole('Warehouse Keeper');
            }
        }
    }

    /**
     * Handle the Warehouse "updated" event.
     */
    public function updated(Warehouse $warehouse): void
    {
        if ($warehouse->wasChanged('keeper_id')) {

            $oldKeeperId = $warehouse->getOriginal('keeper_id');

            if ($oldKeeperId) {
                $oldKeeper = AppUser::findOrFail($oldKeeperId);

                if ($oldKeeper) {
                    $oldKeeper->update([
                        'is_warehouse_man' => false,
                        'is_delivery_man' => false,
                        'warehouse_id' => null
                    ]);
                    $oldKeeper->removeRole('Warehouse Keeper');
                }
            }

            if ($warehouse->keeper_id) {

                $keeper = AppUser::findOrFail($warehouse->keeper_id);

                if ($keeper) {
                    $keeper->update([
                        'is_warehouse_man' => true,
                        'is_delivery_man' => true,
                        'warehouse_id' => $warehouse->id
                    ]);

                    $keeper->assignRole('Warehouse Keeper');
                }
            }
        }
    }

    /**
     * Handle the Warehouse "deleted" event.
     */
    public function deleted(Warehouse $warehouse): void
    {
        //
    }

    /**
     * Handle the Warehouse "restored" event.
     */
    public function restored(Warehouse $warehouse): void
    {
        //
    }

    /**
     * Handle the Warehouse "force deleted" event.
     */
    public function forceDeleted(Warehouse $warehouse): void
    {
        //
    }
}
