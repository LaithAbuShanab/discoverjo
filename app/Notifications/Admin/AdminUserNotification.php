<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminUserNotification extends Notification
{
    use Queueable;

    public $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
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
        $data = [
            'title_en'=>$this->data['title_en'],
            'title_ar'=>$this->data['title_ar'],
            'body_en'=>$this->data['body_en'],
            'body_ar'=>$this->data['body_ar'],
            'image' => asset('assets/images/icons/loudspeaker.png'),
        ];
        return $data;
    }
}
