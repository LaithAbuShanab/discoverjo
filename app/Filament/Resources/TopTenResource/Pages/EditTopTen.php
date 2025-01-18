<?php

namespace App\Filament\Resources\TopTenResource\Pages;

use App\Filament\Resources\TopTenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopTen extends EditRecord
{
    protected static string $resource = TopTenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
