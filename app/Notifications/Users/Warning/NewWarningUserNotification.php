<?php

namespace App\Notifications\Users\Warning;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewWarningUserNotification extends Notification
{
    use Queueable;

    /**
     * Type of the notification: 'warning', 'blocked', or 'blacklisted'.
     *
     * @var string
     */
    public string $type;

    /**
     * Create a new notification instance.
     *
     * @param string $type 'warning', 'blocked', or 'blacklisted'
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
                'title_en' => 'Temporary Account Suspension',
                'title_ar' => 'إشعار حظر مؤقت للحساب',
                'body_en'  => 'Your account has been temporarily suspended for two weeks due to repeated policy violations. Please review our guidelines to avoid future suspensions.',
                'body_ar'  => 'تم تعليق حسابك مؤقتًا لمدة أسبوعين نتيجة تكرار المخالفات لسياسات المنصة. نرجو منك مراجعة السياسات لتجنب الحظر مستقبلاً.',
            ];
        }

        if ($this->type === 'blacklisted') {
            return [
                'title_en' => 'Account Status Notification',
                'title_ar' => 'إشعار بشأن حالة الحساب',
                'body_en'  => 'We regret to inform you that your account has been permanently blacklisted due to serious violations of our policies. For more details, please contact support.',
                'body_ar'  => 'نأسف لإبلاغك بأن حسابك قد أُدرج في القائمة السوداء بشكل دائم بسبب مخالفات جسيمة لسياسات الاستخدام. لمزيد من التفاصيل، يرجى التواصل مع فريق الدعم.',
            ];
        }

        // Default: warning
        return [
            'title_en' => 'Account Behavior Notice',
            'title_ar' => 'تنبيه بشأن سلوك الحساب',
            'body_en'  => 'A warning has been issued to your account due to behavior that does not align with our community guidelines. Please adhere to the rules to avoid further action.',
            'body_ar'  => 'تم توجيه تحذير إلى حسابك نتيجة سلوك لا يتوافق مع إرشادات المجتمع. نرجو الالتزام بالسياسات لتفادي الإجراءات المستقبلية.',
        ];
    }
}
