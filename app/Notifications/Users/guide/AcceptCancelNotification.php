<?php

namespace App\Notifications\Users\guide;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AcceptCancelNotification extends Notification
{
    use Queueable;

    public $trip, $status, $guideTripUser;

    /**
     * Create a new notification instance.
     */
    public function __construct($trip, $status, $guideTripUser)
    {
        $this->status = $status;
        $this->trip = $trip;
        $this->guideTripUser = $guideTripUser;
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
        $tripNameEn = $this->trip->getTranslation('name', 'en');
        $tripNameAr = $this->trip->getTranslation('name', 'ar');
        $fullName   = $this->guideTripUser?->first_name . ' ' . $this->guideTripUser?->last_name;

        if ($this->status == 1) {
            return [
                'title_en' => __('app.notifications.accepted-guide-trip-title', ['username' => $fullName], 'en'),
                'title_ar' => __('app.notifications.accepted-guide-trip-title', ['username' => $fullName], 'ar'),
                'body_en'  => __('app.notifications.accepted-guide-trip-body', [
                    'username'  => $fullName,
                    'trip_name' => $tripNameEn
                ], 'en'),
                'body_ar'  => __('app.notifications.accepted-guide-trip-body', [
                    'username'  => $fullName,
                    'trip_name' => $tripNameAr
                ], 'ar'),
                'options'  => [
                    'type'    => 'guide_trip',
                    'slug'    => $this->trip->slug,
                    'trip_id' => $this->trip->id,
                ]
            ];
        } else {
            return [
                'title_en' => __('app.notifications.declined-guide-trip-title', ['username' => $fullName], 'en'),
                'title_ar' => __('app.notifications.declined-guide-trip-title', ['username' => $fullName], 'ar'),
                'body_en'  => __('app.notifications.declined-guide-trip-body', [
                    'username'  => $fullName,
                    'trip_name' => $tripNameEn
                ], 'en'),
                'body_ar'  => __('app.notifications.declined-guide-trip-body', [
                    'username'  => $fullName,
                    'trip_name' => $tripNameAr
                ], 'ar'),
                'options'  => [
                    'type'    => 'guide_trip',
                    'slug'    => $this->trip->slug,
                    'trip_id' => $this->trip->id,
                ]
            ];
        }
    }
}
