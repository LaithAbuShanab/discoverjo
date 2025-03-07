<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\SinglePostResource;
use App\Http\Resources\UserPostResource;
use App\Interfaces\Gateways\Api\User\PostApiRepositoryInterface;
use App\Models\Admin;
use App\Models\DeviceToken;
use App\Models\Post;
use App\Models\User;
use App\Notifications\Admin\NewPostNotification;
use App\Notifications\Users\post\NewPostDisLikeNotification;
use App\Notifications\Users\post\NewPostFollowersNotification;
use App\Notifications\Users\post\NewPostLikeNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EloquentPostApiRepository implements PostApiRepositoryInterface
{
    public function followingPost()
    {
        $perPage = 20;
        $userId = Auth::guard('api')->user()->id;

        // Get posts from followed users with specific privacy levels, ordered by newest first
        $posts = Post::whereIn('user_id', function ($query) use ($userId) {
            $query->select('following_id')
                ->from('follows')
                ->where('follower_id', $userId);
        })
            ->whereIn('privacy', ["1", "2"])
            ->orderBy('created_at', 'desc') // Order by newest first
            ->paginate($perPage);

        $postsArray = $posts->toArray();

        $pagination = [
            'next_page_url' => $postsArray['next_page_url'],
            'prev_page_url' => $postsArray['prev_page_url'], // Fixed to prev_page_url
            'total' => $postsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
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
            ])
            ->thenReturn();

        $validatedData['content'] = $filteredContent;
        $eloquentPost = Post::create($validatedData);
        if ($media !== null) {
            foreach ($media as $image) {
                $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                $eloquentPost->addMedia($image)->usingFileName($filename)->toMediaCollection('post');
            }
        }
        Notification::send(Admin::all(), new NewPostNotification($eloquentPost));

        //$response = Http::post('http://127.0.0.1:3000/notifications');

        $followers = Auth::guard('api')->user()->followers()->get();
        Notification::send($followers, new NewPostFollowersNotification(Auth::guard('api')->user()));

        // To Send Notification To Owner Using Firebase Cloud Messaging
        $followersTokens = DeviceToken::whereIn('user_id', Auth::guard('api')->user()->followers()->pluck('follower_id')->toArray())->pluck('token')->toArray();
        $receiverLanguage = Auth::guard('api')->user()->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.new-post-title', [], $receiverLanguage),
            'body' => Lang::get('app.notifications.new-post-body', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'sound' => 'default',
        ];
        sendNotification($followersTokens, $notificationData);

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
        //
        activity('view')
            ->causedBy(Auth::check() ? Auth::user() : 'guest') // User or Guest
            ->performedOn(Post::find($id))
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ])
            ->log('User viewed the post');
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
        Post::find($id)->delete();
    }

    public function favorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoritePosts()->attach($id);
    }
    public function deleteFavorite($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoritePosts()->detach($id);
    }

    public function postLike($request)
    {
        $post = Post::find($request->post_id);
        $status = $request->status == "like" ? '1' : '0';
        $ownerToken = $post->user->DeviceToken->token;
        $receiverLanguage = $post->user->lang;
        $notificationData=[];
        $userPostId = $post->user_id;

        $existingLike = $post->likes()->where('user_id', Auth::guard('api')->user()->id)->first();

        if ($existingLike) {
            if ($existingLike->status != $status) {
                $existingLike->update(['status' => $status]);

                if($request->status == "like"){
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-post-like', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-like-in-post', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];
                    Notification::send(User::find($userPostId), new NewPostLikeNotification(Auth::guard('api')->user()));

                }else{
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-post-dislike', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-dislike-in-post', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];
                    Notification::send(User::find($userPostId), new NewPostDisLikeNotification(Auth::guard('api')->user()));
                }
            } else {
                $existingLike->delete();
            }
        } else {
            $post->likes()->create([
                'user_id' => Auth::guard('api')->user()->id,
                'status' => $status,
            ]);
        }



        sendNotification($ownerToken, $notificationData);
    }
}
