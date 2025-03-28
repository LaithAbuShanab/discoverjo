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
        return [
            'id' => $this->user->id,
            'slug' => $this->user->slug,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'status' => $this->status,
            'image' => $this->user->getFirstMediaUrl('avatar', 'avatar_app'),
            // 'points' => $this->user->getPoints(),
            // 'streak' => $this->user->getCurrentStreakCount($activity),
        ];
    }
}
