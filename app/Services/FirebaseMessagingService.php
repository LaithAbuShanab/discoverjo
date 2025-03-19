<?php

namespace App\Services;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\FirebaseException;
use Illuminate\Support\Facades\Log;

class FirebaseMessagingService
{
    protected $messaging;

    public function __construct()
    {
        // Initialize Firebase
//        $factory = (new Factory)->withServiceAccount(config('firebase.projects.app.credentials'));
        $factory = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body)
    {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification([
                'title' => $title,
                'body' => $body,
            ]);

        try {
            $this->messaging->send($message);
            return ['success' => true, 'message' => 'Notification sent successfully'];
        } catch (FirebaseException $e) {
            Log::error("FCM Send Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
