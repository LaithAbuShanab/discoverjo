<?php

namespace App\Notifications\Users\guide;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuideActiveNotification extends Notification
{
    use Queueable;

    public $user;

    /**
     * Create a new notification instance.
     */

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('app.account_active_notification'))
            ->view('emails.guide-active', ['user' => $this->user]);
    }
}
