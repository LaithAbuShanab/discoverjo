<?php

namespace App\Filament\Provider\Resources\ServiceResource\Pages;

use App\Filament\Provider\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;
}
