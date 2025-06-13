<?php

namespace App\Filament\Guide\Resources\GuideTripResource\Pages;

use App\Filament\Guide\Resources\GuideTripResource;
use App\Http\Requests\Api\User\GuideTrip\CreateGuideTripRequest;
use App\Models\GuideTrip;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use LevelUp\Experience\Models\Activity;


class CreateGuideTrip extends CreateRecord
{
    protected static string $resource = GuideTripResource::class;

    public function getTitle(): string
    {
        return __('panel.guide.create');
    }

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

            if ($this->data['is_trail']) {
                $trip->trail()->create([
                    'min_duration_in_minute' => $this->data['min_duration_in_minute'],
                    'max_duration_in_minute' => $this->data['max_duration_in_minute'],
                    'distance_in_meter' => $this->data['distance_in_meter'],
                    'difficulty' => $this->data['difficulty'],
                ]);
            }

            $user = User::find(auth()->id());
            $user->addPoints(10);
            $activity = Activity::find(1);

            $user->recordStreak($activity);

            return $trip;
        });
    }
}
