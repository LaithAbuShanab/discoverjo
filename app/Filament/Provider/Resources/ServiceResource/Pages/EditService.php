<?php

namespace App\Filament\Provider\Resources\ServiceResource\Pages;

use App\Filament\Provider\Resources\ServiceResource;
use App\Models\ServiceBooking;
use App\Models\ServiceBookingDay;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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

    public function beforeSave(): void
    {
        if (!empty($this->data['serviceBookings'])) {
            foreach ($this->data['serviceBookings'] as $serviceBooking) {
                $serviceBookingData['available_start_date'] = $serviceBooking['available_start_date'];
                $serviceBookingData['available_end_date'] = $serviceBooking['available_end_date'];
                $serviceBookingData['session_duration'] = $serviceBooking['session_duration'];
                $serviceBookingData['session_capacity'] = $serviceBooking['session_capacity'];

                $serviceBookingData['service_booking_days'] = [];

                foreach ($serviceBooking['serviceBookingDays'] as $serviceBookingDay) {
                    foreach ($serviceBookingDay['day_of_week'] as $value) {
                        $serviceBookingData['service_booking_days'][] = [
                            'day_of_week' => $value,
                            'opening_time' => $serviceBookingDay['opening_time'],
                            'closing_time' => $serviceBookingDay['closing_time'],
                        ];
                    }
                }

                $this->serviceBookingData = $serviceBookingData;

                break;
            }
        }

        unset($this->data['serviceBookings']);

        if (!$this->serviceBookingData) {
            return;
        }

        $this->record->serviceBookings()->delete();

        $booking = ServiceBooking::create([
            'service_id' => $this->record->id,
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
