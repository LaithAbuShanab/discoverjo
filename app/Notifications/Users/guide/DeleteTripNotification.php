<?php

namespace App\Notifications\Users\guide;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeleteTripNotification extends Notification
{
    use Queueable;

    public $guideTrip;

    /**
     * Create a new notification instance.
     */
    public function __construct($guideTrip)
    {
        $this->guideTrip = $guideTrip;
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
        $tripNameEn = $this->guideTrip->getTranslation('name', 'en') ?? $this->guideTrip->name;
        $tripNameAr = $this->guideTrip->getTranslation('name', 'ar') ?? $this->guideTrip->name;

        $guideUserName = $this->guideTrip->user->first_name . ' ' . $this->guideTrip->user->last_name;

        return [
            'title_en' => __('app.notifications.trip_deleted-title', [], 'en'),
            'title_ar' => __('app.notifications.trip_deleted-title', [], 'ar'),

            'body_en'  => __('app.notifications.trip_deleted-body', [
                'guide_user' => $guideUserName,
                'trip'       => $tripNameEn,
            ], 'en'),

            'body_ar'  => __('app.notifications.trip_deleted-body', [
                'guide_user' => $guideUserName,
                'trip'       => $tripNameAr,
            ], 'ar'),

            'options' => [],
        ];
    }
}
