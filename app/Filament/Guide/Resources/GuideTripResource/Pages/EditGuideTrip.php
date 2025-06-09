<?php

namespace App\Filament\Guide\Resources\GuideTripResource\Pages;

use App\Filament\Guide\Resources\GuideTripResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuideTrip extends EditRecord
{
    protected static string $resource = GuideTripResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset(
            $data['is_trail'],
            $data['min_duration_in_minute'],
            $data['max_duration_in_minute'],
            $data['distance_in_meter'],
            $data['difficulty']
        );

        return $data;
    }
    protected function afterSave(): void
    {

        $trip = $this->record;
        $state = $this->form->getState();

        // Translations
        $trip->setTranslations('name', $state['name']);
        $trip->setTranslations('description', $state['description']);
        $trip->save();

        // Activities
        $trip->activities()->delete();
        if (!empty($state['activities'])) {
            $trip->activities()->createMany($state['activities']);
        }

        // Assemblies
        $trip->assemblies()->delete();
        if (!empty($state['assemblies'])) {
            $trip->assemblies()->createMany($state['assemblies']);
        }

        // Price Includes
        $trip->priceIncludes()->delete();
        if (!empty($state['priceIncludes'])) {
            $trip->priceIncludes()->createMany($state['priceIncludes']);
        }

        // Price Ages
        $trip->priceAges()->delete();
        if (!empty($state['priceAges'])) {
            $trip->priceAges()->createMany(array_map(fn ($item) => [
                'min_age' => $item['min_age'],
                'max_age' => $item['max_age'],
                'price' => $item['price'],
            ], $state['priceAges']));
        }

        // Requirements
        $trip->requirements()->delete();
        if (!empty($state['requirements'])) {
            $trip->requirements()->createMany($state['requirements']);
        }

        // Payment Methods
        $trip->paymentMethods()->delete();
        if (!empty($state['payment_method'])) {
            $trip->paymentMethods()->createMany($state['payment_method']);
        }

        // Trail
        if (isset($this->data['is_trail']) && $this->data['is_trail']) {
            $trip->trail()->updateOrCreate([], [
                'min_duration_in_minute' => $this->data['min_duration_in_minute'],
                'max_duration_in_minute' => $this->data['max_duration_in_minute'],
                'distance_in_meter' => $this->data['distance_in_meter'],
                'difficulty' => $this->data['difficulty'],
            ]);
        } else {
            $trip->trail()->delete();
        }

    }
}
