<?php

namespace App\Notifications\Users\Trip;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AcceptCancelNotification extends Notification
{
    use Queueable;

    public $trip, $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($trip, $status)
    {
        $this->status = $status;
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
            "title_en" => $this->status == "accept" ? "You have been accepted into the trip" : "You have been rejected from the trip",
            "title_ar" => $this->status == "accept" ? "تم قبول طلبك للإنضمام للرحلة" : "تم رفض طلبك للإنضمام للرحلة",
            "body_en" => $this->status == "accept" ? "User " . $this->trip->user->username . " has accepted your request for the trip " . $this->trip->name  : "User " . $this->trip->user->username . " has rejected you from the trip " . $this->trip->name,
            "body_ar" => $this->status == "accept" ? "قام المستخدم" . $this->trip->user->username . "بقبول طلبك في الرحلة" . $this->trip->name : "قام المستخدم" . $this->trip->user->username . "برفض طلبك من الرحلة" . $this->trip->name,
            "options" => [
                'type'    => 'single_trip',
                'slug'    => $this->trip->slug,
                'trip_id' => $this->trip->id,
            ]
        ];
    }
}
