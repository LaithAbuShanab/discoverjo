<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['provider_type'] = \App\Models\Admin::class;
        $data['provider_id'] = auth()->user()->id ?? null;
        return $data;
    }

}
