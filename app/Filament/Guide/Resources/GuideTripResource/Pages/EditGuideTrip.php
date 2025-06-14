<?php

namespace App\Filament\Guide\Resources\GuideTripResource\Pages;

use App\Filament\Guide\Resources\GuideTripResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditGuideTrip extends EditRecord
{
    protected static string $resource = GuideTripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('panel.guide.edit');
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
            $record->update($data);

            if ($this->data['is_trail']) {
                $data = Arr::only($this->data ?? [], [
                    'min_duration_in_minute',
                    'max_duration_in_minute',
                    'distance_in_meter',
                    'difficulty',
                ]);

                if (filled($data)) {
                    $record->trail()->updateOrCreate([], $data);
                } else {
                    $record->trail?->delete();
                }
            } else {
                $record->trail?->delete();
            }

            return $record;
        });
    }

    protected function afterFill(): void
    {
        $this->data['is_trail'] = $this->record->trail ? true : false;
    }
}
