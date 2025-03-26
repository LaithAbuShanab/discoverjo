<?php

namespace App\Repositories\Api\User;


use App\Interfaces\Gateways\Api\User\SuggestionPlaceApiRepositoryInterface;
use App\Models\SuggestionPlace;
use Illuminate\Support\Str;


class EloquentSuggestionPlaceApiRepository implements SuggestionPlaceApiRepositoryInterface
{
    public function createSuggestionPlace($data, $imageData)
    {
        $suggestionPlace = SuggestionPlace::create($data);
        if ($imageData !== null) {
            foreach ($imageData as $image) {
                $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                $suggestionPlace->addMedia($image)->usingFileName($filename)->toMediaCollection('suggestion_place');
            }
        }

        adminNotification(
            'New Suggestion Place',
            'There is a new suggestion place',
            ['action' => 'view_place', 'action_label' => 'View Place', 'action_url' => route('filament.admin.resources.suggestion-places.view', $suggestionPlace)]
        );

        return $suggestionPlace;
    }
}
