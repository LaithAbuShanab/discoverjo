<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
            'username_for_me'   => $this->user->id == Auth::guard('api')->user()->id ? __('app.you') : $this->user->username,
            'user_image'        => $this->user->getFirstMediaUrl('avatar', 'avatar_app'),
            'message'           => $this->message_txt,
            'message_file'      => $this->getFirstMediaUrl('file', 'file_thumb'),
            'sent_datetime'     => $this->created_at ? Carbon::parse($this->created_at)->format('g:i A') : null,
            'is_read'           => $this->is_read ? true : false
        ];
    }
}
