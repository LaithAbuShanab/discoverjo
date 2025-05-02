<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\FollowerResource;
use App\Http\Resources\FollowingResource;
use App\Interfaces\Gateways\Api\User\FollowApiRepositoryInterface;
use App\Models\Follow;
use App\Models\User;
use App\Notifications\Users\follow\AcceptFollowRequestNotification;
use App\Notifications\Users\follow\NewFollowRequestNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use LevelUp\Experience\Models\Activity;


class EloquentFollowApiRepository implements FollowApiRepositoryInterface
{
    public function follow($request)
    {
        Follow::create($request);
        $followingUser = User::find($request['following_id']);

        $tokens = $followingUser->DeviceTokenMany->pluck('token')->toArray();
        $receiverLanguage = $followingUser->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.new-following-request', [], $receiverLanguage),
            'body'  => Lang::get('app.notifications.new-user-following-request', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'icon'  => asset('assets/icon/new.png'),
            'sound' => 'default',
        ];
        Notification::send($followingUser, new NewFollowRequestNotification(Auth::guard('api')->user(), $followingUser));
        sendNotification($tokens, $notificationData);

        //add points and streak
        $user = Auth::guard('api')->user();
        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
    }

    public function unfollow($following_slug)
    {
        $followingId = User::findBySlug($following_slug);
        $id = Auth::guard('api')->user()->id;
        $follow = Follow::where('follower_id', $id)->where('following_id', $followingId->id)->first();
        $follow->delete();
    }


    public function acceptFollower($follower_slug)
    {
        $id = Auth::guard('api')->user()->id;
        $followerUser = User::findBySlug($follower_slug);
        $eloquentFollows = Follow::where('follower_id', $followerUser->id)->where('following_id', $id)->first();
        $eloquentFollows->update([
            'status' => 1,
        ]);

        $tokens = $followerUser->DeviceTokenMany->pluck('token')->toArray();
        $receiverLanguage = $followerUser->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.accept-your-following-request', [], $receiverLanguage),
            'body'  => Lang::get('app.notifications.the-following-accept-your-following-request', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'icon' => asset('assets/icon/speaker.png'),
            'sound' => 'default',
        ];
        Notification::send($followerUser, new AcceptFollowRequestNotification(Auth::guard('api')->user()));
        sendNotification($tokens, $notificationData);

        $user = Auth::guard('api')->user();
        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
    }

    public function unacceptedFollower($follower_slug)
    {
        $id = Auth::guard('api')->user()->id;
        $follower = User::findBySlug($follower_slug);
        $eloquentFollows = Follow::where('follower_id', $follower->id)->where('following_id', $id)->first();
        $eloquentFollows->delete();
        return;
    }

    public function followersRequest()
    {
        $id = Auth::guard('api')->user()->id;

        $followers = Follow::where('following_id', $id)
            ->where('status', 0)
            ->whereHas('followerUser', function ($query) {
                $query->where('status', 1);
            })
            ->get();
        activityLog('follow', $followers->first(), 'the user view follower request ', 'view followers');
        return FollowerResource::collection($followers);
    }



    public function followers($user_slug)
    {
        $followingUser = User::findBySlug($user_slug);
        $followers = Follow::where('following_id', $followingUser->id)->where('status', 1)->get();
        activityLog('view other users follows', $followingUser, 'the user view followers of this user ', 'view');
        return FollowerResource::collection($followers);
    }

    public function followings($user_slug)
    {
        $follower = User::findBySlug($user_slug);
        $followers = Follow::where('follower_id', $follower->id)->where('status', 1)->get();
        activityLog('view other users followings', $follower, 'the user view followings of this user ', 'view');
        return FollowingResource::collection($followers);
    }
}
