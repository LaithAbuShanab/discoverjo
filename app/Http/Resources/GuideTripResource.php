<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GuideTripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activities = [];
        foreach ($this->activities as $activity) {
            $activities[] = $activity->activity;
        }

        $priceIncludes = [];
        foreach ($this->priceIncludes as $priceInclude) {
            $priceIncludes[] = $priceInclude->include;
        }

        $requirements = [];
        foreach ($this->requirements as $requirement) {
            $requirements[] = $requirement->item;
        }

        $gallery = [];
        foreach ($this->getMedia('guide_trip_gallery') as $image) {
            $gallery[] = $image->original_url;
        }
        $filteredReviews = $this->reviews->filter(function ($review) {
            return $review->user->status == 1;
        });

        $joined = Auth::guard('api')->check()
            ? Auth::guard('api')->user()->guideTripUsers->contains('guide_trip_id', $this->id)
            : false;
        return [
            'id'=>$this->id,
            "name"=>$this->name,
            "description"=>$this->description,
            "start_datetime"=>$this->start_datetime,
            "end_datetime"=>$this->end_datetime,
            "price"=>$this->main_price,
            "max_attendance"=>$this->max_attendance,
            "status"=>$this->status,
            'guide_id'=>$this->guide_id,
            "guide_username"=>$this->guide->username,
            "guide_phone_number"=>$this->guide->phone_number,
            'guide_rating' => $this->guide->guideRatings->avg('rating'),
            'guide_avatar' => $this->guide->getFirstMediaUrl('avatar', 'avatar_app'),
            'is_creator' => Auth::guard('api')->check() && Auth::guard('api')->user()->id == $this->guide_id,
            "activities"=>$activities,
            "assemblies"=>GuideTripAssemblyResource::collection($this->assemblies),
            "age_price"=>GuideTripPriceAgeResource::collection($this->priceAges),
            "price_include"=>$priceIncludes,
            "requirements"=>$requirements,
            "trail"=> new GuideTripTrailResource($this->trail),
//            "join_request"=>GuideTripUserResource::collection($this->guideTripUsers),
            'gallery'=>$gallery,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteGuideTrip->contains('id', $this->id) : false,
            'reviews' => ReviewResource::collection($filteredReviews),
            'is_joined' => $joined,



        ];
    }
}
