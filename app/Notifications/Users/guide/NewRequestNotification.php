<?php

namespace App\Notifications\Users\guide;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRequestNotification extends Notification
{
    use Queueable;

    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
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
            "title_en" => "New Request In Your Trip",
            "title_ar" => "طلب جديد للإنضمام للرحلة",
            "body_en" => "User " . $this->user->username . " has sent a request to join your trip",
            "body_ar" => "المستخدم " . $this->user->username . " قام بارسال طلب للإنضمام للرحلة",
        ];
    }
}
