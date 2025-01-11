<?php

namespace App\Notifications\Users\Trip;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTripNotification extends Notification
{
    use Queueable;

    public $user;
    public $type;
    /**
     * Create a new notification instance.
     */
    public function __construct($user, $type)
    {
        $this->user = $user;
        $this->type = $type;
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
        if ($this->type == 1) {
            return [
                "title_en" => "There is a new trip",
                "title_ar" => "يوجد رحلة جديدة",
                "body_en" => "The User " . $this->user->name . " has created a new trip",
                "body_ar" => "تم إنشاء رحلة جديدة بواسطة المستخدم " . $this->user->name
            ];
        } elseif ($this->type == 2) {
            return [
                "title_en" => "New Trip Invitation",
                "title_ar" => "دعوة رحلة جديدة",
                "body_en" => $this->user->name . " has invited you to join a trip.",
                "body_ar" => $this->user->name . " دعاك للانضمام إلى رحلة."
            ];
        }
    }
}
