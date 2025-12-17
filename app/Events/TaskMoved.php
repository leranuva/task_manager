<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $user;
    public $fromStatusId;
    public $toStatusId;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, User $user, $fromStatusId, $toStatusId)
    {
        $this->task = $task->load(['status', 'assignedTo', 'project']);
        $this->user = $user;
        $this->fromStatusId = $fromStatusId;
        $this->toStatusId = $toStatusId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('project.' . $this->task->project_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.moved';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'task' => [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'status_id' => $this->task->status_id,
                'position' => $this->task->position,
                'status' => $this->task->status ? [
                    'id' => $this->task->status->id,
                    'name' => $this->task->status->name,
                    'color' => $this->task->status->color,
                ] : null,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'from_status_id' => $this->fromStatusId,
            'to_status_id' => $this->toStatusId,
            'project_id' => $this->task->project_id,
        ];
    }
}
