<?php

namespace App\Notifications\Users\review;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewDisLikeNotification extends Notification
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
            "title_en" => "New dislike",
            "title_ar" => "عدم اعجاب جديد",
            "body_en" => "The User " . $this->user->username . " has dislike your review",
            "body_ar" => "المستخدم لم يعجب بمراجعتك " . $this->user->username
        ];
    }
}
