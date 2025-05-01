<?php

namespace App\Http\Resources;

use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activities = $this->days->flatMap->activities;

        // Get all unique place IDs from activities
        $placeIds = $activities->pluck('place_id')->unique()->values();

        // Get the first place
        $place = Place::find($placeIds->first());

        // Determine creator type
        $creator = $this->creator_type == "App\Models\Admin" ? "admin" : "user";

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'creator' => $creator,
            'description' => $this->description,
            'number_of_days' => $this->days->count(),
            'number_of_activities' => $activities->count(),
            'number_of_places' => $placeIds->count(),
            'image' => $place?->getFirstMediaUrl('main_place'),
            'favorite' => Auth::guard('api')->user()
                ? Auth::guard('api')->user()->favoritePlans->contains($this->id)
                : false,
        ];
    }
}
