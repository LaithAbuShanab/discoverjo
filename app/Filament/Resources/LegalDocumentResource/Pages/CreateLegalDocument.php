<?php

namespace App\Filament\Resources\LegalDocumentResource\Pages;

use App\Filament\Resources\LegalDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalDocument extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = LegalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
