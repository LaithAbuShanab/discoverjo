<?php

namespace App\Notifications\Users\guide;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class DeleteAllRequestNotification extends Notification
{
    use Queueable;

    public $trip;
    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $trip)
    {
        $this->trip = $trip;
        $this->user = $user;
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
        $tripNameEn = $this->trip->getTranslation('name', 'en') ?? $this->trip->name;
        $tripNameAr = $this->trip->getTranslation('name', 'ar') ?? $this->trip->name;

        return [
            "title_en" => Lang::get('app.notifications.delete-user-requests-title', [], 'en'),
            "title_ar" => Lang::get('app.notifications.delete-user-requests-title', [], 'ar'),
            "body_en"  => Lang::get('app.notifications.delete-user-requests-body', [
                'username' => $this->user->username,
                'trip'     => $tripNameEn,
            ], 'en'),
            "body_ar"  => Lang::get('app.notifications.delete-user-requests-body', [
                'username' => $this->user->username,
                'trip'     => $tripNameAr,
            ], 'ar'),
            "options" => [
                'type'    => 'guide_trip',
                'slug'    => $this->trip->slug,
                'trip_id' => $this->trip->id,
            ]
        ];
    }
}

