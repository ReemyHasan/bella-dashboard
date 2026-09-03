<?php

namespace App\Observers;

use App\Enums\NotificationType;
use App\Events\NotificationEvent;
use App\Models\WarehouseHandover;

class WarehouseHandoverObserver
{
    /**
     * Handle the WarehouseHandover "created" event.
     */
    public function created(WarehouseHandover $warehouseHandover): void
    {
        event(new NotificationEvent(
            type: NotificationType::NEW_WAREHOUSE_HANDOVER,
            data: [
                'handover' => $warehouseHandover->load([
                    'requesterWarehouse.keeper',
                    'providerWarehouse.keeper'
                ]),
            ]
        ));
    }

    /**
     * Handle the WarehouseHandover "updated" event.
     */
    public function updated(WarehouseHandover $warehouseHandover): void
    {
        event(new NotificationEvent(
            type: NotificationType::WAREHOUSE_HANDOVER_UPDATE,
            data: [
                'handover' => $warehouseHandover->load([
                    'requesterWarehouse.keeper',
                    'providerWarehouse.keeper'
                ]),
            ]
        ));
    }

    /**
     * Handle the WarehouseHandover "deleted" event.
     */
    public function deleted(WarehouseHandover $warehouseHandover): void
    {
        //
    }

    /**
     * Handle the WarehouseHandover "restored" event.
     */
    public function restored(WarehouseHandover $warehouseHandover): void
    {
        //
    }

    /**
     * Handle the WarehouseHandover "force deleted" event.
     */
    public function forceDeleted(WarehouseHandover $warehouseHandover): void
    {
        //
    }
}
