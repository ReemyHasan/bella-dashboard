<?php

namespace App\Observers;

use App\Enums\NotificationType;
use App\Events\NotificationEvent;
use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        event(new NotificationEvent(
            type: NotificationType::NEW_PRODUCT,
            data: [
                'product' => $product

            ]
        ));
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        event(new NotificationEvent(
            type: NotificationType::UPDATE_PRODUCT,
            data: [
                'product' => $product,
            ]
        ));
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
