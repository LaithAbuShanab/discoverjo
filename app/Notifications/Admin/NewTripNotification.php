<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewTripNotification extends Notification
{
    use Queueable;

    public $trip;

    /**
     * Create a new notification instance.
     */
    public function __construct($trip)
    {
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
        $data = [
            'username' => $this->trip->user->username,
            'trip_name' => $this->trip->name,
            'type' => 'new-trip',
            'image' => asset('assets/images/icons/trip.png'),
        ];
        return $data;
    }
}
