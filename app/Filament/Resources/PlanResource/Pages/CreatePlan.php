<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = PlanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_type'] = auth()->user()->getMorphClass();
        $data['creator_id'] = auth()->user()->id;

        return $data;
    }
}
