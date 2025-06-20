<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GuideTripUpdateDetailResource extends JsonResource
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
            $activities[] = $activity->getTranslations('activity');
        }

        $priceIncludes = [];
        foreach ($this->priceIncludes as $priceInclude) {
            $priceIncludes[] = $priceInclude->getTranslations('include');
        }

        $requirements = [];
        foreach ($this->requirements as $requirement) {
            $requirements[] = $requirement->getTranslations('item');
        }

        $assemblies =[];
        foreach ($this->assemblies as $assembly) {
            $assemblies[] = [
                'place' => [
                    'en' => $assembly->getTranslation('place', 'en'),
                    'ar' => $assembly->getTranslation('place', 'ar'),
                ],
                'time' => $assembly->time, // Adjust this to match the actual property or method for getting time
            ];
        }
        $gallery = [];
        foreach ($this->getMedia('guide_trip_gallery') as $image) {
            $gallery[] = $image->getUrl();
        }
        $filteredReviews = $this->reviews->filter(function ($review) {
            return $review->user->status == 1;
        });

        $joined = Auth::guard('api')->check()
            ? Auth::guard('api')->user()->guideTripUsers->contains('guide_trip_id', $this->id)
            : false;
        if(Auth::guard('api')->user()->id == $this->guide_id)
        {
            $countRequest= $this->requestGuideTripUsers()->count();
        }
        $fullName= $this->guide->first_name ." ". $this->guide->last_name;

        $paymentMethods = [];
        foreach ($this->paymentMethods as $singleMethod) {
            $paymentMethods[] = $singleMethod->getTranslations('method');
        }
        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            "name"=>$this->getTranslations('name'),
            "description"=>$this->getTranslations('description'),
            'region'=>new RegionResource($this->region),
            "start_datetime"=>$this->start_datetime,
            'main_image'=> $this->getFirstMediaUrl('main_image','main_image_app'),
            "end_datetime"=>$this->end_datetime,
            "price"=>$this->main_price,
            "max_attendance"=>$this->max_attendance,
            "status"=>$this->status,
            'guide_id'=>$this->guide_id,
            'guide_slug'=>$this->guide->slug,
            "guide_username"=>$this->guide->username,
            'full_name'=>$fullName,
            "guide_phone_number"=>$this->guide->phone_number,
            'guide_rating' => $this->guide->guideRatings->avg('rating'),
            'guide_avatar' => $this->guide->getFirstMediaUrl('avatar','avatar_app'),
            'is_creator' => Auth::guard('api')->check() && Auth::guard('api')->user()->id == $this->guide_id,
            'request_count'=>$countRequest,
            "activities"=>$activities,
            "assemblies"=>$assemblies,
            "age_price"=>GuideTripPriceAgeResource::collection($this->priceAges),
            "price_include"=>$priceIncludes,
            "requirements"=>$requirements,
            "payment_methods"=>$paymentMethods,
            "trail"=> new GuideTripTrailUpdateResource($this->trail),
//            "join_request"=>GuideTripUserResource::collection($this->guideTripUsers),
            'gallery'=>$gallery,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteGuideTrips->contains('id', $this->id) : false,
            'reviews' => ReviewResource::collection($filteredReviews),
            'is_joined' => $joined,



        ];
    }

}
