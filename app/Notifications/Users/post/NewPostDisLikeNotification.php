<?php

namespace App\Notifications\Users\post;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPostDisLikeNotification extends Notification
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
            "title_en" => "New dislike",
            "title_ar" => "عدم اعجاب جديد",
            "body_en" => "The User " . $this->user->name . " has dislike your post",
            "body_ar" => "المستخدم لم يعجب بمنشورك " . $this->user->name,
            'options' => [
                'type'      => 'single_post',
                'slug'      => null,
                'post_id'   => $this->postId
            ]
        ];
    }
}
