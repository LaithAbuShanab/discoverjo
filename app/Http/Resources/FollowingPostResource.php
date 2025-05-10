<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class FollowingPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images =[];
        foreach ( $this->getMedia('post') as $image){
            $images[]=$image->getUrl();
        }

        return [
            'content'=>$this->content,
            'visitable_type'=>explode("\\Models\\",$this->visitable_type)[1],
            'visitable_id'=>$this->visitable_id,
            'user'=>$this->user->username,
            'is_following' =>isFollowing($this->user->id),
            'user_image'=>$this->user->getMedia('avatar')->first()?->getUrl('avatar_app'),
            'images'=> $images,

        ];
    }
}
