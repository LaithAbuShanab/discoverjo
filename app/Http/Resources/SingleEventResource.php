<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SingleEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $organizers = $this->organizers->map(function ($organizer) {
            return [
                'name' => $organizer->name,
                'image' => $organizer->getFirstMediaUrl('organizer', 'organizer_app'),
            ];
        });

        $filteredReviews = $this->reviews->filter(function ($review) {
            return $review->user->status == 1;
        });

        $filteredPosts = $this->posts->filter(function ($post) {
            return $post->user->status == 1;
        });

        $startDatetime = dateTime($this->start_datetime);
        $endDateTime =  dateTime($this->end_datetime);

        $total_ratings = 0;
        $total_user_total = 0;
        if ($this->reviews->count() > 0) {
            $total_ratings =  $filteredReviews->avg('rating');
            $total_user_total = $filteredReviews->count();
        }

        $authUser = Auth::guard('api')->user();
        $interestedUsers = $this->interestedUsers
            ->filter(fn($user) => $user->status == 1)
            ->reject(fn($user) => $authUser && ($authUser->hasBlocked($user) || $user->hasBlocked($authUser)));

        $data = [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->getFirstMediaUrl('event', 'event_app'),
            'start_day' => $startDatetime->format('Y-m-d'),
            'start_time' => $startDatetime->format('H:i:s'),
            'end_day' => $endDateTime->format('Y-m-d'),
            'end_time' => $endDateTime->format('H:i:s'),
            'region' => $this->region->name,
            'address' => $this->address,
            'status' => intval($this->status),
            'link' => $this->link,
            'price' => $this->price,
            'rating' => round($total_ratings, 2),
            'total_user_rating' => $total_user_total,
            'attendance_number' => $this->attendance_number,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteEvents->contains('id', $this->id) : false,
            'interested' => Auth::guard('api')->user() ? Auth::guard('api')->user()->eventInterestables->contains('id', $this->id) : false,
            'organizers' => $organizers,
            'interested_users' => UserResource::collection($interestedUsers),
        ];


        // if ($this->start_datetime < $now) {
        $data['reviews'] = ReviewResource::collection($filteredReviews);
        $data['posts'] = UserPostResource::collection($filteredPosts);
        // }
        return $data;
    }
}
