<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SingleVolunteeringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $now = Carbon::now('Asia/Riyadh');

        $organizers = $this->organizers->map(function ($organizer) {
            return [
                'name' => $organizer->name,
                'image' => $organizer->getFirstMediaUrl('organizer', 'organizer_app'),
            ];
        });

        $filteredPosts = $this->posts->filter(function ($post) {
            return $post->user->status == 1;
        });

        $filteredReviews = $this->reviews->filter(function ($review) {
            return $review->user->status == 1;
        });

        $startDatetime = dateTime($this->start_datetime);
        $endDateTime =  dateTime($this->end_datetime);

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->getFirstMediaUrl('volunteering', 'volunteering_app'),
            'start_day' => $startDatetime->format('Y-m-d'),
            'start_time' => $startDatetime->format('H:i:s'),
            'end_day' => $endDateTime->format('Y-m-d'),
            'end_time' => $endDateTime->format('H:i:s'),
            'region' => $this->region->name,
            'address' => $this->address,
            'status' => intval($this->status),
            'link' => $this->link,
            'hours_worked' => $this->hours_worked,
            'organizers' => $organizers,
            'interested_users' => UserResource::collection($this->interestedUsers),
            'attendance_number' => $this->attendance_number,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteVolunteering->contains('id', $this->id) : false,
            'interested' => Auth::guard('api')->user() ? Auth::guard('api')->user()->volunteeringInterestables->contains('id', $this->id) : false,
        ];

        if ($this->start_datetime < $now) {
            $data['reviews'] = ReviewResource::collection($filteredReviews);
            $data['posts'] = UserPostResource::collection($filteredPosts);
        }

        return $data;
    }
}