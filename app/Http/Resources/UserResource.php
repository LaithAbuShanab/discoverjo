<?php

namespace App\Http\Resources;

use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
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
            return [
                'id' => $this->user->id,
                'slug'=>$this->user->slug,
                'username' => $this->user->username,
                'image' => $this->user->getFirstMediaUrl('avatar','avatar_app'),
                'points' => $this->user->getPoints(),
                'streak' => $this->user->getCurrentStreakCount($activity),
            ];
        } else {
            return [
                'id' => $this->id,
                'slug'=>$this->slug,
                'username' => $this->username,
                'email' => $this->email,
                'image' => $this->getFirstMediaUrl('avatar','avatar_app'),
                'points' => $this->getPoints(),
                'streak' => $this->getCurrentStreakCount($activity),
                'is_following' => Auth::guard('api')->check() && $this->id
                    ? (optional(Auth::guard('api')->user()
                        ->following()
                        ->where('users.id', $this->id)
                        ->first())->pivot->status ?? false)
                    : null,



            ];
        }
    }
}
