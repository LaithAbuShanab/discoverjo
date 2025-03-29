<?php

namespace App\Notifications\Users\Trip;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewTripNotification extends Notification
{
    use Queueable;

    /**
     * @var \App\Models\User
     */
    public $user;

    /**
     * @var \App\Models\Trip
     */
    public $trip;

    /**
     * @var int
     */
    public $type;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Trip  $trip
     */
    public function __construct($user, $trip)
    {
        $this->user = $user;
        $this->trip = $trip;
        $this->type = $trip->trip_type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $baseData = [
            'options' => [
                'type'    => 'single_trip',
                'slug'    => $this->trip->slug,
                'trip_id' => $this->trip->id,
            ]
        ];

        switch ($this->type) {
            case 1:
                return array_merge($baseData, [
                    'title_en' => 'There is a new trip',
                    'title_ar' => 'يوجد رحلة جديدة',
                    'body_en'  => 'The User ' . $this->user->username . ' has created a new trip',
                    'body_ar'  => 'تم إنشاء رحلة جديدة بواسطة المستخدم ' . $this->user->username,
                ]);

            case 2:
                return array_merge($baseData, [
                    'title_en' => 'New Trip Invitation',
                    'title_ar' => 'دعوة رحلة جديدة',
                    'body_en'  => $this->user->username . ' has invited you to join a trip.',
                    'body_ar'  => $this->user->username . ' دعاك للانضمام إلى رحلة.',
                ]);

            default:
                return array_merge($baseData, [
                    'title_en' => 'Trip Notification',
                    'title_ar' => 'إشعار رحلة',
                    'body_en'  => 'There is an update regarding a trip.',
                    'body_ar'  => 'هناك تحديث بخصوص رحلة.',
                ]);
        }
    }
}
