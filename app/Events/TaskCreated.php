<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, User $user)
    {
        $this->task = $task->load(['status', 'assignedTo', 'project', 'createdBy']);
        $this->user = $user;
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
        return 'task.created';
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
                'description' => $this->task->description,
                'status_id' => $this->task->status_id,
                'priority' => $this->task->priority,
                'due_date' => $this->task->due_date ? $this->task->due_date->format('Y-m-d') : null,
                'position' => $this->task->position,
                'status' => $this->task->status ? [
                    'id' => $this->task->status->id,
                    'name' => $this->task->status->name,
                    'color' => $this->task->status->color,
                ] : null,
                'assigned_to' => $this->task->assignedTo ? [
                    'id' => $this->task->assignedTo->id,
                    'name' => $this->task->assignedTo->name,
                ] : null,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'project_id' => $this->task->project_id,
        ];
    }
}
