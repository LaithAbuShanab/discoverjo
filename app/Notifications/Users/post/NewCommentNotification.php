<?php

namespace App\Notifications\Users\post;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
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
            "title_en" => "There is a new comment",
            "title_ar" => "يوجد تعليق جديد",
            "body_en" => "The User " . $this->user->username . " has add new comment",
            "body_ar" => "تم إنشاء تعليق جديد بواسطة المستخدم " . $this->user->username,
            'options' => [
                'type'       => 'comment',
                'slug'       => null,
                'post_id'    => $this->postId,
                'comment_id' => $this->commentId,
            ]
        ];
    }
}
