<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ListOfConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userId = Auth::id();

        $lastMessage = $this->messages->first();

        $otherUser = $this->members
            ->where('user_id', '!=', $userId)
            ->pluck('user')
            ->first();

        $unreadCount = $this->messages()
            ->where('user_id', '!=', $userId)
            ->whereNull('is_read')
            ->count();

        return [
            'conversation_id' => $this->id,
            'sender' => [
                'id' => $otherUser->id ?? null,
                'slug' => $otherUser->slug ?? null,
                'username' => $otherUser->username ?? null,
                'avatar' => $otherUser?->getFirstMediaUrl('avatar', 'avatar_app'),
            ],
            'last_message' => $lastMessage?->message_txt,
            'sent_at' => $lastMessage?->sent_datetime
                ? Carbon::parse($lastMessage->sent_datetime)->format('h:i A')
                : null,
            'unread_count' => $unreadCount,
        ];
    }
}
