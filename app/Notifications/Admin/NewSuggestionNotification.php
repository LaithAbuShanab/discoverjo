<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSuggestionNotification extends Notification
{
    use Queueable;

    public $suggestionPlace;

    /**
     * Create a new notification instance.
     */
    public function __construct($suggestionPlace)
    {
        $this->suggestionPlace = $suggestionPlace;
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
            'place_name' => $this->suggestionPlace->place_name,
            'address' => $this->suggestionPlace->address,
            'type' => 'new-suggestion',
            'image' => asset('assets/images/icons/suggestion.png'),
        ];
        return $data;
    }
}
