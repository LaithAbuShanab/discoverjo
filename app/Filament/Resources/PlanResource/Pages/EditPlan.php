<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditPlan extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function beforeValidate(): void
    {
        $data = $this->data;

        if (!isset($data['days']) || !is_array($data['days'])) {
            return;
        }

        foreach ($data['days'] as $dayKey => &$day) {
            if (!isset($day['activities']) || !is_array($day['activities']) || empty($day['activities'])) {
                continue;
            }

            uasort($day['activities'], fn($a, $b) => strtotime($a['start_time']) <=> strtotime($b['start_time']));

            $previousEndTime = null;

            foreach ($day['activities'] as $activityKey => $activity) {
                if ($previousEndTime !== null && strtotime($activity['start_time']) <= strtotime($previousEndTime)) {
                    throw ValidationException::withMessages([
                        "data.days.{$dayKey}.activities.{$activityKey}.start_time" =>
                        "Activity must start after " . $previousEndTime,
                    ]);
                }
                $previousEndTime = $activity['end_time'];
            }
        }
    }
}
