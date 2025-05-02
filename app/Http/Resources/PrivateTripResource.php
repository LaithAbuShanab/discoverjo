<?php

namespace App\Http\Resources;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PrivateTripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $trip = Trip::findOrFail($this->id);
        $trip->load('usersTrip.user');

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'conversation_id' => $this->conversation->id ?? null,
            'image' => $this->place->getFirstMediaUrl('main_place','main_place_app'),
            'date' => Carbon::parse($this->date_time)->format('Y-m-d'),
            'name' => $this->name,
            'place_name' => $this->place->name,
            'place_slug' => $this->place->slug,
            'price' => $this->cost,
            'attendance_number' => $this->attendance_number,
            'location' => $this->place->region->name,
            'users_number' => UserTripResource::collection($this->usersTrip),
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteTrips->contains('id', $this->id) : false,
        ];
    }
}
