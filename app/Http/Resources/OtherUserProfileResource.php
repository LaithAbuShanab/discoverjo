<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'is_guide' => $this->is_guide,
            'guide_rating' => $this->is_guide ? $this->guideRatings->avg('rating') : false,
            'gender' => $gender[$this->lang][$this->sex],
            'points' => $this->getPoints(),
            'streak' => $this->getCurrentStreakCount($activity),
            'status' => $this->status,
            'description' => $this->description,
            'following_number' => $this->acceptedFollowing()->count(),
            'follower_number' => $this->acceptedFollowers()->count(),
            'is_following' => Auth::guard('api')->check() && $this->id
                ? (optional(Auth::guard('api')->user()
                    ->following()
                    ->where('users.id', $this->id)
                    ->first())->pivot->status ?? false)
                : null,
            'tags' => $tags,
            'reviews' =>  ReviewResource::collection($reviews),
            'visited_places' => UserVisitedPlaceResource::collection($this->visitedPlace),
            'avatar' => $this->getFirstMediaUrl('avatar','avatar_app'),
        ];
    }
}
