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
use Illuminate\Support\Facades\DB;
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
            'notification' => [
                'title' => Lang::get('app.notifications.new-following-request', [], $receiverLanguage),
                'body'  => Lang::get('app.notifications.new-user-following-request', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                'sound' => 'default'
            ],
            "data" => [
                'type'      => 'list_followers',
                'slug'      => $followingUser->slug,
                'user_id'   => $followingUser->id,
                'user_s_id' => Auth::guard('api')->user()->id
            ]
        ];
        Notification::send($followingUser, new NewFollowRequestNotification(Auth::guard('api')->user(), $followingUser));

        if (!empty($tokens)) {
            sendNotification($tokens, $notificationData);
        }

        //add points and streak
        $user = Auth::guard('api')->user();
        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
    }

    public function unfollow($following_slug)
    {
        DB::beginTransaction();

        try {
            $user = Auth::guard('api')->user();
            $followingUser = User::findBySlug($following_slug);
            $followingId = $followingUser->id;

            $follow = Follow::where('follower_id', $user->id)
                ->where('following_id', $followingId)
                ->first();

            if ($follow) {
                $follow->delete();
                $user->deductPoints(10);

                DB::table('notifications')
                    ->where('type', 'App\\Notifications\\Users\\Follow\\NewFollowRequestNotification')
                    ->where('notifiable_type', get_class($followingUser))
                    ->where('notifiable_id', $followingId)
                    ->where('data', 'LIKE', '%"user_s_id":' . $user->id . '%')
                    ->where('data', 'LIKE', '%"list_followers"%')
                    ->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
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
            'notification' => [
                'title' => Lang::get('app.notifications.accept-your-following-request', [], $receiverLanguage),
                'body'  => Lang::get('app.notifications.the-following-accept-your-following-request', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                'sound' => 'default'
            ],
            "data" => [
                'type'    => 'follow_profile',
                'slug'    => Auth::guard('api')->user()->slug,
                'user_id' => Auth::guard('api')->user()->id
            ]
        ];
        Notification::send($followerUser, new AcceptFollowRequestNotification(Auth::guard('api')->user()));

        if (!empty($tokens)) {
            sendNotification($tokens, $notificationData);
        }

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
        $perPage = config('app.pagination_per_page');
        $followingUser = User::findBySlug($user_slug);

        $rawFollowers = Follow::where('following_id', $followingUser->id)
            ->where('status', 1)
            ->with('followerUser')
            ->paginate($perPage);

        activityLog('view other users follows', $followingUser, 'the user view followers of this user', 'view');

        $currentUser = auth('api')->user();

        $filteredFollowers = $rawFollowers->getCollection()->filter(function ($follow) use ($currentUser) {
            $follower = $follow->followerUser;

            if (! $follower || $follower->status !== 1) {
                return false;
            }

            if ($currentUser && (
                $follower->hasBlocked($currentUser) || $currentUser->hasBlocked($follower)
            )) {
                return false;
            }

            return true;
        });

        $rawFollowers->setCollection($filteredFollowers);

        $hasBlocked = $currentUser && $currentUser->hasBlocked($followingUser);

        $pagination = [
            'next_page_url' => $rawFollowers->nextPageUrl(),
            'prev_page_url' => $rawFollowers->previousPageUrl(),
            'total' => $rawFollowers->total(),
        ];

        return [
            'followers' => $hasBlocked ? [] : FollowerResource::collection($rawFollowers),
            'pagination' => $pagination
        ];
    }

    public function followings($user_slug)
    {
        $perPage = config('app.pagination_per_page');
        $follower = User::findBySlug($user_slug);

        $rawFollowings = Follow::where('follower_id', $follower->id)
            ->where('status', 1)
            ->with('followingUser')
            ->paginate($perPage);

        activityLog('view other users followings', $follower, 'the user view followings of this user', 'view');

        $currentUser = auth('api')->user();

        $filteredFollowings = $rawFollowings->getCollection()->filter(function ($follow) use ($currentUser) {
            $following = $follow->followingUser;

            if (! $following || $following->status !== 1) {
                return false;
            }

            if ($currentUser && (
                $following->hasBlocked($currentUser) || $currentUser->hasBlocked($following)
            )) {
                return false;
            }

            return true;
        });


        $rawFollowings->setCollection($filteredFollowings);

        $hasBlocked = $currentUser && $currentUser->hasBlocked($follower);

        $pagination = [
            'next_page_url' => $rawFollowings->nextPageUrl(),
            'prev_page_url' => $rawFollowings->previousPageUrl(),
            'total' => $rawFollowings->total(),
        ];

        return [
            'followings' => $hasBlocked ? [] : FollowingResource::collection($rawFollowings),
            'pagination' => $pagination
        ];
    }


    public function removeFollower($user_slug)
    {
        $user = Auth::guard('api')->user();
        $follower = User::findBySlug($user_slug);
        $id = $user->id;
        $follow = Follow::where('follower_id', $follower->id)->where('following_id', $id)->first();
        $follow->delete();
        $user->deductPoints(10);
    }
}
