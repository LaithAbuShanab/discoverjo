<?php

namespace App\Filament\Guide\Resources\GuideTripResource\Pages;

use App\Filament\Guide\Resources\GuideTripResource;
use App\Http\Requests\Api\User\GuideTrip\CreateGuideTripRequest;
use App\Models\GuideTrip;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateGuideTrip extends CreateRecord
{
    protected static string $resource = GuideTripResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guide_id'] = auth()->id();
        unset(
            $data['is_trail'],
            $data['min_duration_in_minute'],
            $data['max_duration_in_minute'],
            $data['distance_in_meter'],
            $data['difficulty']
        );
        return $data;
    }

    protected function afterCreate(): void
    {
        $trip = $this->record;
        $state = $this->form->getState();

        // Set translations (name, description)
        $trip->setTranslations('name', $state['name']);
        $trip->setTranslations('description', $state['description']);
        $trip->save();

        // Activities
        if (!empty($state['activities'])) {
            $trip->activities()->createMany($state['activities']);
        }

        // Assemblies
        if (!empty($state['assemblies'])) {
            $trip->assemblies()->createMany($state['assemblies']);
        }

        // Price Includes
        if (!empty($state['priceIncludes'])) {
            $trip->priceIncludes()->createMany($state['priceIncludes']);
        }

        // Price Ages
        if (!empty($state['priceAges'])) {
            $trip->priceAges()->createMany(array_map(fn ($item) => [
                'min_age' => $item['min_age'],
                'max_age' => $item['max_age'],
                'price' => $item['price'],
            ], $state['priceAges']));
        }

        // Requirements
        if (!empty($state['requirements'])) {
            $trip->requirements()->createMany($state['requirements']);
        }

        // Payment Methods
        if (!empty($state['payment_method'])) {
            $trip->paymentMethods()->createMany($state['payment_method']);
        }

        // Trail (only if is_trail is true)
        if (isset($this->data['is_trail'])&& $this->data['is_trail']) {
            $trip->trail()->create([
                'min_duration_in_minute' => $this->data['min_duration_in_minute'],
                'max_duration_in_minute' => $this->data['max_duration_in_minute'],
                'distance_in_meter' => $this->data['distance_in_meter'],
                'difficulty' => $this->data['difficulty'],
            ]);
        }

        // Media uploads handled automatically via SpatieMediaLibraryFileUpload
    }

}
