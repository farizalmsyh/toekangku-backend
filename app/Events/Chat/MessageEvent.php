<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id, $room_id, $user_id, $user_name, $type, $message, $timestamp;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($id, $room_id, $user_id, $user_name, $type, $message, $timestamp)
    {
        $this->id = $id;
        $this->room_id = $room_id;
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->type = $type;
        $this->message = $message;
        $this->timestamp = $timestamp;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('room.'.$this->room_id);
    }

    public function broadcastAs(){
        return 'message-event';
    }

}
