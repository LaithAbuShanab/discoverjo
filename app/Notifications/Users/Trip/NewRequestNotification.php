<?php

namespace App\Notifications\Users\Trip;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRequestNotification extends Notification
{
    use Queueable;

    public $user;
    public $trip;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $trip)
    {
        $this->user = $user;
        $this->trip = $trip;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            "title_en" => "New Request In Your Trip",
            "title_ar" => "طلب جديد للإنضمام للرحلة",
            "body_en" => "User " . $this->user->username . " has sent a request to join your trip",
            "body_ar" => "المستخدم " . $this->user->username . " قام بارسال طلب للإنضمام للرحلة",
            "options" => [
                'type'    => 'single_trip',
                'slug'    => $this->trip->slug,
                'trip_id' => $this->trip->id,
            ]
        ];
    }
}
