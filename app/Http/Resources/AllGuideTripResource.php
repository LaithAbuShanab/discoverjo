<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AllGuideTripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'price'=>$this->main_price,
            'start_time'=>$this->start_datetime,
            'max_attendance'=>$this->max_attendance,
            'image' => $this->getMedia('guide_trip_gallery')->first()?->original_url,
            'number_of_request'=>$this->guideTripUsers->count(),
            'guide_username' => $this->guide->username,
            'guide_rating' => $this->guide->guideRatings->avg('rating'),
            'guide_avatar' => $this->guide->getFirstMediaUrl('avatar', 'avatar_app'),
            'status'=>$this->status,
            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteGuideTrip->contains('id', $this->id) : false,

//            'favorite' => Auth::guard('api')->user() ? Auth::guard('api')->user()->favoriteGuideTrip->contains('id', $this->id) : false,

        ];
    }
}
