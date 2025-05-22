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
use LevelUp\Experience\Models\Activity;


class EloquentCommentApiRepository implements CommentApiRepositoryInterface
{
    public function createComment($data)
    {
        // Apply filtering pipeline
        $filteredContent = app(Pipeline::class)
            ->send($data['content'])
            ->through([
                ContentFilter::class,
            ])
            ->thenReturn();

        $data['content'] = $filteredContent;
        $comment = Comment::create($data);
        $userPost = Post::find($data['post_id'])->user;

        if ($data['parent_id'] == null) {
            // To Save Notification In Database
            Notification::send($userPost, new NewCommentNotification(Auth::guard('api')->user(), $comment->id, $data['post_id']));

            // To Send Notification To Owner Using Firebase Cloud Messaging
            $tokens = $userPost->DeviceTokenMany->pluck('token')->toArray();
            $receiverLanguage = $userPost->lang;
            $notificationData = [
                'title' => Lang::get('app.notifications.new-comment', [], $receiverLanguage),
                'body'  => Lang::get('app.notifications.new-user-comment-in-post', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                'icon'  => asset('assets/icon/new.png'),
                'sound' => 'default',
            ];

            sendNotification($tokens, $notificationData);
        } else {
            $parentComment = Comment::find($data['parent_id']);
            $userParentComment = User::find($parentComment->user_id);
            $tokens = $userParentComment->DeviceTokenMany->pluck('token')->toArray();
            $receiverLanguage = $userParentComment->lang;

            // To Save Notification In Database
            Notification::send($userParentComment, new NewReplyNotification(Auth::guard('api')->user(), $comment->id, $data['post_id']));

            $notificationData = [
                'title' => Lang::get('app.notifications.new-reply', [], $receiverLanguage),
                'body' => Lang::get('app.notifications.new-user-reply-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                'sound' => 'default',
            ];
            sendNotification($tokens, $notificationData);
        }


        //add points and streak
        $user = User::find($data['user_id']);
        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);

        return new CommentResource($comment);
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
        $comment = Comment::find($id);
        $comment->delete();
    }

    public function commentLike($data)
    {
        $comment = Comment::find($data['comment_id']);
        $userComment = User::find($comment->user_id);
        $tokens = $userComment->DeviceTokenMany->pluck('token')->toArray();
        $receiverLanguage = $userComment->lang;
        $notificationData = [];
        $status = $data['status'] == "like" ? '1' : '0';

        $existingLike = $comment->likes()->where('user_id', Auth::guard('api')->user()->id)->first();
        if ($existingLike) {
            if ($existingLike->status != $status) {
                $existingLike->update(['status' => $status]);

                if ($comment->user_id != Auth::guard('api')->user()->id) {
                    if ($data['status'] == "like") {
                        $notificationData = [
                            'title' => Lang::get('app.notifications.new-comment-like', [], $receiverLanguage),
                            'body'  => Lang::get('app.notifications.new-user-like-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                            'icon' => asset('assets/icon/speaker.png'),
                            'sound' => 'default',
                        ];
                        Notification::send($userComment, new NewCommentLikeNotification(Auth::guard('api')->user(), $comment->id, $comment->post_id));
                    } else {
                        $notificationData = [
                            'title' => Lang::get('app.notifications.new-comment-dislike', [], $receiverLanguage),
                            'body'  => Lang::get('app.notifications.new-user-dislike-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                            'icon' => asset('assets/icon/speaker.png'),
                            'sound' => 'default',
                        ];

                        Notification::send($userComment, new NewCommentDisLikeNotification(Auth::guard('api')->user(), $comment->id, $comment->post_id));
                    }
                }
            } else {
                $existingLike->delete();
            }
        } else {
            $comment->likes()->create([
                'user_id' => Auth::guard('api')->user()->id,
                'status' => $status,
            ]);

            if ($comment->user_id != Auth::guard('api')->user()->id) {
                if ($data['status'] == "like") {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-comment-like', [], $receiverLanguage),
                        'body'  => Lang::get('app.notifications.new-user-like-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'icon' => asset('assets/icon/speaker.png'),
                        'sound' => 'default',
                    ];
                    Notification::send($userComment, new NewCommentLikeNotification(Auth::guard('api')->user(), $comment->id, $comment->post_id));
                } else {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-comment-dislike', [], $receiverLanguage),
                        'body'  => Lang::get('app.notifications.new-user-dislike-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'icon' => asset('assets/icon/speaker.png'),
                        'sound' => 'default',
                    ];

                    Notification::send($userComment, new NewCommentDisLikeNotification(Auth::guard('api')->user(), $comment->id, $comment->post_id));
                }
            }
        }

        if (!empty($notificationData) && $comment->user_id != Auth::guard('api')->user()->id) {
            sendNotification($tokens, $notificationData);
        }

        $user = Auth::guard('api')->user();
        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
        ActivityLog('comment', $comment, 'the user ' . $data['status'] . ' the comment', $data['status']);
    }
}
