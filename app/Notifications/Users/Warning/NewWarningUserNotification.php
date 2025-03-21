<?php

namespace App\Notifications\Users\Warning;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewWarningUserNotification extends Notification
{
    use Queueable;

    /**
     * Type of the notification: 'warning' or 'blocked'.
     *
     * @var string
     */
    public string $type;

    /**
     * Create a new notification instance.
     *
     * @param string $type 'warning' or 'blocked'
     */
    public function __construct(string $type = 'warning')
    {
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->type === 'blocked') {
            return [
                'title_en' => 'Account Temporarily Blocked',
                'title_ar' => 'تم حظر الحساب مؤقتاً',
                'body_en' => 'Your account has been blocked for two weeks due to repeated violations of our policies.',
                'body_ar' => 'تم حظر حسابك لمدة أسبوعين بسبب تكرار المخالفات لسياساتنا.',
            ];
        }

        // Default: warning
        return [
            'title_en' => 'New Warning',
            'title_ar' => 'تحذير جديد',
            'body_en' => 'You have received a warning due to unethical behavior. Please adhere to the community guidelines.',
            'body_ar' => 'لقد تلقيت تحذيراً بسبب سلوك غير أخلاقي. يرجى الالتزام بإرشادات المجتمع.',
        ];
    }
}
