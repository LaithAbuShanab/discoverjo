<?php

namespace App\Filament\Resources\RepliesResource\Pages;

use App\Filament\Resources\RepliesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReplies extends ListRecords
{
    protected static string $resource = RepliesResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
