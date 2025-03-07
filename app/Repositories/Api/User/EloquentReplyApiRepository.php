<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Interfaces\Gateways\Api\User\ReplyApiRepositoryInterface;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\User;
use App\Notifications\Users\post\NewCommentNotification;
use App\Notifications\Users\post\NewReplyDisLikeNotification;
use App\Notifications\Users\post\NewReplyLikeNotification;
use App\Notifications\Users\post\NewReplyNotification;
use App\Pipelines\ContentFilters\ContentFilter;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;


class EloquentReplyApiRepository implements ReplyApiRepositoryInterface
{
    public function createReply($data)
    {
        $filteredContent = app(Pipeline::class)
            ->send($data['content'])
            ->through([
                ContentFilter::class,
            ])
            ->thenReturn();

        $data['content'] = $filteredContent;

        $reply = Reply::create($data);
        $commentUser = Comment::find($data['comment_id'])->user;

        // To Save Notification In Database
        Notification::send($commentUser, new NewReplyNotification(Auth::guard('api')->user()));

        // To Send Notification To Owner Using Firebase Cloud Messaging
        $ownerToken = $commentUser->DeviceToken->token;
        $receiverLanguage = $commentUser->lang;
        $notificationData = [
            'title' => Lang::get('app.notifications.new-reply', [], $receiverLanguage),
            'body' => Lang::get('app.notifications.new-user-reply-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
            'sound' => 'default',
        ];
        sendNotification($ownerToken, $notificationData);
        return $reply;
    }

    public function updateReply($data)
    {
        $filteredContent = app(Pipeline::class)
            ->send($data['content'])
            ->through([
                ContentFilter::class,
            ])
            ->thenReturn();

        $data['content'] = $filteredContent;
        $reply = Reply::find($data['reply_id']);
        $reply->update([
            'content' => $data['content']
        ]);
        return $reply;

    }

    public function deleteReply($id)
    {
        Reply::find($id)->delete();
    }

    public function replyLike($request)
    {
        $reply = Reply::find($request->reply_id);
        $status = $request->status == "like" ? '1' : '0';
        $comment = Comment::find($reply->comment_id);
        $userReply = User::find($comment->user_id);
        $ownerToken = $userReply->DeviceToken->token;
        $receiverLanguage = $userReply->lang;
        $notificationData = [];

        $existingLike = $reply->likes()->where('user_id', Auth::guard('api')->user()->id)->first();

        if ($existingLike) {
            if ($existingLike->status != $status) {
                $existingLike->update(['status' => $status]);

                if ($request->status == "like") {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-reply-like', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-like-in-reply', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];
                    Notification::send($userReply, new NewReplyLikeNotification(Auth::guard('api')->user()));

                } else {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-reply-dislike', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-dislike-in-reply', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];

                    Notification::send($userReply, new NewReplyDisLikeNotification(Auth::guard('api')->user()));
                }
            } else {
                $existingLike->delete();
            }
        } else {
            $reply->likes()->create([
                'user_id' => Auth::guard('api')->user()->id,
                'status' => $status,
            ]);

            if ($request->status == "like") {
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-reply-like', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-like-in-reply', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];
                Notification::send($userReply, new NewReplyLikeNotification(Auth::guard('api')->user()));

            } else {
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-comment-dislike', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-dislike-in-comment', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];

                Notification::send($userReply, new NewReplyDisLikeNotification(Auth::guard('api')->user()));
            }

        }
        sendNotification($ownerToken, $notificationData);
    }


}
