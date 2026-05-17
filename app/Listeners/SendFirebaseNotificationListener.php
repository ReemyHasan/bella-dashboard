<?php

namespace App\Listeners;

use App\Events\NotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFirebaseNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationEvent $event): void
    {
        $handler = app($event->type->handler());

        if (method_exists($handler, 'handleFirebase')) {
            $handler->handleFirebase($event);
        }
    }
}
