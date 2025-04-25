<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use LevelUp\Experience\Models\Activity;

class VolunteeringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        $activity= Activity::find(1);
        $startDatetime = dateTime($this->start_datetime);
        $endDateTime =  dateTime($this->end_datetime);
//
//        $interestedUsers = $this->interestedUsers->map(function ($interestedUser)  use ($activity){
//            return [
//                'id' => $interestedUser->id,
//                'slug'=>$interestedUser->slug,
//                'name' => $interestedUser->username,
//                'image' => $interestedUser->getFirstMediaUrl('avatar', 'avatar_app'),
//                'points' => $this->getPoints(),
//                'streak' => $this->getCurrentStreakCount($activity),
//            ];
//        });

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'start_day' => $startDatetime->format('Y-m-d'),
            'start_time' => $startDatetime->format('H:i:s'),
            'end_day' => $endDateTime->format('Y-m-d'),
            'end_time' => $endDateTime->format('H:i:s'),
            'image' => $this->getFirstMediaUrl('volunteering', 'volunteering_app'),
            'region' => $this->region->name,
            'address' => $this->address,
            'hours_worked' => $this->hours_worked,
            'status' => intval($this->status),
            'interested_users' => UserResource::collection($this->interestedUsers),
            'attendance_number' => $this->attendance_number,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteVolunteerings->contains('id', $this->id) : false,
            'interested' => Auth::guard('api')->user() ? Auth::guard('api')->user()->volunteeringInterestables->contains('id', $this->id) : false,
        ];
    }
}
