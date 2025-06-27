<?php

namespace App\Notifications\Users\Host;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class propertyReservationCreated extends Notification
{
    use Queueable;

    protected $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct($reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $user = $this->reservation->user;
        $reservationId = $this->reservation->id;

        return [
            'title_en' => 'New Reservation Received',
            'title_ar' => 'تم استلام حجز جديد',

            'body_en'  => "User {$user->username} has submitted a new reservation (ID: #{$reservationId}).",
            'body_ar'  => "قام المستخدم {$user->username} بإنشاء حجز جديد (رقم #{$reservationId}).",

            'options'  => [
                'type'           => 'property_reservation',
                'slug'           => $this->reservation->property->slug,
                'property_id'     => $this->reservation->property->id,
                'reservation_id' => $reservationId,
            ],
        ];
    }
}
