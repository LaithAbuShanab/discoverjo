<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\SinglePostResource;
use App\Http\Resources\UserPostResource;
use App\Interfaces\Gateways\Api\User\PostApiRepositoryInterface;
use App\Models\Post;
use App\Models\User;
use App\Notifications\Users\post\NewPostDisLikeNotification;
use App\Notifications\Users\post\NewPostFollowersNotification;
use App\Notifications\Users\post\NewPostLikeNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use LevelUp\Experience\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\DB;

class EloquentPostApiRepository implements PostApiRepositoryInterface
{
    public function followingPost()
    {
        $perPage = config('app.pagination_per_page');
        $userId = Auth::guard('api')->user()->id;

        // Ensure following_id users are active (status = 1)
        $posts = Post::whereIn('user_id', function ($query) use ($userId) {
            $query->select('follows.following_id')
                ->from('follows')
                ->join('users', 'users.id', '=', 'follows.following_id')
                ->where('follows.follower_id', $userId)
                ->where('follows.status', 1)
                ->where('users.status', 1); // Only active users
        })
            ->whereIn('privacy', [1, 2])
            ->orWhere('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $postsArray = $posts->toArray();

        $pagination = [
            'next_page_url' => $postsArray['next_page_url'],
            'prev_page_url' => $postsArray['prev_page_url'],
            'total' => $postsArray['total'],
        ];

        activityLog('view followings posts', $posts->first(), 'the user view all post belong followings', 'view');

        return [
            'posts' => UserPostResource::collection($posts),
            'pagination' => $pagination
        ];
    }

    public function createPost($validatedData, $media)
    {
        $filteredContent = app(Pipeline::class)
            ->send($validatedData['content'])
            ->through([
                ContentFilter::class,
            ])->thenReturn();

        $validatedData['content'] = $filteredContent;
        $eloquentPost = Post::create($validatedData);
        if ($media !== null) {
            foreach ($media as $image) {
                $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                $eloquentPost->addMedia($image)->usingFileName($filename)->toMediaCollection('post');
            }
        }

        // Dashboard notification for new post
        adminNotification(
            'New Post',
            'a new post has been created by ' . $eloquentPost->user->username . ' (ID: ' . $eloquentPost->user->id . ')',
            ['action' => 'view_post', 'action_label' => 'View Post', 'action_url' => route('filament.admin.resources.posts.view', $eloquentPost)]
        );

        // Notification for followers if the privacy not equal 0
        if ($eloquentPost->privacy) {

            $followers = Auth::guard('api')->user()
                ->followers()
                ->wherePivot('status', 1)
                ->where('users.status', 1)
                ->get();

            // To Save Notification In Database
            Notification::send($followers, new NewPostFollowersNotification(Auth::guard('api')->user(), $eloquentPost->id));

            // Send Notification Via Firebase
            foreach ($followers as $follower) {
                $tokens = $follower->DeviceTokenMany->pluck('token')->toArray();
                $receiverLanguage = $follower->lang;
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-post-title', [], $receiverLanguage),
                    'body'  => Lang::get('app.notifications.new-post-body', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'icon'  => asset('assets/icon/new.png'),
                    'sound' => 'default',
                ];

                sendNotification($tokens, $notificationData);
            }

            $user = Auth::guard('api')->user();
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);
        }
    }

    public function updatePost($validatedData, $media)
    {
        $filteredContent = app(Pipeline::class)
            ->send($validatedData['content'])
            ->through([
                ContentFilter::class,
            ])
            ->thenReturn();

        $validatedData['content'] = $filteredContent;

        $eloquentPost = Post::findOrFail($validatedData['post_id']);
        unset($validatedData['post_id']);
        $eloquentPost->update($validatedData);
        if ($media !== null) {
            foreach ($media as $image) {
                $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                $eloquentPost->addMedia($image)->usingFileName($filename)->toMediaCollection('post');
            }
        }
    }

    public function showPost($id)
    {
        $eloquentPost = Post::findOrFail($id);
        return new SinglePostResource($eloquentPost);
    }

    public function deleteImage($id)
    {
        if ($id) {
            Media::find($id)->delete();
        }
        return;
    }

    public function delete($id)
    {
        $post = Post::findOrFail($id);

        DB::table('notifications')
            ->where('type', 'App\\Notifications\\Users\\Post\\NewPostFollowersNotification')
            ->where('data', 'LIKE', '%"post_id":' . $post->id . '%')
            ->delete();

        $post->delete();

        $user = Auth::guard('api')->user();
        $user->deductPoints(10);
    }

    public function favorite($id)
    {
        $user = Auth::guard('api')->user();
        $post = Post::find($id);
        activityLog('post', $post, 'the user favorite the post', 'favorite');
        $user->favoritePosts()->attach($id);

        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
    }

    public function deleteFavorite($id)
    {
        $user = Auth::guard('api')->user();
        $post = Post::find($id);
        activityLog('post', $post, 'the user unfavored the post', 'unfavored');
        $user->favoritePosts()->detach($id);
        $user = Auth::guard('api')->user();
        $user->deductPoints(10);
    }

    public function postLike($data)
    {
        DB::beginTransaction();

        try {
            $post = Post::findOrFail($data['post_id']);
            $status = $data['status'] == "like" ? '1' : '0';
            $authUser = Auth::guard('api')->user();
            $userPost = $post->user;
            $receiverLanguage = $userPost->lang;
            $tokens = $userPost->DeviceTokenMany->pluck('token')->toArray();

            $existingLike = $post->likes()->where('user_id', $authUser->id)->first();

            // Define notification types
            $likeNotificationType = NewPostLikeNotification::class;
            $dislikeNotificationType = NewPostDisLikeNotification::class;

            $deleteNotification = function ($type) use ($authUser, $post) {
                DB::table('notifications')
                    ->where('type', $type)
                    ->where('notifiable_id', $post->user_id)
                    ->where('notifiable_type', get_class($post->user))
                    ->where('data', 'LIKE', '%"post_id":' . $post->id . '%')
                    ->where('data', 'LIKE', '%"user_id":' . $authUser->id . '%')
                    ->where('data', 'LIKE', '%"options%') // optional: ensures it's in "options"
                    ->delete();
            };

            if ($existingLike) {
                if ($existingLike->status != $status) {
                    $existingLike->update(['status' => $status]);

                    $deleteNotification($existingLike->status === '1' ? $dislikeNotificationType : $likeNotificationType);

                    if ($authUser->id != $post->user_id) {
                        if ($status === '1') {
                            Notification::send($userPost, new NewPostLikeNotification($authUser, $post->id));
                            sendNotification($tokens, [
                                'title' => Lang::get('app.notifications.new-post-like-title', [], $receiverLanguage),
                                'body'  => Lang::get('app.notifications.new-post-like-body', ['username' => $authUser->username], $receiverLanguage),
                                'icon'  => asset('assets/icon/speaker.png'),
                                'sound' => 'default',
                            ]);
                        } else {
                            Notification::send($userPost, new NewPostDisLikeNotification($authUser, $post->id));
                            sendNotification($tokens, [
                                'title' => Lang::get('app.notifications.new-post-dislike-title', [], $receiverLanguage),
                                'body'  => Lang::get('app.notifications.new-post-dislike-body', ['username' => $authUser->username], $receiverLanguage),
                                'icon'  => asset('assets/icon/speaker.png'),
                                'sound' => 'default',
                            ]);
                        }
                    }
                } else {
                    $existingLike->delete();
                    $deleteNotification($status === '1' ? $likeNotificationType : $dislikeNotificationType);
                }
            } else {
                $post->likes()->create([
                    'user_id' => $authUser->id,
                    'status' => $status,
                ]);

                if ($authUser->id != $post->user_id) {
                    if ($status === '1') {
                        Notification::send($userPost, new NewPostLikeNotification($authUser, $post->id));
                        sendNotification($tokens, [
                            'title' => Lang::get('app.notifications.new-post-like-title', [], $receiverLanguage),
                            'body'  => Lang::get('app.notifications.new-post-like-body', ['username' => $authUser->username], $receiverLanguage),
                            'icon'  => asset('assets/icon/speaker.png'),
                            'sound' => 'default',
                        ]);
                    } else {
                        Notification::send($userPost, new NewPostDisLikeNotification($authUser, $post->id));
                        sendNotification($tokens, [
                            'title' => Lang::get('app.notifications.new-post-dislike-title', [], $receiverLanguage),
                            'body'  => Lang::get('app.notifications.new-post-dislike-body', ['username' => $authUser->username], $receiverLanguage),
                            'icon'  => asset('assets/icon/speaker.png'),
                            'sound' => 'default',
                        ]);
                    }
                }

                // Add points
                $authUser->addPoints(10);
                $activity = Activity::find(1);
                $authUser->recordStreak($activity);
            }

            activityLog($data['status'], $post, 'the user ' . $data['status'] . ' the post', $data['status']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function currentUserPosts()
    {
        $paginationPerPage = config('app.pagination_per_page');
        $user = Auth::guard('api')->user();
        $posts = $user->posts()->orderBy('created_at', 'desc')->paginate($paginationPerPage);
        $postsArray = $posts->toArray();

        return [
            'places' => UserPostResource::collection($posts),
            'pagination' => [
                'next_page_url' => $posts->nextPageUrl(),
                'prev_page_url' => $posts->previousPageUrl(),
                'total' => $postsArray['total'],
            ],
        ];
    }

    public function otherUserPosts($slug)
    {
        $paginationPerPage = config('app.pagination_per_page');
        $user = User::findBySlug($slug);
        $posts = $user->posts()->orderBy('created_at', 'desc')->paginate($paginationPerPage);
        $postsArray = $posts->toArray();

        return [
            'posts' => UserPostResource::collection($posts),
            'pagination' => [
                'next_page_url' => $posts->nextPageUrl(),
                'prev_page_url' => $posts->previousPageUrl(),
                'total' => $postsArray['total'],
            ],
        ];
    }
}
