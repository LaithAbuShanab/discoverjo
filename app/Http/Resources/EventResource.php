<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startDatetime = dateTime($this->start_datetime);
        $endDateTime =  dateTime($this->end_datetime);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'start_day' => $startDatetime->format('Y-m-d'),
            'start_time' => $startDatetime->format('H:i:s'),
            'end_day' => $endDateTime->format('Y-m-d'),
            'end_time' => $endDateTime->format('H:i:s'),
            'image' => $this->getFirstMediaUrl('event','event_app'),
            'region' => $this->region->name,
            'address' => $this->address,
            'price' => $this->price,
            'status' => intval($this->status),
            'attendance_number' => $this->attendance_number,
            'interested_users' => UserResource::collection($this->interestedUsers),
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteEvents->contains('id', $this->id) : false,
            'interested' => Auth::guard('api')->user() ? Auth::guard('api')->user()->eventInterestables->contains('id', $this->id) : false,
        ];
    }
}
