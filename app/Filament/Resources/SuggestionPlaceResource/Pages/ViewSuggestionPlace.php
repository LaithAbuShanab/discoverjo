<?php

namespace App\Filament\Resources\SuggestionPlaceResource\Pages;

use App\Filament\Resources\SuggestionPlaceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSuggestionPlace extends ViewRecord
{
    protected static string $resource = SuggestionPlaceResource::class;

    protected function beforeFill(): void
    {
        $this->record->update(['status' => 1]);
    }
    protected function getHeaderActions(): array
    {
        return [
//            Actions\EditAction::make(),
        ];
    }
}
