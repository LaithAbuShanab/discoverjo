<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userLang = Auth::guard('api')->user()->lang ?? 'en';
        $type = class_basename($this->type);

        // Define icon map
        $iconMap = [
            'NewPostFollowersNotification'        => 'speaker.png',
            'AcceptFollowRequestNotification'     => 'correct_sign.png',
            'NewFollowRequestNotification'        => 'new.png',
            'NewWarningUserNotification'          => 'warning.png',
            'AcceptCancelInvitationNotification'  => 'trip.png',
            'AcceptCancelNotification'            => 'trip.png',
            'NewRequestNotification'              => 'trip.png',
            'NewTripNotification'                 => 'trip.png',
        ];

        $engagementTypes = [
            'NewCommentDisLikeNotification',
            'NewCommentLikeNotification',
            'NewCommentNotification',
            'NewPostDisLikeNotification',
            'NewPostLikeNotification',
            'NewReplyDisLikeNotification',
            'NewReplyLikeNotification',
            'NewReplyNotification',
            'NewReviewDisLikeNotification',
            'NewReviewLikeNotification',
        ];

        // Determine the icon file
        $iconFile = $iconMap[$type] ?? (in_array($type, $engagementTypes) ? 'hand_with_star.png' : null);

        return [
            'id'    => $this->id,
            'type'  => $type,
            'title' => $this->data['title_' . $userLang] ??  $this->data['title_en'],
            'body' => $this->data['body_' . $userLang] ?? $this->data['body_en'],
            'options' => $this->data['options'] ?? [],
            'icon'  => $iconFile ? asset('assets/icon/' . $iconFile) : null,
            'is_read' => $this->read_at,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
