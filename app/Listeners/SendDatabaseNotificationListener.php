<?php

namespace App\Listeners;

use App\Events\NotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDatabaseNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(NotificationEvent $event): void
    {
        $handler = app($event->type->handler());

        if (method_exists($handler, 'handleDatabase')) {
            $handler->handleDatabase($event);
        }
    }
}
