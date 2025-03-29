<?php

namespace App\Notifications\Users\Trip;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AcceptCancelInvitationNotification extends Notification
{
    use Queueable;

    public $status, $username, $trip;

    /**
     * Create a new notification instance.
     */
    public function __construct($status, $username, $trip)
    {
        $this->status = $status;
        $this->username = $username;
        $this->trip = $trip;
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
            "title_en" => $this->status == "accept" ? "Invitation accepted" : "Invitation rejected",
            "title_ar" => $this->status == "accept" ? "تم قبول الدعوة" : "تم رفض الدعوة",
            "body_en" => $this->status == "accept" ?  $this->username . " has accepted the invitation" : $this->username . " has rejected the invitation",
            "body_ar" => $this->status == "accept" ? " وافق المستخدم" . $this->username . "على الدعوة"  : " رفض المستخدم" . $this->username . "الدعوة",
            "options" => [
                'type'    => 'single_trip',
                'slug'    => $this->trip->slug,
                'trip_id' => $this->trip->id,
            ]
        ];
    }
}
