<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use LevelUp\Experience\Models\Activity;

class AllGuideTripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activity= Activity::find(1);

        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            'name'=>$this->name,
            'price'=>$this->main_price,
            'start_time'=>$this->start_datetime,
            'max_attendance'=>$this->max_attendance,
            'image' => $this->getMedia('guide_trip_gallery')->first()?->getUrl('guide_trip_gallery_app'),
            'number_of_request' => $this->guideTripUsers
                ->filter(fn($userTrip) => $userTrip->user && $userTrip->user->status == 1)
                ->count(),
            'guide_username' => $this->guide->username,
            'guide_slug' => $this->guide->slug,
            'is_following' => isFollowing($this->guide->id),
            'guide_points' => $this->guide->getPoints(),
            'guide_streak' => $this->guide->getCurrentStreakCount($activity),
            'guide_rating' => $this->guide->guideRatings->avg('rating'),
            'guide_avatar' => $this->guide->getFirstMediaUrl('avatar', 'avatar_app'),
            'status'=>$this->status,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteGuideTrips->contains('id', $this->id) : false,

//            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteGuideTrips->contains('id', $this->id) : false,

        ];
    }
}
