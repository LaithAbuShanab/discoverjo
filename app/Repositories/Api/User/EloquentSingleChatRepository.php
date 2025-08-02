<?php

namespace App\Repositories\Api\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Events\SingleMessageEvent;
use App\Events\SingleMessageSeenEvent;
use App\Http\Resources\ChatResource;
use App\Http\Resources\ListOfConversationResource;
use App\Interfaces\Gateways\Api\User\SingleChatRepositoryInterface;
use App\Models\Conversation;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class EloquentSingleChatRepository implements SingleChatRepositoryInterface
{
    public function createSingleChat($slug)
    {
        $user = Auth::user();
        $host = User::where('slug', $slug)->firstOrFail();

        return DB::transaction(function () use ($user, $host) {
            $conversation = $this->getOrCreateConversation($user->id, $host->id);

            $perPage = 20;
            $messages = Message::where('conversation_id', $conversation->id)
                ->orderByDesc('id')
                ->paginate($perPage);

            if ($messages->isNotEmpty()) {
                Message::where('conversation_id', $conversation->id)
                    ->where('user_id', $host->id)
                    ->whereNull('is_read')
                    ->update(['is_read' => true]);
            }

            return [
                'messages' => ChatResource::collection($messages),
                'conversation_id' => $conversation->id,
                'pagination' => [
                    'next_page_url' => $messages->nextPageUrl(),
                    'prev_page_url' => $messages->previousPageUrl(),
                    'total' => $messages->total(),
                ],
            ];
        });
    }

    public function listConversations()
    {
        $userId = Auth::id();

        $conversations = Conversation::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->whereHas('messages')
            ->with([
                'members.user',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->orderByDesc('updated_at')
            ->get();


        $pagination = [
            'next_page_url' => null,
            'prev_page_url' => null,
            'total' => $conversations->count(),
        ];

        return [
            'conversations' => ListOfConversationResource::collection($conversations),
            'pagination' => $pagination,
        ];
    }

    public function getConversation($conversationId)
    {
        $perPage = 20;
        $userId = Auth::guard('api')->id();

        $messages = Message::where('conversation_id', $conversationId)
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        if ($messages->isNotEmpty()) {
            $unreadCount = Message::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $userId)
                ->whereNull('is_read')
                ->count();

            Message::where('conversation_id', $conversationId)
                ->where('user_id', '!=', $userId)
                ->whereNull('is_read')
                ->update(['is_read' => true]);

            $messages = Message::where('conversation_id', $conversationId)
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            if ($unreadCount > 0) {
                broadcast(new SingleMessageSeenEvent($conversationId))->toOthers();
            }
        }

        $pagination = [
            'next_page_url' => $messages->nextPageUrl(),
            'prev_page_url' => $messages->previousPageUrl(),
            'total' => $messages->total(),
        ];

        return [
            'messages' => ChatResource::collection($messages),
            'conversation_id' => $conversationId,
            'pagination' => $pagination,
        ];
    }

    public function sendMessages($request)
    {
        return DB::transaction(function () use ($request) {
            $authUser = Auth::guard('api')->user();

            // Create and optionally attach file
            $message = $this->createMessage($request, $authUser->id);

            $fileUrl = $message->getFirstMediaUrl('file', 'thumb') ?: null;

            // Sender data
            $sender = User::find($authUser->id);
            $data = [
                'conversation_id'   => $request->conversation_id,
                'username'          => $sender->username,
                'username_for_me'   => __('app.you'),
                'user_image'        => $sender->getFirstMediaUrl('avatar', 'avatar_app'),
                'message'           => $message->message_txt,
                'message_file'      => $fileUrl,
                'sent_datetime'     => Carbon::parse($message->sent_datetime)
                    ->setTimezone('Asia/Amman')
                    ->format('g:i A'),
            ];

            // Receiver data
            $conversation = Conversation::with('members.user')->findOrFail($request->conversation_id);
            $receiver = $conversation->members
                ->where('user_id', '!=', $authUser->id)
                ->first()
                ->user;

            // Notifications
            $tokens = $receiver->DeviceTokenMany->pluck('token')->toArray();
            $notificationData = [
                'notification' => [
                    'title' => __('app.notifications.new-message', [], $receiver->lang),
                    'body'  => __('app.notifications.new-user-message', ['username' => $sender->username], $receiver->lang),
                    'image' => asset('assets/images/logo_eyes_yellow.jpeg'),
                    'sound' => 'default',
                ],
                'data' => [
                    'type'            => 'single_chat',
                    'conversation_id' => $conversation->id,
                    'message_id'      => $message->id,
                    'slug'            => null,
                    'trip_id'         => $conversation->trip_id,
                ],
            ];

            if (!empty($tokens)) {
                sendNotification($tokens, $notificationData);
            }

            // Broadcast the message
            broadcast(new SingleMessageEvent($data))->toOthers();

            return $data;
        });
    }

    private function getOrCreateConversation($userId, $hostId): Conversation
    {
        $conversation = $this->findPrivateConversationBetween($userId, $hostId);

        if ($conversation) {
            return $conversation;
        }

        $conversation = Conversation::create();

        foreach ([$userId, $hostId] as $participantId) {
            GroupMember::create([
                'conversation_id' => $conversation->id,
                'user_id' => $participantId,
                'joined_datetime' => now(),
            ]);
        }

        return $conversation;
    }

    public function findPrivateConversationBetween($userId1, $userId2): ?Conversation
    {
        return Conversation::whereNull('trip_id')
            ->whereHas('members', function (Builder $query) use ($userId1, $userId2) {
                $query->whereIn('user_id', [$userId1, $userId2]);
            }, '=', 2)
            ->whereHas('members', function (Builder $query) {
                $query->select('conversation_id')
                    ->groupBy('conversation_id')
                    ->havingRaw('COUNT(DISTINCT user_id) = 2');
            })
            ->first();
    }

    private function createMessage($request, int $userId): Message
    {
        $message = new Message();
        $message->user_id = $userId;
        $message->conversation_id = $request->conversation_id;
        $message->sent_datetime = now();
        $message->message_txt = $request->message_txt;

        $message->save();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $message->addMedia($file)
                ->usingFileName($filename)
                ->toMediaCollection('file');
        }

        return $message;
    }
}
