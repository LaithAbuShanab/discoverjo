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
        $fullName= $this->guide->first_name ." ". $this->guide->last_name;
        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            'name'=>$this->name,
            'region'=>new RegionResource($this->region),
            'price'=>$this->main_price,
            'start_time'=>$this->start_datetime,
            'max_attendance'=>$this->max_attendance,
            'main_image'=> $this->getFirstMediaUrl('main_image','main_image_app'),
//            'image' => $this->getMedia('guide_trip_gallery')->first()?->getUrl(),
            'number_of_request' => $this->guideTripUsers
                ->filter(fn($userTrip) => $userTrip->user && $userTrip->user->status == 1)
                ->count(),
            'guide_username' => $this->guide->username,
            'full_name' => $fullName,
            'guide_slug' => $this->guide->slug,
            'is_following' => isFollowing($this->guide->id),
            'is_follow_me' => isFollower($this->guide->id),
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
