<?php

namespace App\Filament\Resources\TopTenResource\Pages;

use App\Filament\Resources\TopTenResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTopTen extends CreateRecord
{
    protected static string $resource = TopTenResource::class;

    public static function canCreateAnother() : bool
    {
        return false;
    }
}
