<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LevelUp\Experience\Models\Activity;

class OtherUserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $paginationPerPage = config('app.pagination_per_page');

        $tags = $this->tags->map(function ($tag) {
            return [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'image_active' => $tag->getFirstMediaUrl('tag_active', 'tag_active_app'),
                'image_inactive' => $tag->getFirstMediaUrl('tag_inactive', 'tag_inactive_app'),
            ];
        });

        $gender = [
            'ar' => [
                1 => 'ذكر',
                2 => 'انثى'
            ],
            'en' => [1 => 'Male', 2 => 'Female']
        ];

        $reviews = $this->reviews()->paginate($paginationPerPage);
        $activity = Activity::find(1);
        $fullName = $this->first_name . " " . $this->last_name;

        $isGuide = 0;
        if ($this->userTypes()->where('type', 2)->exists()) {
            $isGuide = 1;
        }

        $currentUser = auth('api')->user();
        $hasBlocked = $currentUser && $currentUser->hasBlocked($this->resource);

        return [
            'id'               => $this->id,
            'slug'             => $this->slug,
            'first_name'       => $this->first_name,
            'last_name'        => $this->last_name,
            'username'         => $this->username,
            'full_name'        => $fullName,
            'is_guide'         => $isGuide,
            'referral_code'    => $this->referral_code,
            'guide_rating'     => $hasBlocked ? 0 : ($isGuide ? $this->guideRatings->avg('rating') : false),
            'gender'           => $gender[$this->lang][$this->sex],
            'points'           =>  $hasBlocked ? 0 : $this->getPoints(),
            'streak'           =>  $hasBlocked ? 0 : $this->getCurrentStreakCount($activity),
            'status'           => $this->status,
            'description'      => $this->description,
            'following_number' =>  $hasBlocked ? 0 : $this->acceptedFollowingCountExcludingBlocked(),
            'follower_number'  =>  $hasBlocked ? 0 : $this->acceptedFollowersCountExcludingBlocked(),
            'is_following'     => isFollowing($this->id),
            'is_follow_me'     => isFollower($this->id),
            'tags'             => $tags,
            'reviews'          =>  $hasBlocked ? [] : ReviewResource::collection($reviews),
            'visited_places'   =>  $hasBlocked ? [] : UserVisitedPlaceResource::collection($this->visitedPlace),
            'avatar'           => $this->getFirstMediaUrl('avatar', 'avatar_app'),
            'has_blocked'      => $hasBlocked
        ];
    }
}
