<?php

namespace App\Events;

use App\Models\ActivityLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityLogged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $activity;

    /**
     * Create a new event instance.
     */
    public function __construct(ActivityLog $activity)
    {
        $this->activity = $activity->load(['user', 'subject']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Determinar el canal basado en el tipo de actividad
        $projectId = null;
        
        if ($this->activity->subject_type === 'App\Models\Project') {
            $projectId = $this->activity->subject_id;
        } elseif ($this->activity->subject_type === 'App\Models\Task') {
            $projectId = $this->activity->subject->project_id ?? null;
        } elseif ($this->activity->subject_type === 'App\Models\Comment') {
            $commentable = $this->activity->subject->commentable ?? null;
            if ($commentable && $commentable instanceof \App\Models\Task) {
                $projectId = $commentable->project_id ?? null;
            }
        }

        if ($projectId) {
            return [
                new PresenceChannel('project.' . $projectId),
            ];
        }

        return [];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'activity.logged';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'activity' => [
                'id' => $this->activity->id,
                'description' => $this->activity->description,
                'subject_type' => $this->activity->subject_type,
                'subject_id' => $this->activity->subject_id,
                'user' => $this->activity->user ? [
                    'id' => $this->activity->user->id,
                    'name' => $this->activity->user->name,
                ] : null,
                'created_at' => $this->activity->created_at->toIso8601String(),
            ],
        ];
    }
}
