<?php

namespace App\Events;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $user;
    public $projectId;

    /**
     * Create a new event instance.
     */
    public function __construct(Comment $comment, User $user, $projectId)
    {
        $this->comment = $comment->load(['user', 'commentable']);
        $this->user = $user;
        $this->projectId = $projectId;
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
        return 'comment.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'comment' => [
                'id' => $this->comment->id,
                'body' => $this->comment->body,
                'commentable_type' => $this->comment->commentable_type,
                'commentable_id' => $this->comment->commentable_id,
                'parent_id' => $this->comment->parent_id,
                'created_at' => $this->comment->created_at->toIso8601String(),
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'project_id' => $this->projectId,
        ];
    }
}
