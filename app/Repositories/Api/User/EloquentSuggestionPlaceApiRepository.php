<?php

namespace App\Repositories\Api\User;


use App\Interfaces\Gateways\Api\User\SuggestionPlaceApiRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use App\Models\Admin;
use App\Notifications\Admin\NewContactNotification;
use App\Models\SuggestionPlace;
use App\Notifications\Admin\NewSuggestionNotification;
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
        Notification::send(Admin::all(), new NewSuggestionNotification($suggestionPlace));

        //Http::post('http://127.0.0.1:3000/notifications');
        return $suggestionPlace;
    }
}
