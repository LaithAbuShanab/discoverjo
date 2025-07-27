<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUser = Auth::guard('api')->user();
        $start = dateTime($this->start_datetime);
        $end = dateTime($this->end_datetime);

        $interestedUsers = $this->interestedUsers
            ->filter(fn($user) => $user->status == 1)
            ->reject(fn($user) => $authUser && ($authUser->hasBlocked($user) || $user->hasBlocked($authUser)));

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'start_day' => $start->format('Y-m-d'),
            'start_time' => $start->format('H:i:s'),
            'end_day' => $end->format('Y-m-d'),
            'end_time' => $end->format('H:i:s'),
            'image' => $this->getFirstMediaUrl('event', 'event_app'),
            'region' => $this->region->name,
            'address' => $this->address,
            'price' => $this->price,
            'status' => (int) $this->status,
            'attendance_number' => $this->attendance_number,
            'interested_users' => UserResource::collection($interestedUsers),
            'favorite' => $authUser?->favoriteEvents->contains('id', $this->id) ?? false,
            'interested' => $authUser?->eventInterestables->contains('id', $this->id) ?? false,
        ];
    }
}
