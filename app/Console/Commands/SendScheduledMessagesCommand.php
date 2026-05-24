<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Events\NotificationEvent;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledMessagesCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Message';

    protected $signature = 'messages:send-scheduled';

    public function handle(): void
    {
        Message::query()
            ->whereNull('sent_at')
            ->where('appears_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('appears_to')
                    ->orWhere('appears_to', '>=', now());
            })
            ->chunkById(50, function ($messages) {

                foreach ($messages as $message) {


                    $updated = $message->update([
                        'sent_at' => now(),
                    ]);

                    if (!$updated) {
                        return;
                    }
                    event(new NotificationEvent(
                        type: NotificationType::MESSAGE,
                        data: [
                            'message' => $message,
                        ]
                    ));
                }
            });
    }
}
