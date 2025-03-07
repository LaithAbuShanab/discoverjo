<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMemberEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('group-channel.' . $this->data['conversation_id']);
    }

    public function broadcastAs(): string
    {
        return 'group-member';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->data['conversation_id'],
            'action' => $this->data['action'],
            'user' => [
                'id' => $this->data['member']->id,
                'username' => $this->data['member']->username,
                'user_image' => $this->data['member']->getFirstMediaUrl('avatar', 'avatar_app'),
            ]
        ];
    }
}
