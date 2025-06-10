<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\ServiceBooking;
use App\Models\ServiceBookingDay;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    public $serviceBookingData;
    protected static string $resource = ServiceResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
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

                // Store for use in afterCreate()
                $this->serviceBookingData = $serviceBookingData;

                // Only handle the first serviceBooking
                break;
            }
        }

        unset($this->data['serviceBookings']);

        $data['provider_type'] = \App\Models\Admin::class;
        $data['provider_id'] = auth()->user()->id;

        return $data;
    }

    public function afterCreate(): void
    {
        $days = $this->serviceBookingData['service_booking_days'];

        $serviceBooking = ServiceBooking::create([
            'service_id' => $this->record->id,
            'available_start_date' => $this->serviceBookingData['available_start_date'],
            'available_end_date' => $this->serviceBookingData['available_end_date'],
            'session_duration' => $this->serviceBookingData['session_duration'],
            'session_capacity' => $this->serviceBookingData['session_capacity'],
        ]);

        foreach ($days as $day) {
            ServiceBookingDay::create([
                'service_booking_id' => $serviceBooking->id,
                'day_of_week' => $day['day_of_week'],
                'opening_time' => $day['opening_time'],
                'closing_time' => $day['closing_time'],
            ]);
        }
    }
}
