<?php

namespace App\Notifications\Users\post;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPostFollowersNotification extends Notification
{
    use Queueable;

    public $user;
    public $postId;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $postId)
    {
        $this->user = $user;
        $this->postId = $postId;
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
            "title_en" => "New Post Alert!",
            "title_ar" => "تنبيه: منشور جديد!",
            "body_en" => "Exciting news! " . $this->user->username . " has just shared a new post. Stay updated and check it out!",
            "body_ar" => "خبر رائع! قام " . $this->user->username . " بنشر منشور جديد. ابقَ على اطلاع وتصفحه الآن!",
            'options' => [
                'type'      => 'single_post',
                'slug'      => null,
                'post_id'   => $this->postId
            ]
        ];
    }
}
