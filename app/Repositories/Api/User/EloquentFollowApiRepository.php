<?php

namespace App\Repositories\Api\User;

//use App\Http\Resources\AllFollowsResource;
use App\Http\Resources\FollowerResource;
use App\Http\Resources\FollowingResource;
//use App\Http\Resources\FollowResource;
use App\Interfaces\Gateways\Api\User\FollowApiRepositoryInterface;
use App\Models\Follow;
use App\Models\User;
use App\Notifications\Users\follow\AcceptFollowRequestNotification;
use App\Notifications\Users\follow\NewFollowRequestNotification;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;


class EloquentFollowApiRepository implements FollowApiRepositoryInterface
{
    public function follow($request)
    {
        $eloquentFollows = Follow::create($request);
        $followingUser = User::find($request['following_id']);
        $ownerToken = $followingUser->DeviceToken->token;
        $receiverLanguage = $followingUser->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.new-following-request', [], $receiverLanguage),
            'body' => Lang::get('app.notifications.new-user-following-request', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'sound' => 'default',
        ];
        Notification::send($followingUser, new NewFollowRequestNotification(Auth::guard('api')->user()));
        sendNotification($ownerToken, $notificationData);
    }

    public function unfollow($following_id)
    {
        $id = Auth::guard('api')->user()->id;
        $eloquentFollows = Follow::where('follower_id', $id)->where('following_id', $following_id)->delete();
        return;
    }

    public function acceptFollower($follower_id)
    {
        $id = Auth::guard('api')->user()->id;
        $eloquentFollows = Follow::where('follower_id', $follower_id)->where('following_id', $id)->first();
        $eloquentFollows->update([
            'status' => 1,
        ]);

        $followerUser = User::find($follower_id);
        $ownerToken = $followerUser->DeviceToken->token;
        $receiverLanguage = $followerUser->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.accept-your-following-request', [], $receiverLanguage),
            'body' => Lang::get('app.notifications.the-following-accept-your-following-request', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'sound' => 'default',
        ];
        Notification::send($followerUser, new AcceptFollowRequestNotification(Auth::guard('api')->user()));
        sendNotification($ownerToken, $notificationData);
        return;
    }

    public function unacceptedFollower($follower_id)
    {
        $id = Auth::guard('api')->user()->id;
        $eloquentFollows = Follow::where('follower_id', $follower_id)->where('following_id', $id)->delete();
        return;
    }

    public function followersRequest($id)
    {
        $followers = Follow::where('following_id', $id)->where('status', 0)->get();
        return FollowerResource::collection($followers);
    }


    public function followers($user_id)
    {
        $followers = Follow::where('following_id', $user_id)->where('status', 1)->get();
        return FollowerResource::collection($followers);
    }

    public function followings($user_id)
    {
        $followers = Follow::where('follower_id', $user_id)->where('status', 1)->get();
        return FollowingResource::collection($followers);
    }
}
