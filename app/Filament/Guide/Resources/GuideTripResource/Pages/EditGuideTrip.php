<?php

namespace App\Filament\Guide\Resources\GuideTripResource\Pages;

use App\Filament\Guide\Resources\GuideTripResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EditGuideTrip extends EditRecord
{
    protected static string $resource = GuideTripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset(
            $data['is_trail'],
            $data['min_duration_in_minute'],
            $data['max_duration_in_minute'],
            $data['distance_in_meter'],
            $data['difficulty']
        );

        return $data;
    }
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // Update the Trip
            $record->update($data);

            // Update or delete the Trail
            if (!empty($data['is_trail'])) {
                $record->trail()->updateOrCreate([], [
                    'min_duration_in_minute' => $data['min_duration_in_minute'],
                    'max_duration_in_minute' => $data['max_duration_in_minute'],
                    'distance_in_meter' => $data['distance_in_meter'],
                    'difficulty' => $data['difficulty'],
                ]);
            } else {
                $record->trail()->delete();
            }

            return $record;
        });
    }
}
