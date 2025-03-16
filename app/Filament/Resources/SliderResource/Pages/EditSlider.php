<?php

namespace App\Filament\Resources\SliderResource\Pages;

use App\Filament\Resources\SliderResource;
use App\Models\Slider;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditSlider extends EditRecord
{
    protected static string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

        ];
    }

    protected function beforeValidate(): array
    {
        // Access validated data from the form
        $data = $this->data;

        // Check if a Slider with the same type and priority already exists
        if (Slider::where('type', $data['type'] ?? null)
            ->where('priority', $data['priority'] ?? null)
            ->exists()) {

            // Show error notification using Filament's Notification system
            Notification::make()
                ->warning()
                ->title('Duplicate Type and Priority')
                ->body('The combination of priority and type must be unique.')
                ->send();

            // Throw a validation exception to halt the process
            throw ValidationException::withMessages([
                'priority' => 'The combination of priority and type must be unique.',
            ]);
        }

        // Return validated data
        return $data;
    }

}
