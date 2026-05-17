<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.projects.app.credentials'));
        // ->withServiceAccount(base_path(config('firebase.projects.app.credentials')));

        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send notification to specific device(s)
     *
     * @param string|array $tokens One or more FCM tokens
     * @param string $title
     * @param string $body
     * @param array|null $data Optional data payload
     */
    public function sendNotification($tokens, string $title, string $body, array $data = []): void
    {
        $tokens = is_array($tokens) ? $tokens : [$tokens];

        $notification = Notification::create($title, $body);

        $sound = 'default';
        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::new()
                    ->withNotification($notification)
                    ->withAndroidConfig([
                        'notification' => [
                            'sound' => $sound,
                            'channel_id' => 'high_priority_channel',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                        'priority' => 'high',
                    ])
                    ->withApnsConfig([
                        'payload' => [
                            'aps' => [
                                'sound' => $sound,
                                'content-available' => 1,
                            ],
                        ],
                    ])
                    ->withData($data)
                    ->toToken($token);

                $this->messaging->send($message);
            } catch (\Throwable $e) {
                Log::warning('FCM Notification Failed', [
                    'token' => $token,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
