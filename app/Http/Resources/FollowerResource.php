<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class FollowerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fullName= $this->followerUser->first_name ." ". $this->followerUser->last_name;
        $authUser= $auth = Auth::guard('api')->user();
        $followed = $authUser->following()
            ->where('users.id', $this->followerUser->id)
            ->first();
        return [
            'id'=>$this->id,
            'follower_id'=>$this->followerUser->id,
            'follower_slug'=>$this->followerUser->slug,
            'follower_name'=>$this->followerUser->username,
            'full_name'=>$fullName,
            'follower_image' => $this->followerUser?->getMedia('avatar')->first()?->getUrl('avatar_app'),
            'status'=>$this->status,
            'is_follow'=>$followed ?$followed->pivot->status :false,
        ];
    }
}
