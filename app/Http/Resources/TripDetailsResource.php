<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TripDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        $now = Carbon::now('Asia/Riyadh');

        $filteredPosts = $this->posts->filter(function ($post) {
            return $post->user->status == 1;
        });

        $filteredReviews = $this->reviews->filter(function ($review) {
            return $review->user->status == 1;
        });

        $gallery =  array_merge(
            [$this->place->getFirstMediaUrl('main_place', 'main_place_app')],
            $this->place->getMedia('place_gallery')->map(fn($image) => $image->getUrl('place_gallery_app'))->all()
        );

        $tripType = match ($this->trip_type) {
            1 => __('app.trip_type_1'),
            2 => __('app.trip_type_2'),
            default => __('app.trip_type_0'),
        };

        $data = [
            'id' => $this->id,
            'slug' => $this->slug,
            'selected_type' => [
                'id'   => $this->trip_type,
                'name' => $tripType
            ],
            'creator' => new UserResource($this->user),
            'is_creator' => Auth::guard('api')->user()->id == $this->user_id,
            'is_following' => isFollowing($this->user_id),
            'name' => $this->name,
            'address' => $this->place->address,
            'region' => $this->place->region->name,
            'place_gallery' => $gallery,

            'description' => $this->description,
            'tags' => $this->tags->map(function ($tag) {
                return [
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'image_active' => $tag->getFirstMediaUrl('tag_active', 'tag_active_app'),
                    'image_inactive' => $tag->getFirstMediaUrl('tag_inactive', 'tag_inactive_app'),
                ];
            }),
            'place_name' => $this->place->name,
            'place_slug' => $this->place->slug,
            'cost' => $this->cost,
            'age_min' => optional(json_decode($this->age_range))->min,
            'age_max' => optional(json_decode($this->age_range))->max,
            'gender' => $this->gender(),
            'date' => Carbon::parse($this->date_time)->format('Y-m-d'),
            'time' => Carbon::parse($this->date_time)->format('H:i:s'),
            'attendance_number' => $this->attendance_number,
            'attendances' => UserResource::collection($this->usersTrip->where('status', '1')->pluck('user')),
            'users_request' => UserTripResource::collection($this->usersTrip()->where('status', 0)->get()),
            'status' => $this->status,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteTrips->contains('id', $this->id) : false,
        ];

        if ($this->date_time < $now) {
            $data['posts'] = UserPostResource::collection($filteredPosts);
            $data['reviews'] = ReviewResource::collection($filteredReviews);
        }

        if ($this->trip_type != 2) {
            $data['request_count'] = $this->usersTrip->where('status', '0')->count();
        }

        return $data;
    }
}
