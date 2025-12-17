<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $taskId;
    public $projectId;
    public $statusId;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct($taskId, $projectId, $statusId, User $user)
    {
        $this->taskId = $taskId;
        $this->projectId = $projectId;
        $this->statusId = $statusId;
        $this->user = $user;
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
        return 'task.deleted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->taskId,
            'status_id' => $this->statusId,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'project_id' => $this->projectId,
        ];
    }
}
