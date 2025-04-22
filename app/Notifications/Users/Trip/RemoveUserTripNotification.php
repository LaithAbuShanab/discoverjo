<?php

namespace App\Notifications\Users\Trip;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RemoveUserTripNotification extends Notification
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

            "title_en" => "You have been removed from the trip.",
            "title_ar" => "تمت إزالتك من الرحلة",

            "body_en" => "The User " . $this->user->username . " has removed you from the trip " . $this->trip->name,
            "body_ar" => "قام المستخدم " . $this->user->username . " بإزالتك من الرحلة " . $this->trip->name,

            "options" => [
                'type'    => 'single_trip',
                'slug'    => $this->trip->slug,
                'trip_id' => $this->trip->id,
            ]
        ];
    }
}
