<?php

namespace App\Filament\Guide\Resources\GuideTripResource\Pages;

use App\Filament\Guide\Resources\GuideTripResource;
use App\Http\Requests\Api\User\GuideTrip\CreateGuideTripRequest;
use App\Models\GuideTrip;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class CreateGuideTrip extends CreateRecord
{
    protected static string $resource = GuideTripResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guide_id'] = auth()->id();
        unset(
            $data['is_trail'],
            $data['min_duration_in_minute'],
            $data['max_duration_in_minute'],
            $data['distance_in_meter'],
            $data['difficulty']
        );
        return $data;
    }




    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Create the Trip first
            $trip = static::getModel()::create($data);
            $this->record = $trip;

            // Create the Trail only if `is_trail` is true
            if (!empty($data['is_trail'])) {
                $trip->trail()->create([
                    'min_duration_in_minute' => $data['min_duration_in_minute'],
                    'max_duration_in_minute' => $data['max_duration_in_minute'],
                    'distance_in_meter' => $data['distance_in_meter'],
                    'difficulty' => $data['difficulty'],
                ]);
            }

            return $trip;
        });
    }


}
