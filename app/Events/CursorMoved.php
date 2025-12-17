<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CursorMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $projectId;
    public $context; // 'kanban', 'task', 'comment', etc.
    public $contextId; // ID del contexto
    public $position; // { x, y } o { column, row } para Kanban

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $projectId, $context, $contextId, $position)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->context = $context;
        $this->contextId = $contextId;
        $this->position = $position;
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
        return 'cursor.moved';
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
            'position' => $this->position,
            'project_id' => $this->projectId,
        ];
    }
}
