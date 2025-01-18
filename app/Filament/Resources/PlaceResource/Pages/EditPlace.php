<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use App\Models\OpeningHour;
use App\Models\Place;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlace extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = PlaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function afterFill(): void
    {
        $openingHours = $this->record->openingHours;

        // Group days by opening and closing hours
        $groupedOpeningHours = $openingHours->groupBy(function ($openingHour) {
            return $openingHour->opening_time . '-' . $openingHour->closing_time;
        });

        // Transform grouped data to match the Repeater format
        $formattedOpeningHours = $groupedOpeningHours->map(function ($hoursGroup) {
            return [
                'day_of_week' => $hoursGroup->pluck('day_of_week')->toArray(), // Collect days in the array
                'opening_time' => $hoursGroup->first()->opening_time,
                'closing_time' => $hoursGroup->first()->closing_time,
            ];
        })->values()->toArray(); // Ensure to get an array of values

        $this->data['openingHours'] = $formattedOpeningHours;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['openingHours']);

        return $data;
    }

    public function beforeSave(): void
    {
        // Find the place by its record ID
        $place = Place::find($this->record->id);

        // Delete all existing opening hours
        $place->openingHours()->delete();

        // Iterate through each opening hour data and create a new record for each day
        foreach ($this->data['openingHours'] as $openingHours) {
            // Iterate over each day in the 'day_of_week' array
            foreach ($openingHours['day_of_week'] as $day) {
                $newDay = new OpeningHour();
                $newDay->place_id = $place->id;
                $newDay->day_of_week = $day; // Set each day
                $newDay->opening_time = $openingHours['opening_time'];
                $newDay->closing_time = $openingHours['closing_time'];
                $newDay->save();
            }
        }
    }
}
