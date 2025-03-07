<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageEvent implements ShouldBroadcast
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
        return 'group-message';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id'     => $this->data['conversation_id'],
            'user' => [
                'username'        => $this->data['username'],
                'username_for_me' => $this->data['username_for_me'],
                'user_image'      => $this->data['user_image']
            ],
            'message' => [
                'message'        => $this->data['message'],
                'message_file'   => $this->data['message_file'],
                'sent_datetime'  => $this->data['sent_datetime']
            ]
        ];
    }
}
