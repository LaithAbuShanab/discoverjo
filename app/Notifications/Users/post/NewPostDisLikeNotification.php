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
        $username = $this->user->name;

        return [
            "title_en" => __('app.notifications.new-post-dislike-title', [], 'en'),
            "title_ar" => __('app.notifications.new-post-dislike-title', [], 'ar'),
            "body_en"  => __('app.notifications.new-post-dislike-body', ['username' => $username], 'en'),
            "body_ar"  => __('app.notifications.new-post-dislike-body', ['username' => $username], 'ar'),
            'options'  => [
                'type'    => 'single_post',
                'slug'    => null,
                'post_id' => $this->postId
            ]
        ];
    }
}
