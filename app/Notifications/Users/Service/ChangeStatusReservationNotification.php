<?php

namespace App\Notifications\Users\Service;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChangeStatusReservationNotification extends Notification
{
    use Queueable;

    protected $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct($reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $reservationId = $this->reservation->id;
        $status = $this->reservation->status; // 1 = confirmed, 2 = cancelled

        // Optional: Convert numeric status to string label
        $statusTextEn = $status === 1 ? 'confirmed' : 'cancelled';
        $statusTextAr = $status === 1 ? 'تم تأكيد الحجز' : 'تم إلغاء الحجز';

        return [
            'title_en' => 'Reservation Status Updated',
            'title_ar' => 'تم تحديث حالة الحجز',

            'body_en'  => "Your reservation (ID: #{$reservationId}) has been {$statusTextEn}.",
            'body_ar'  => "{$statusTextAr} لطلب الحجز رقم #{$reservationId}.",

            'options'  => [
                'type'           => 'service_reservation',
                'slug'           => $this->reservation->service->slug,
                'service_id'     => $this->reservation->service->id,
                'reservation_id' => $reservationId,
                'new_status'     => $statusTextEn,
            ],
        ];
    }
}
