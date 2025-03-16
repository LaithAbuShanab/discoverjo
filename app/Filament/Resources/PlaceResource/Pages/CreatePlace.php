<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use App\Models\OpeningHour;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlace extends CreateRecord
{

    protected static string $resource = PlaceResource::class;

    protected function getActions(): array
    {
        return [

        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove `openingHours` to avoid saving it to the database
        unset($data['openingHours']);

        return $data;
    }

    public function afterCreate(): void
    {
        foreach ($this->data['openingHours'] as $openingHours) {
            foreach ($openingHours['day_of_week'] as $day) {
                $newDay = new OpeningHour();
                $newDay->place_id = $this->record->id;
                $newDay->day_of_week = $day;
                $newDay->opening_time = $openingHours['opening_time'];
                $newDay->closing_time = $openingHours['closing_time'];
                $newDay->save();
            }
        }
    }
}
