<?php

namespace App\Filament\provider\Resources\GuideTripResource\Pages;

use App\Filament\provider\Resources\GuideTripResource;
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


    protected function afterSave(): void
    {
        $currentStatus = $this->record->status;

        // If service was active and is now inactive
        if ($currentStatus === false) {
            $this->record->guideTripUsers()
                ->where('status', '!=', 2) // Only update non-cancelled reservations
                ->update(['status' => 2]); // Set to cancelled
        }
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);

    }
}
