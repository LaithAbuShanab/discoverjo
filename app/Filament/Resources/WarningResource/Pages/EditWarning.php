<?php

namespace App\Filament\Resources\WarningResource\Pages;

use App\Filament\Resources\WarningResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarning extends EditRecord
{
    protected static string $resource = WarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->status == 1) {
            $this->record->user_id = $this->record->reported_id;
            handleWarning($this->record);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getFormActions(): array
    {
        if ($this->record->status == 1) {
            return [
                $this->getCancelFormAction(),
            ];
        } else {
            return [
                $this->getCancelFormAction(),
                $this->getSaveFormAction(),
            ];
        }
    }
}
