<?php

namespace App\Observers;

use App\Enums\NotificationType;
use App\Events\NotificationEvent;
use App\Models\Offer;

class OfferObserver
{
    /**
     * Handle the Offer "created" event.
     */
    public function created(Offer $offer): void
    {
        event(new NotificationEvent(
            type: NotificationType::NEW_OFFER,
            data: [
                'offer' => $offer

            ]
        ));
    }

    /**
     * Handle the Offer "updated" event.
     */
    public function updated(Offer $offer): void
    {

        event(new NotificationEvent(
            type: NotificationType::UPDATE_OFFER,
            data: [
                'offer' => $offer,
            ]
        ));
    }

    /**
     * Handle the Offer "deleted" event.
     */
    public function deleted(Offer $offer): void {}

    /**
     * Handle the Offer "restored" event.
     */
    public function restored(Offer $offer): void
    {
        //
    }

    /**
     * Handle the Offer "force deleted" event.
     */
    public function forceDeleted(Offer $offer): void
    {
        //
    }
}
