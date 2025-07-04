<?php

namespace App\Filament\Provider\Resources\ServiceResource\Pages;

use App\Filament\Provider\Resources\ServiceResource;
use App\Models\ServiceBooking;
use App\Models\ServiceBookingDay;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class EditService extends EditRecord
{
    public ?array $serviceBookingData = null;

    protected static string $resource = ServiceResource::class;

    public function getTitle(): string
    {
        return __('panel.provider.edit');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterFill(): void
    {
        $firstServiceBooking = $this->record->serviceBookings->first();

        if (!$firstServiceBooking) {
            return;
        }

        $openingHours = $firstServiceBooking->serviceBookingDays;

        $groupedOpeningHours = $openingHours->groupBy(function ($openingHour) {
            return $openingHour->opening_time . '-' . $openingHour->closing_time;
        });

        $formattedOpeningHours = $groupedOpeningHours->map(function ($hoursGroup) {
            return [
                'day_of_week' => $hoursGroup->pluck('day_of_week')->toArray(),
                'opening_time' => $hoursGroup->first()->opening_time,
                'closing_time' => $hoursGroup->first()->closing_time,
            ];
        })->values()->toArray();

        foreach ($this->data['serviceBookings'] as $key => $serviceBooking) {
            $this->data['serviceBookings'][$key]['serviceBookingDays'] = $formattedOpeningHours;
        }
    }

    protected function beforeSave(): void
    {
        $record = $this->record;
        $newBookings = $this->data['serviceBookings'] ?? [];

        if (!empty($newBookings)) {
            foreach ($newBookings as $serviceBooking) {
                $startDate = Carbon::parse($serviceBooking['available_start_date']);
                $endDate = Carbon::parse($serviceBooking['available_end_date']);
                $newCapacity = $serviceBooking['session_capacity'];
                $newDuration = $serviceBooking['session_duration'];

                $validDays = [];
                $bookingDays = [];

                foreach ($serviceBooking['serviceBookingDays'] as $serviceBookingDay) {
                    foreach ($serviceBookingDay['day_of_week'] as $dayOfWeek) {
                        $openingTime = $serviceBookingDay['opening_time'];
                        $closingTime = $serviceBookingDay['closing_time'];

                        $validDays[] = $dayOfWeek;

                        $bookingDays[] = [
                            'day_of_week' => $dayOfWeek,
                            'opening_time' => $openingTime,
                            'closing_time' => $closingTime,
                        ];
                    }
                }

                // 🔍 Check for reservations outside new date range
                $outsideReservations = $record->reservations()
                    ->whereIn('status', [0, 1])
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('date', '<', $startDate)
                            ->orWhereDate('date', '>', $endDate);
                    })
                    ->exists();

                if ($outsideReservations) {
                    Notification::make()
                        ->title(__('panel.provider.cannot-update-service'))
                        ->body(__('panel.provider.reservations-outside-date-range'))
                        ->danger()
                        ->persistent()
                        ->send();

                    $this->halt();
                }

                // 🔍 Check for time and capacity conflicts
                $reservations = $record->reservations()
                    ->whereIn('status', [0, 1])
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->get();

                foreach ($reservations as $reservation) {
                    $resDate = Carbon::parse($reservation->date);
                    $resDay = $resDate->format('l');

                    if (!in_array($resDay, $validDays)) {
                        Notification::make()
                            ->title(__('panel.provider.day-conflict'))
                            ->body(__('panel.provider.reservation-day-conflict', [
                                'day' => __("panel.provider.$resDay"),
                                'date' => $reservation->date,
                            ]))
                            ->danger()
                            ->persistent()
                            ->send();

                        $this->halt();
                    }

                    $resStart = Carbon::createFromFormat('H:i:s', $reservation->start_time);
                    $bookingDay = collect($bookingDays)->firstWhere('day_of_week', $resDay);

                    if (!$bookingDay) {
                        continue;
                    }

                    $open = Carbon::parse($bookingDay['opening_time']);
                    $close = Carbon::parse($bookingDay['closing_time']);
                    $resEnd = $resStart->copy()->addMinutes($newDuration);

                    if ($resStart->lt($open) || $resEnd->gt($close)) {
                        Notification::make()
                            ->title(__('panel.provider.time-conflict'))
                            ->body(__('panel.provider.reservation-time-conflict', [
                                'time' => $reservation->start_time,
                                'date' => $reservation->date,
                            ]))
                            ->danger()
                            ->persistent()
                            ->send();

                        $this->halt();
                    }

                    $qty = $reservation->details()->sum('quantity');
                    if ($qty > $newCapacity) {
                        Notification::make()
                            ->title(__('panel.provider.capacity-conflict'))
                            ->body(__('panel.provider.reservation-capacity-conflict', [
                                'date' => $reservation->date,
                                'time' => $reservation->start_time,
                                'qty' => $qty,
                                'capacity' => $newCapacity,
                            ]))->danger()
                            ->persistent()
                            ->send();

                        $this->halt();
                    }
                }

                $this->serviceBookingData = [
                    'available_start_date' => $startDate,
                    'available_end_date' => $endDate,
                    'session_duration' => $newDuration,
                    'session_capacity' => $newCapacity,
                    'service_booking_days' => $bookingDays,
                ];

                break;
            }
        }

        unset($this->data['serviceBookings']);

        if (empty($this->serviceBookingData)) {
            return;
        }

        $record->serviceBookings()->delete();

        $booking = ServiceBooking::create([
            'service_id' => $record->id,
            'available_start_date' => $this->serviceBookingData['available_start_date'],
            'available_end_date' => $this->serviceBookingData['available_end_date'],
            'session_duration' => $this->serviceBookingData['session_duration'],
            'session_capacity' => $this->serviceBookingData['session_capacity'],
        ]);

        foreach ($this->serviceBookingData['service_booking_days'] as $day) {
            ServiceBookingDay::create([
                'service_booking_id' => $booking->id,
                'day_of_week' => $day['day_of_week'],
                'opening_time' => $day['opening_time'],
                'closing_time' => $day['closing_time'],
            ]);
        }
    }

    protected function afterSave(): void
    {
        $currentStatus = $this->record->status;

        // If service was active and is now inactive
        if ($currentStatus === false) {
            $this->record->reservations()
                ->where('status', '!=', 2) // Only update non-cancelled reservations
                ->update(['status' => 2]); // Set to cancelled
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
