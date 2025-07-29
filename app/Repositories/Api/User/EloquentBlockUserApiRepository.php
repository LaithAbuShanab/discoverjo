<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\UserResource;
use App\Interfaces\Gateways\Api\User\BlockUserApiRepositoryInterface;
use App\Models\Follow;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Support\Facades\Auth;


class EloquentBlockUserApiRepository implements BlockUserApiRepositoryInterface
{
    public function listOfBlockedUsers(){
        $user = Auth::guard('api')->user();
        $blockedUsers = $user->blockedUsers;
        return UserResource::collection($blockedUsers);
    }

    public function block($slug)
    {
        $user = Auth::guard('api')->user();
        $blockedUser = User::findBySlug($slug);

        // Check if already blocked
        $alreadyBlocked = UserBlock::where('blocker_id', $user->id)
            ->where('blocked_id', $blockedUser->id)
            ->exists();

        if (! $alreadyBlocked) {
            UserBlock::create([
                'blocker_id' => $user->id,
                'blocked_id' => $blockedUser->id,
            ]);
        }
        Follow::where(function ($query) use ($blockedUser, $user) {
            $query->where('following_id', $blockedUser->id)
                ->where('follower_id', $user->id);
        })->orWhere(function ($query) use ($blockedUser, $user) {
            $query->where('follower_id', $blockedUser->id)
                ->where('following_id', $user->id);
        })->delete();
    }

    public function unblock($slug)
    {
        $user = Auth::guard('api')->user();
        $blockedUser = User::findBySlug($slug);

        UserBlock::where('blocker_id', $user->id)
            ->where('blocked_id', $blockedUser->id)
            ->delete();
    }
}
