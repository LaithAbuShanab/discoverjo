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
        $fullName = $this->guideTripUser->first_name . ' ' . $this->guideTripUser->last_name;

        if ($this->status == 1) {
            return [
                'title_en' => "$fullName was accepted on the trip.",
                'title_ar' => "تم قبول $fullName من الرحلة",
                'body_en'  => "$fullName was accepted on the trip $tripNameEn",
                'body_ar'  => "تم قبول $fullName من الرحلة $tripNameAr",
            ];
        } else {
            return [
                'title_en' => "$fullName was rejected on the trip.",
                'title_ar' => "تم رفض $fullName من الرحلة",
                'body_en'  => "$fullName was rejected on the trip $tripNameEn",
                'body_ar'  => "تم رفض $fullName من الرحلة $tripNameAr",
            ];
        }
    }
}
