<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Events\NotificationEvent;
use App\Models\CustomerOrder;
use Illuminate\Console\Command;

class SendWaitingOrdersNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:send-waiting-notifications';

    protected $description = 'Send daily notifications for orders waiting today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        CustomerOrder::query()
            ->whereNotNull('waiting_until')
            ->whereDate('waiting_until', today())
            ->chunkById(50, function ($orders) {

                foreach ($orders as $order) {
                    event(new NotificationEvent(
                        type: NotificationType::WAITING_ORDER,
                        data: [
                            'order' => $order,
                        ]
                    ));
                }
            });
    }
}
