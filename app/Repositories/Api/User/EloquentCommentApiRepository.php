<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\CommentResource;
use App\Interfaces\Gateways\Api\User\CommentApiRepositoryInterface;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\Users\post\NewCommentDisLikeNotification;
use App\Notifications\Users\post\NewCommentLikeNotification;
use App\Notifications\Users\post\NewCommentNotification;
use App\Notifications\Users\post\NewReplyNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use LevelUp\Experience\Models\Activity;


class EloquentCommentApiRepository implements CommentApiRepositoryInterface
{

    public function createComment($data)
    {
        return DB::transaction(function () use ($data) {
            $filteredContent = app(Pipeline::class)
                ->send($data['content'])
                ->through([
                    ContentFilter::class,
                ])
                ->thenReturn();

            $data['content'] = $filteredContent;
            $comment = Comment::create($data);
            $commentUser = Auth::guard('api')->user();

            if ($data['parent_id'] == null) {
                $post = Post::find($data['post_id']);
                $userPost = $post->user;

                if ($commentUser->id != $userPost->id) {
                    Notification::send($userPost, new NewCommentNotification($commentUser, $comment->id, $data['post_id']));
                    $tokens = $userPost->DeviceTokenMany->pluck('token')->toArray();
                    $receiverLanguage = $userPost->lang;
                    $notificationData = [
                        "notification" => [
                            'title' => Lang::get('app.notifications.new-comment', [], $receiverLanguage),
                            'body'  => Lang::get('app.notifications.new-user-comment-in-post', ['username' => $commentUser->username], $receiverLanguage),
                            'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                            'sound' => 'default',
                        ],
                        'data'  => [
                            'type'       => 'comment',
                            'slug'       => null,
                            'post_id'    => $data['post_id'],
                            'comment_id' => $comment->id,
                        ]
                    ];
                    sendNotification($tokens, $notificationData);
                }
            } else {
                $parentComment = Comment::find($data['parent_id']);
                $userParentComment = User::find($parentComment->user_id);

                if ($commentUser->id != $userParentComment->id) {
                    Notification::send($userParentComment, new NewReplyNotification($commentUser, $comment->id, $data['post_id']));

                    $tokens = $userParentComment->DeviceTokenMany->pluck('token')->toArray();
                    $receiverLanguage = $userParentComment->lang;
                    $notificationData = [
                        "notification" => [
                            'title' => Lang::get('app.notifications.new-reply', [], $receiverLanguage),
                            'body' => Lang::get('app.notifications.new-user-reply-in-comment', ['username' => $commentUser->username], $receiverLanguage),
                            'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                            'sound' => 'default',
                        ],
                        'data'  => [
                            'type'       => 'comment',
                            'slug'       => null,
                            'post_id'    => $data['post_id'],
                            'comment_id' => $comment->id,
                        ]
                    ];
                    sendNotification($tokens, $notificationData);
                }
            }

            $user = User::find($data['user_id']);
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);

            return new CommentResource($comment);
        });
    }

    public function updateComment($data)
    {
        $filteredContent = app(Pipeline::class)
            ->send($data['content'])
            ->through([
                ContentFilter::class,
            ])
            ->thenReturn();

        $data['content'] = $filteredContent;
        $comment = Comment::find($data['comment_id']);
        $comment->update([
            'content' => $data['content']
        ]);
        return $comment;
    }

    public function deleteComment($id)
    {
        DB::beginTransaction();

        try {
            $user = Auth::guard('api')->user();
            $user->deductPoints(10);

            $comment = Comment::findOrFail($id);

            DB::table('notifications')
                ->where('type', 'App\\Notifications\\Users\\Post\\NewCommentNotification')
                ->where('notifiable_type', get_class($comment->post->user))
                ->where('notifiable_id', $comment->post->user->id)
                ->where('data', 'LIKE', '%"options"%')
                ->where('data', 'LIKE', '%"comment_id":' . $comment->id . '%')
                ->delete();

            $comment->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function commentLike($data)
    {
        DB::beginTransaction();

        try {
            $authUser = Auth::guard('api')->user();
            $comment = Comment::findOrFail($data['comment_id']);
            $userComment = User::findOrFail($comment->user_id);
            $tokens = $userComment->DeviceTokenMany->pluck('token')->toArray();
            $receiverLanguage = $userComment->lang;
            $status = $data['status'] == "like" ? '1' : '0';

            $existingLike = $comment->likes()->where('user_id', $authUser->id)->first();

            $likeNotificationType = NewCommentLikeNotification::class;
            $dislikeNotificationType = NewCommentDisLikeNotification::class;

            $deleteNotification = function ($type) use ($authUser, $comment) {
                DB::table('notifications')
                    ->where('type', $type)
                    ->where('notifiable_type', get_class($comment->user))
                    ->where('notifiable_id', $comment->user_id)
                    ->where('data', 'LIKE', '%"comment_id":' . $comment->id . '%')
                    ->where('data', 'LIKE', '%"user_id":' . $authUser->id . '%')
                    ->delete();
            };

            $notificationData = [];

            if ($existingLike) {
                if ($existingLike->status != $status) {
                    $deleteNotification($existingLike->status === '1' ? $likeNotificationType : $dislikeNotificationType);
                    $existingLike->update(['status' => $status]);

                    if ($comment->user_id != $authUser->id) {
                        if ($status === '1') {
                            $notificationData = [
                                'notification' => [
                                    'title' => Lang::get('app.notifications.new-comment-like', [], $receiverLanguage),
                                    'body'  => Lang::get('app.notifications.new-user-like-in-comment', ['username' => $authUser->username], $receiverLanguage),
                                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                                    'sound' => 'default'
                                ],
                                'data'  => [
                                    'type'       => 'comment',
                                    'slug'       => null,
                                    'post_id'    => $comment->post_id,
                                    'comment_id' => $comment->id,
                                    'user_id'    => $authUser->id
                                ]
                            ];
                            Notification::send($userComment, new NewCommentLikeNotification($authUser, $comment->id, $comment->post_id));
                        } else {
                            $notificationData = [
                                'notification' => [
                                    'title' => Lang::get('app.notifications.new-comment-dislike', [], $receiverLanguage),
                                    'body'  => Lang::get('app.notifications.new-user-dislike-in-comment', ['username' => $authUser->username], $receiverLanguage),
                                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                                    'sound' => 'default'
                                ],
                                'data'  => [
                                    'type'       => 'comment',
                                    'slug'       => null,
                                    'post_id'    => $comment->post_id,
                                    'comment_id' => $comment->id,
                                    'user_id'    => $authUser->id
                                ]
                            ];
                            Notification::send($userComment, new NewCommentDisLikeNotification($authUser, $comment->id, $comment->post_id));
                        }
                    }
                } else {
                    $existingLike->delete();
                    $deleteNotification($status === '1' ? $likeNotificationType : $dislikeNotificationType);
                }
            } else {
                $comment->likes()->create([
                    'user_id' => $authUser->id,
                    'status' => $status,
                ]);

                if ($comment->user_id != $authUser->id) {
                    if ($status === '1') {
                        $notificationData = [
                            'notification' => [
                                'title' => Lang::get('app.notifications.new-comment-like', [], $receiverLanguage),
                                'body'  => Lang::get('app.notifications.new-user-like-in-comment', ['username' => $authUser->username], $receiverLanguage),
                                'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                                'sound' => 'default'
                            ],
                            'data'  => [
                                'type'       => 'comment',
                                'slug'       => null,
                                'post_id'    => $comment->post_id,
                                'comment_id' => $comment->id,
                                'user_id'    => $authUser->id
                            ]
                        ];
                        Notification::send($userComment, new NewCommentLikeNotification($authUser, $comment->id, $comment->post_id));
                    } else {
                        $notificationData = [
                            'notification' => [
                                'title' => Lang::get('app.notifications.new-comment-dislike', [], $receiverLanguage),
                                'body'  => Lang::get('app.notifications.new-user-dislike-in-comment', ['username' => $authUser->username], $receiverLanguage),
                                'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                                'sound' => 'default'
                            ],
                            'data'  => [
                                'type'       => 'comment',
                                'slug'       => null,
                                'post_id'    => $comment->post_id,
                                'comment_id' => $comment->id,
                                'user_id'    => $authUser->id
                            ]
                        ];
                        Notification::send($userComment, new NewCommentDisLikeNotification($authUser, $comment->id, $comment->post_id));
                    }
                }

                $authUser->addPoints(10);
                $activity = Activity::find(1);
                $authUser->recordStreak($activity);
            }

            if (!empty($notificationData) && $comment->user_id != $authUser->id) {
                sendNotification($tokens, $notificationData);
            }

            ActivityLog('comment', $comment, 'the user ' . $data['status'] . ' the comment', $data['status']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
