<?php

namespace App\Repositories\Api\User;


use App\Interfaces\Gateways\Api\User\BlockUserApiRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class EloquentBlockUserApiRepository implements BlockUserApiRepositoryInterface
{
    public function block($slug)
    {
        $user = Auth::guard('api')->user();
        $blockedUser = User::findBySlug($slug);
        $user->blockedUsers()->attach($blockedUser->id);
        return true;
    }

    public function unblock($slug)
    {
        $user = Auth::guard('api')->user();
        $blockedUser = User::findBySlug($slug);
        $user->blockedUsers()->detach($blockedUser->id);
        return false;
    }
}
