<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use LevelUp\Experience\Models\Activity;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isGuide = 0;
        if ($this->userTypes()->where('type', 2)->exists()) {
            $isGuide = 1;
        }

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


        $activity = Activity::find(1);

        // Check If User Have Streak
        $streak = DB::table('streaks')
            ->where('user_id', $this->id)
            ->where('activity_id', $activity->id)
            ->exists();

        $reviews = $this->reviews()->orderBy('created_at', 'desc')->paginate($paginationPerPage);
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'is_following' => isFollowing($this->id),
            'is_follow_me' => isFollower($this->id),
            'username' => $this->username,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'lang' => $this->lang,
            'gender' => $gender[$this->lang][$this->sex],
            'referral_code' => $this->referral_code,
            'birth_of_day' => $this->birthday,
            'points' => $this->getPoints(),
            'streak' =>  $streak ? $this->getCurrentStreakCount($activity) : 0,
            'has_streak_today' => $streak ? $this->hasStreakToday($activity) : false,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'address' => $this->address,
            'is_guide' => $isGuide,
            'guide_rating' => $isGuide ? $this->guideRatings->avg('rating') : false,
            'status' => $this->status,
            'description' => $this->description,
            'following_number' => $this->acceptedFollowing()->wherePivot('status', 1)->count(),
            'follower_number' => $this->acceptedFollowers()->wherePivot('status', 1)->count(),
            'request_following_count' =>  $this->requestFollowers()->count(),
            'tags' => $tags,
            'reviews' => ReviewResource::collection($reviews),
            'visited_places' => UserVisitedPlaceResource::collection($this->visitedPlace),
            'avatar' => $this->getFirstMediaUrl('avatar', 'avatar_app'),

        ];
    }
}
