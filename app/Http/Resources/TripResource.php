<?php

namespace App\Http\Resources;

use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TripResource extends JsonResource
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
            'slug'=>$this->slug,
            'creator_id'=>$this->user_id,
            'user' => new UserResource($this->user),
            'is_following' =>isFollowing($this->user_id),
            'creator_slug'=>$this->user->slug,
            'conversation_id' => $this->conversation->id ?? null,
            'image' => $this->place->getFirstMediaUrl('main_place','main_place_app'),
            'date' => Carbon::parse($this->date_time)->format('Y-m-d'),
            'name' => $this->name,
            'place_name' => $this->place->name,
            'place_slug' => $this->place->slug,
            'price' => $this->cost,
            'attendance_number' => $this->attendance_number,
            'location' => $this->place->region->name,
            'status'=>$this->status,
            'users_number' => UserResource::collection(
                $this->usersTrip
                    ->where('status', '1')
                    ->filter(fn($userTrip) => $userTrip->user && $userTrip->user->status == 1)
                    ->map->user
            ),

            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteTrips->contains('id', $this->id) : false,
        ];
    }
}
