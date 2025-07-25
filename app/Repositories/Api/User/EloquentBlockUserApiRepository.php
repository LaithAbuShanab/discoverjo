<?php

namespace App\Repositories\Api\User;


use App\Interfaces\Gateways\Api\User\BlockUserApiRepositoryInterface;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Support\Facades\Auth;


class EloquentBlockUserApiRepository implements BlockUserApiRepositoryInterface
{
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
