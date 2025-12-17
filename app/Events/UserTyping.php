<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $projectId;
    public $context; // 'task', 'comment', 'project', etc.
    public $contextId; // ID del contexto (task_id, comment_id, etc.)
    public $isTyping;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $projectId, $context, $contextId, $isTyping = true)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->context = $context;
        $this->contextId = $contextId;
        $this->isTyping = $isTyping;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('project.' . $this->projectId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.typing';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'context' => $this->context,
            'context_id' => $this->contextId,
            'is_typing' => $this->isTyping,
            'project_id' => $this->projectId,
        ];
    }
}
