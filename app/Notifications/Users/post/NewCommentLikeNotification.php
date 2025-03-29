<?php

namespace App\Notifications\Users\post;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentLikeNotification extends Notification
{
    use Queueable;

    public $user;
    public $commentId;
    public $postId;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $commentId, $postId)
    {
        $this->user = $user;
        $this->commentId = $commentId;
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
            "title_en" => "New Like",
            "title_ar" => "اعجاب جديد",
            "body_en" => "The User " . $this->user->username . " has liked your Comment",
            "body_ar" => "المستخدم اعجب بتعليقك " . $this->user->username,
            'options' => [
                'type'       => 'comment',
                'slug'       => null,
                'post_id'    => $this->postId,
                'comment_id' => $this->commentId,
            ]
        ];
    }
}
