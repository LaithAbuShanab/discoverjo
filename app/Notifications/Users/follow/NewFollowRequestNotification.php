<?php

namespace App\Notifications\Users\follow;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollowRequestNotification extends Notification
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
            "title_en" => "New following request",
            "title_ar" => "عدم طلب متابعة جديد",
            "body_en" => "The User " . $this->user->username . " has send your new following request",
            "body_ar" => "المستخدم قام بارسال طلب متابعة جديد " . $this->user->username
        ];
    }
}
