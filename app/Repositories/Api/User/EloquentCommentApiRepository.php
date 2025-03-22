<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Interfaces\Gateways\Api\User\CommentApiRepositoryInterface;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\Users\post\NewCommentDisLikeNotification;
use App\Notifications\Users\post\NewCommentLikeNotification;
use App\Notifications\Users\post\NewCommentNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use Illuminate\Pipeline\Pipeline;


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

        // To Save Notification In Database
        Notification::send($userPost, new NewCommentNotification(Auth::guard('api')->user()));

        // To Send Notification To Owner Using Firebase Cloud Messaging
        $ownerToken = $userPost->DeviceToken->token;
        $receiverLanguage = $userPost->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.new-comment', [], $receiverLanguage),
            'body' => Lang::get('app.notifications.new-user-comment-in-post', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'sound' => 'default',
        ];

        sendNotification([$ownerToken], $notificationData);

        return $comment;
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
        Comment::find($id)->delete();
    }

    public function commentLike($data)
    {
        $comment = Comment::find($data['comment_id']);
        $userComment = User::find($comment->user_id);
        $ownerToken = $userComment->DeviceToken->token;
        $receiverLanguage = $userComment->lang;
        $notificationData = [];
        $status = $data['status'] == "like" ? '1' : '0';

        $existingLike = $comment->likes()->where('user_id', Auth::guard('api')->user()->id)->first();



        if ($existingLike) {
            if ($existingLike->status != $status) {
                $existingLike->update(['status' => $status]);

                if ($data['status'] == "like") {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-comment-like', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-like-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];
                    Notification::send($userComment, new NewCommentLikeNotification(Auth::guard('api')->user()));
                } else {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-comment-dislike', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-dislike-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];

                    Notification::send($userComment, new NewCommentDisLikeNotification(Auth::guard('api')->user()));
                }
            } else {
                $existingLike->delete();
            }
        } else {
            $comment->likes()->create([
                'user_id' => Auth::guard('api')->user()->id,
                'status' => $status,
            ]);

            if ($data['status'] == "like") {
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-comment-like', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-like-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];
                Notification::send($userComment, new NewCommentLikeNotification(Auth::guard('api')->user()));
            } else {
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-comment-dislike', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-dislike-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];

                Notification::send($userComment, new NewCommentDisLikeNotification(Auth::guard('api')->user()));
            }
        }

        if (!empty($notificationData)) {
            sendNotification($ownerToken, $notificationData);
        }

        ActivityLog('comment', $comment, 'the user ' . $data['status'] . ' the comment', $data['status']);
    }
}
