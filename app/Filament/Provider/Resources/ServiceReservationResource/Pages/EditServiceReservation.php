<?php

namespace App\Filament\Provider\Resources\ServiceReservationResource\Pages;

use App\Filament\Provider\Resources\ServiceReservationResource;
use App\Models\Service;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Users\Service\ChangeStatusReservationNotification;
use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceReservation extends EditRecord
{
    protected static string $resource = ServiceReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $newStatus = (int) $data['status'];

        if ($newStatus !== $this->record->status && in_array($newStatus, [1, 2])) {
            $user = User::find($this->record->user_id);
            $service = $this->record->service;

            $this->record->status = $newStatus;
            $this->record->save();

            DatabaseNotification::where('type', 'App\Notifications\Users\Service\ChangeStatusReservationNotification')
                ->whereJsonContains('data->options->reservation_id', $this->record->id)
                ->where('notifiable_id', $user->id)
                ->delete();

            $userLang = $user->lang;

            $statusLabel = $newStatus === 1
                ? Lang::get('app.notifications.status-confirmed', [], $userLang)
                : Lang::get('app.notifications.status-cancelled', [], $userLang);

            $notificationData = [
                'notification' => [
                    'title' => Lang::get('app.notifications.reservation-status-updated-title', [], $userLang),
                    'body'  => Lang::get('app.notifications.reservation-status-updated-body', [
                        'reservation_id' => $this->record->id,
                        'status'         => $statusLabel,
                    ], $userLang),
                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                    'sound' => 'default',
                ],
                'data' => [
                    'type'           => 'service_reservation',
                    'slug'           => $service->slug,
                    'service_id'     => $service->id,
                    'reservation_id' => $this->record->id,
                    'new_status'     => $newStatus === 1 ? 'confirmed' : 'cancelled',
                ],
            ];

            $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
            if (!empty($tokens)) {
                sendNotification($tokens, $notificationData);
            }

            Notification::send($user, new ChangeStatusReservationNotification($this->record));
        }

        if (in_array($newStatus, [0, 3])) {
            unset($data['status']);
        }

        return $data;
    }
}
