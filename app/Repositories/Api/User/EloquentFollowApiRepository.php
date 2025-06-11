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
        $perPage = config('app.pagination_per_page');
        $followingUser = User::findBySlug($user_slug);

        // Get the paginated result first
        $rawFollowers = Follow::where('following_id', $followingUser->id)
            ->where('status', 1)
            ->with('followerUser') // eager load to reduce queries
            ->paginate($perPage);

        activityLog('view other users follows', $followingUser, 'the user view followers of this user ', 'view');

        // Filter followers with valid followerUser and status
        $filteredFollowers = $rawFollowers->getCollection()->filter(function ($follow) {
            return $follow->followerUser && $follow->followerUser->status === 1;
        });

        // Set the filtered collection back to paginator
        $rawFollowers->setCollection($filteredFollowers);

        $pagination = [
            'next_page_url' => $rawFollowers->nextPageUrl(),
            'prev_page_url' => $rawFollowers->previousPageUrl(),
            'total' => $rawFollowers->total(),
        ];

        return [
            'followers' => FollowerResource::collection($rawFollowers),
            'pagination' => $pagination
        ];
    }


    public function followings($user_slug)
    {
        $perPage = config('app.pagination_per_page');
        $follower = User::findBySlug($user_slug);

        // Get paginated followings with eager loaded user
        $rawFollowings = Follow::where('follower_id', $follower->id)
            ->where('status', 1)
            ->with('followingUser') // important to eager load
            ->paginate($perPage);

        activityLog('view other users followings', $follower, 'the user view followings of this user ', 'view');

        // Filter out null or inactive following users
        $filteredFollowings = $rawFollowings->getCollection()->filter(function ($follow) {
            return $follow->followingUser && $follow->followingUser->status === 1;
        });

        // Replace the paginator collection with filtered results
        $rawFollowings->setCollection($filteredFollowings);

        $pagination = [
            'next_page_url' => $rawFollowings->nextPageUrl(),
            'prev_page_url' => $rawFollowings->previousPageUrl(),
            'total' => $rawFollowings->total(),
        ];

        return [
            'followings' => FollowingResource::collection($rawFollowings),
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
