<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use LevelUp\Experience\Models\Activity;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {

        $activity= Activity::find(1);
        if ($this->resource->getTable() == 'group_members') {
            $fullName= $this->user->first_name ." ". $this->user->last_name;

            return [
                'id' => $this->user->id,
                'slug'=>$this->user->slug,
                'username' => $this->user->username,
                'full_name'=>$fullName,
                'image' => $this->user->getFirstMediaUrl('avatar','avatar_app'),
                'points' => $this->user->getPoints(),
                'streak' => $this->user->getCurrentStreakCount($activity),
                'is_following' =>isFollowing($this->user->id),
            ];
        } else {
            $fullName= $this->first_name ." ". $this->last_name;
            return [
                'id' => $this->id,
                'slug'=>$this->slug,
                'username' => $this->username,
                'full_name'=>$fullName,
                'email' => $this->email,
                'image' => $this->getFirstMediaUrl('avatar','avatar_app'),
                'points' => $this->getPoints(),
                'streak' => $this->getCurrentStreakCount($activity),
                'is_following' =>isFollowing($this->id),

            ];
        }
    }
}
