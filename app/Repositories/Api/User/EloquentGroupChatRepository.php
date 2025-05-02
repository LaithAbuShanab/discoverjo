<?php

namespace App\Repositories\Api\User;

use App\Events\GroupMessageEvent;
use App\Http\Resources\ChatResource;
use App\Http\Resources\GroupChatMemberResource;
use App\Interfaces\Gateways\Api\User\GroupChatRepositoryInterface;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\Users\chat\NewMessageNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;


class EloquentGroupChatRepository implements GroupChatRepositoryInterface
{
    public function getGroupMessages($conversationId)
    {
        $perPage = 20;
        $messages = Message::where('conversation_id', $conversationId)->orderBy('id', 'desc')->paginate($perPage);
        $messagesArray = $messages->toArray();

        $pagination = [
            'next_page_url' => $messagesArray['next_page_url'],
            'prev_page_url' => $messagesArray['prev_page_url'],
            'total' => $messagesArray['total'],
        ];

        return [
            'messages' => ChatResource::collection($messages),
            'conversation_id' => $conversationId,
            'pagination' => $pagination
        ];
    }

    public function getGroupMembers($conversationId)
    {
        $eloquentConversation = Conversation::find($conversationId);
        return new GroupChatMemberResource($eloquentConversation->load('members'));
    }

    public function sendMessages($request)
    {
        return DB::transaction(function () use ($request) {
            $eloquentMessage = $this->createMessage($request);

            $fileUrl = null;
            if ($request->hasFile('file')) {
                $fileUrl = $eloquentMessage->getFirstMediaUrl('file', 'thumb');
            }

            $user = User::find(Auth::guard('api')->user()->id);
            $data = [
                'conversation_id' => $request->conversation_id,
                'username' => $user->username,
                'username_for_me' => __('app.you'),
                'user_image' => $user->getFirstMediaUrl('avatar', 'avatar_app'),
                'message' => $request->message_txt ?? null,
                'message_file' => $fileUrl,
                'sent_datetime' => Carbon::parse($eloquentMessage->sent_datetime)
                    ->setTimezone('Asia/Amman')
                    ->format('g:i A'),
            ];

            // Send Notification For All Group Members
            $eloquentConversation = Conversation::find($request->conversation_id);
            foreach ($eloquentConversation->members->where('user_id', '!=', Auth::guard('api')->user()->id) as $member) {
                $user = User::find($member->user_id);
                $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
                $receiverLanguage = $user->lang;
                $notificationData = [
                    'notification' => [
                        'title' => Lang::get('app.notifications.new-message', [], $receiverLanguage),
                        'body'  => Lang::get('app.notifications.new-user-message', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'icon'  => asset('assets/icon/new.png'),
                        'sound' => 'default',
                    ],
                    'data' => [
                        'type'            => 'chat',
                        'slug'            => null,
                        'conversation_id' => $request->conversation_id,
                        'trip_id'         => Conversation::find($request->conversation_id)->trip_id,
                        'message_id'      => $eloquentMessage->id
                    ]
                ];
                sendNotification($tokens, $notificationData);
            }

            Broadcast(new GroupMessageEvent($data))->toOthers();

            return $data;
        });
    }


    public function createMessage($request)
    {
        $eloquentMessage = new Message();
        $eloquentMessage->user_id = Auth::guard('api')->user()->id;
        $eloquentMessage->conversation_id = $request->conversation_id;
        $eloquentMessage->sent_datetime = now();

        $eloquentMessage->message_txt = $request->message_txt;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $eloquentMessage->addMedia($file)->usingFileName($filename)->toMediaCollection('file');
        }

        $eloquentMessage->save();

        return $eloquentMessage;
    }
}
