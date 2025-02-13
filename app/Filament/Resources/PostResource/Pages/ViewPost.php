<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected function beforeFill(): void
    {
        $this->record->update(['seen_status' => 1]);
    }
    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
