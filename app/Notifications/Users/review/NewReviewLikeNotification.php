<?php

namespace App\Notifications\Users\review;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewLikeNotification extends Notification
{
    use Queueable;

    public $user;
    public $review;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $review)
    {
        $this->user = $user;

        $type = strtolower(class_basename($review->reviewable_type));
        $slug = $review->reviewable->slug;
        $review_id = $review->id;

        $this->review = [
            "type" => 'review_' . $type,
            "slug" => $slug,
            "review_id" => $review_id
        ];
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
            'title_en' => __('app.notifications.new-review-like', [], 'en'),
            'title_ar' => __('app.notifications.new-review-like', [], 'ar'),
            'body_en'  => __('app.notifications.new-user-like-in-review', ['username' => $this->user->username], 'en'),
            'body_ar'  => __('app.notifications.new-user-like-in-review', ['username' => $this->user->username], 'ar'),
            "options" => $this->review
        ];
    }
}
