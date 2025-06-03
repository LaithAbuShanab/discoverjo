<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class FollowingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = Auth::guard('api')->user();
        $followingUser = $this->followingUser;

        // Safely handle missing or inactive user
        if (!$followingUser || $followingUser->status !== 1) {
            return [];
        }

        // Default is_follow to false
        $isFollow = false;

        if ($authUser) {
            $followed = $authUser->following()
                ->where('users.id', $followingUser->id)
                ->first();

            if ($followed) {
                $isFollow = $followed->pivot->status;
            }
        }

        $fullName = trim("{$followingUser->first_name} {$followingUser->last_name}");

        return [
            'id' => $this->id,
            'following_id' => $followingUser->id,
            'following_slug' => $followingUser->slug,
            'following_name' => $followingUser->username,
            'full_name' => $fullName,
            'following_image' => $followingUser->getMedia('avatar')->first()?->getUrl('avatar_app'),
            'status' => $this->status,
            'is_follow' => $isFollow,
        ];
    }

}
