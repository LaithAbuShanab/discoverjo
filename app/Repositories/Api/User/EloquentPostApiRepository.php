<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\SinglePostResource;
use App\Http\Resources\UserPostResource;
use App\Interfaces\Gateways\Api\User\PostApiRepositoryInterface;
use App\Models\Admin;
use App\Models\Post;
use App\Models\User;
use App\Notifications\Users\post\NewPostDisLikeNotification;
use App\Notifications\Users\post\NewPostFollowersNotification;
use App\Notifications\Users\post\NewPostLikeNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use App\Services\FirebaseMessagingService;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

class EloquentPostApiRepository implements PostApiRepositoryInterface
{

    public function __construct(protected FirebaseMessagingService $firebaseMessagingService) {


    }
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
                ->where('users.status', 1); // Only active users
        })
            ->whereIn('privacy', [1, 2])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $postsArray = $posts->toArray();

        $pagination = [
            'next_page_url' => $postsArray['next_page_url'],
            'prev_page_url' => $postsArray['prev_page_url'],
            'total' => $postsArray['total'],
        ];

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
        $recipient = Admin::all();
        if ($recipient) {
            FilamentNotification::make()
                ->title('New User post')
                ->success()
                ->body("A new user ({$eloquentPost->user->username}) (ID: {$eloquentPost->user->id}) has just registered.")
                ->actions([
                    Action::make('view_post')
                        ->label('View Post')
                        ->url(route('filament.admin.resources.posts.view', $eloquentPost)),
                ])
                ->sendToDatabase($recipient);
        }

        // Notification for followers if the privacy not equal 0
        if ($eloquentPost->privacy) {

            $followers = Auth::guard('api')->user()
                ->followers()
                ->wherePivot('status', 1)
                ->where('users.status', 1)
                ->get();

            // To Save Notification In Database
            Notification::send($followers, new NewPostFollowersNotification(Auth::guard('api')->user()));

            // Send Notification Via Firebase
            foreach ($followers as $follower) {
                $token = $follower->DeviceToken->token;
                $receiverLanguage = $follower->lang;
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-post-title', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-post-body', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];
                $response = $this->firebaseMessagingService->sendNotification(
                    'ExponentPushToken[pe-Y44GLOTj5csZP2wCaDz]',
                    $notificationData['title'],
                    $notificationData['body']
                );
//                sendNotification($token, $notificationData);
            }
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

    public function postLike($data)
    {
        $post = Post::find($data['post_id']);
        $status = $data['status'] == "like" ? '1' : '0';
        $ownerToken = $post->user->DeviceToken->token;
        $receiverLanguage = $post->user->lang;
        $notificationData = [];
        $userPostId = $post->user_id;

        $existingLike = $post->likes()->where('user_id', Auth::guard('api')->user()->id)->first();

        if ($existingLike) {
            if ($existingLike->status != $status) {
                $existingLike->update(['status' => $status]);

                if ($data->status == "like") {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-post-like', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-like-in-post', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];
                    Notification::send(User::find($userPostId), new NewPostLikeNotification(Auth::guard('api')->user()));
                } else {
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

        if (!empty($notificationData)) {
            sendNotification($ownerToken, $notificationData);
        }
    }
}
