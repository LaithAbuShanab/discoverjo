<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fullName= $this->first_name ." ". $this->last_name;
        return[
            'id'=>$this->id,
            'slug'=>$this->slug,
            "username"=>$this->username,
            'full_name'=>$fullName,
            "phone_number"=>$this->phone_number,
            'is_following' =>isFollowing($this->id),
            'is_follow_me' => isFollower($this->id),
//            'guide_rating' => $this->guide->guideRatings->avg('rating'),
            'avatar' => $this->getFirstMediaUrl('avatar','avatar_app'),
        ];
    }
}
