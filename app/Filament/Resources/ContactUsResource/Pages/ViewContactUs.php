<?php

namespace App\Filament\Resources\ContactUsResource\Pages;

use App\Filament\Resources\ContactUsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewContactUs extends ViewRecord
{
    protected static string $resource = ContactUsResource::class;

    protected function beforeFill(): void
    {
        $this->record->update(['status' => 1]);
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
