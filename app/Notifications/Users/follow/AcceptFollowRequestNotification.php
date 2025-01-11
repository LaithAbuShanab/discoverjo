<?php

namespace App\Notifications\Users\follow;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AcceptFollowRequestNotification extends Notification
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
            "title_en" => "Acceptance following request",
            "title_ar" => "موافقة على طلب المتابعة",
            "body_en" => "The User " . $this->user->name . " has accept your following request",
            "body_ar" => "المستخدم قام بالموافقة على طلب المتابعة " . $this->user->name
        ];
    }
}
