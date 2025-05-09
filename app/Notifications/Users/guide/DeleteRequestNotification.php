<?php

namespace App\Notifications\Users\guide;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class DeleteRequestNotification extends Notification
{
    use Queueable;

    public $trip;
    public $guideTripUser;

    /**
     * Create a new notification instance.
     */
    public function __construct($trip, $guideTripUser)
    {
        $this->trip = $trip;
        $this->guideTripUser = $guideTripUser;
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

        $guideUserName = $this->guideTripUser->first_name . ' ' . $this->guideTripUser->last_name;

        return [
            "title_en" => Lang::get('app.notifications.delete-request-title', [], 'en'),
            "title_ar" => Lang::get('app.notifications.delete-request-title', [], 'ar'),
            "body_en"  => Lang::get('app.notifications.delete-request-body', [
                'guide_user' => $guideUserName,
                'trip'       => $tripNameEn,
            ], 'en'),
            "body_ar"  => Lang::get('app.notifications.delete-request-body', [
                'guide_user' => $guideUserName,
                'trip'       => $tripNameAr,
            ], 'ar'),
            "options" => [
                'type'    => 'guide_trip',
                'slug'    => $this->trip->slug,
                'trip_id' => $this->trip->id,
            ]
        ];
    }
}
