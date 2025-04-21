<?php

namespace App\Notifications\Users\review;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReviewDisLikeNotification extends Notification
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

        $type = $review->reviewable_type == "App\Models\Trip" ? "trip" : "guideTrip";
//        $slug = $review->reviewable_type == "App\Models\Trip" ? $review->reviewable->slug : $review->reviewable->trip->slug;
        $review_id = $review->id;

        $this->review = [
            "type" => 'review_' . $type,
//            "slug" => $slug,
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
            "title_en" => "New dislike",
            "title_ar" => "عدم اعجاب جديد",
            "body_en" => "The User " . $this->user->username . " has dislike your review",
            "body_ar" => "المستخدم لم يعجب بمراجعتك " . $this->user->username,
            "options" => $this->review
        ];
    }
}
