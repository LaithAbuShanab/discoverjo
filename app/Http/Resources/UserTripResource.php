<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LevelUp\Experience\Models\Activity;

class UserTripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activity = Activity::find(1);
        $fullName= $this->user->first_name ." ". $this->user->last_name;

        return [
            'id' => $this->user->id,
            'slug' => $this->user->slug,
            'username' => $this->user->username,
            'full_name'=>$fullName,
            'email' => $this->user->email,
            'status' => $this->status,
            'image' => $this->user->getFirstMediaUrl('avatar','avatar_app'),
            'is_following' =>isFollowing($this->user->id),

            // 'points' => $this->user->getPoints(),
            // 'streak' => $this->user->getCurrentStreakCount($activity),
        ];
    }
}
