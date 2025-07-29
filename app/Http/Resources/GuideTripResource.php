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
        $countRequest = null;
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

        $paymentMethods = [];
        foreach ($this->paymentMethods as $singleMethod) {
            $paymentMethods[] = $singleMethod->method;
        }

        $gallery = [];
        foreach ($this->getMedia('guide_trip_gallery') as $image) {
            $gallery[] = [
                'id' => $image->id,
                'url' => $image->getUrl(),
            ];
        }
        $filteredReviews = $this->reviews->filter(function ($review) {
            return $review->user->status == 1;
        });
        $total_ratings = 0;
        $total_user_total= 0;
        if ( $this->reviews->count() > 0) {
            $total_ratings =  $filteredReviews->avg('rating');
            $total_user_total = $filteredReviews->count();
        }

        $joined = Auth::guard('api')->check()
            ? Auth::guard('api')->user()->guideTripUsers->contains('guide_trip_id', $this->id)
            : false;
        if(Auth::guard('api')->user()?->id == $this->guide_id)
        {
            $countRequest= $this->requestGuideTripUsers()->count();
        }
        $fullName= $this->guide->first_name ." ". $this->guide->last_name;

        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            "name"=>$this->name,
            "description"=>$this->description,
            'region'=>new RegionResource($this->region),
            'main_image'=> $this->getFirstMediaUrl('main_image','main_image_app'),
            "start_datetime"=>$this->start_datetime,
            "end_datetime"=>$this->end_datetime,
            "price"=>$this->main_price,
            "max_attendance"=>$this->max_attendance,
            "status"=>$this->status,
            'guide_id'=>$this->guide_id,
            'guide_slug'=>$this->guide->slug,
            "guide_username"=>$this->guide->username,
            'full_name'=>$fullName,
            "guide_phone_number"=>$this->guide->phone_number,
            'is_following' =>isFollowing($this->guide->id),
            'is_follow_me' => isFollower($this->guide->id),
            'guide_rating' => $this->guide->guideRatings->avg('rating'),
            'guide_avatar' => $this->guide->getFirstMediaUrl('avatar','avatar_app'),
            'is_creator' => Auth::guard('api')->check() && Auth::guard('api')->user()->id == $this->guide_id,
            'request_count'=>$countRequest,
            "activities"=>$activities,
            "assemblies"=>GuideTripAssemblyResource::collection($this->assemblies),
            "age_price"=>GuideTripPriceAgeResource::collection($this->priceAges),
            "payment_methods"=>$paymentMethods,
            "price_include"=>$priceIncludes,
            "requirements"=>$requirements,
            "trail"=> new GuideTripTrailResource($this->trail),
//            "join_request"=>GuideTripUserResource::collection($this->guideTripUsers),
            'gallery'=>$gallery,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteGuideTrips->contains('id', $this->id) : false,
            'rating' => round($total_ratings, 2),
            'total_user_rating' => $total_user_total,
//            'reviews' => ReviewResource::collection($filteredReviews),
            'reviews' => ReviewResource::collection(
                $filteredReviews->reject(function ($review) {
                    $currentUser = Auth::guard('api')->user();
                    if (!$currentUser) return false;
                    return $currentUser->blockedUsers->contains('id', $review->user_id) ||
                        $currentUser->blockers->contains('id', $review->user_id);
                })
            ),
            'is_joined' => $joined,



        ];
    }
}
