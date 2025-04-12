<?php

namespace App\Notifications\Users;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SendNotificationFromAdmin extends Notification
{
    use Queueable;

    public $title;
    public $body;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $body)
    {
        $this->title = $title;
        $this->body = $body;
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
            'title_en' => $this->title['en'],
            'title_ar' => $this->title['ar'],
            'body_en' => $this->body['en'],
            'body_ar' => $this->body['ar'],
            'options' => [
                'type'    => null,
                'slug'    => null
            ]
        ];
    }
}
