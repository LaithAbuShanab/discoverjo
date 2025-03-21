<?php

namespace App\Filament\Resources\ReviewableResource\Pages;

use App\Filament\Resources\ReviewableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReviewables extends ListRecords
{
    protected static string $resource = ReviewableResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
