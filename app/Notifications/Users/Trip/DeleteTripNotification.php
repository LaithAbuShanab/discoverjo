<?php

namespace App\Notifications\Users\Trip;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class DeleteTripNotification extends Notification
{
    use Queueable;

    public $user;
    public $trip;

    public function __construct($user, $trip)
    {
        $this->user = $user;
        $this->trip = $trip;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $titleEn = __('app.notifications.trip-deleted', [], 'en');
        $titleAr = __('app.notifications.trip-deleted', [], 'ar');

        $bodyEn = __('app.notifications.trip-deleted-body', [
            'username' => $this->user->username,
        ], 'en');

        $bodyAr = __('app.notifications.trip-deleted-body', [
            'username' => $this->user->username,
        ], 'ar');

        return [
            'title_en' => $titleEn,
            'title_ar' => $titleAr,
            'body_en'  => $bodyEn,
            'body_ar'  => $bodyAr,
            'options'  => [
                'type'    => null,
                'slug'    => null,
                'trip_id' => null
            ],
        ];
    }
}
