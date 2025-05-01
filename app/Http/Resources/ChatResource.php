<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'user_id'           => $this->user->id,
            'username'          => $this->user->username,
            'user_image'        => $this->user->getFirstMediaUrl('avatar'),
            'message'           => $this->message_txt,
            'message_file'      => $this->getFirstMediaUrl('file'),
            'sent_datetime'     => $this->sent_datetime
                ? Carbon::parse($this->sent_datetime)->setTimezone('Asia/Amman')->format('g:i A')
                : null,
        ];
    }
}
