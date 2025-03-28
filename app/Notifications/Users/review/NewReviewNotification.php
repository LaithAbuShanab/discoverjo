<?php

namespace App\Notifications\Users\review;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification
{
    use Queueable;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title_en' => $this->data['title_en'] ?? '',
            'title_ar' => $this->data['title_ar'] ?? '',
            'body_en'  => $this->data['body_en'] ?? '',
            'body_ar'  => $this->data['body_ar'] ?? '',
            'options'  => [
                'type'        => $this->data['type'] ?? '',
                'slug'        => $this->data['slug'] ?? '',
                'review_id'   => $this->data['review_id'] ?? ''
            ]
        ];
    }
}
