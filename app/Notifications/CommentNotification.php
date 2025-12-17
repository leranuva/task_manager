<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $comment;
    public $user;
    public $project;
    public $isMention = false;

    /**
     * Create a new notification instance.
     */
    public function __construct(Comment $comment, $user, $project, bool $isMention = false)
    {
        $this->comment = $comment;
        $this->user = $user;
        $this->project = $project;
        $this->isMention = $isMention;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $commentable = $this->comment->commentable;
        $url = null;

        if ($commentable instanceof \App\Models\Task) {
            $url = route('projects.tasks.show', [$this->project->id, $commentable->id]);
        } elseif ($commentable instanceof \App\Models\Project) {
            $url = route('projects.show', $commentable->id);
        }

        $message = $this->isMention
            ? "{$this->user->name} te mencionó en un comentario"
            : "{$this->user->name} comentó en {$this->getCommentableName()}";

        return (new MailMessage)
            ->subject($message)
            ->line($message)
            ->line("Proyecto: {$this->project->name}")
            ->line("Comentario: " . substr($this->comment->body, 0, 100) . '...')
            ->action('Ver Comentario', $url)
            ->line('Gracias por usar nuestra aplicación!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $commentable = $this->comment->commentable;

        return [
            'type' => 'comment',
            'action' => $this->isMention ? 'mentioned' : 'created',
            'comment_id' => $this->comment->id,
            'comment_body' => substr($this->comment->body, 0, 200),
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'subject_type' => get_class($commentable),
            'subject_id' => $commentable->id,
            'is_mention' => $this->isMention,
        ];
    }

    /**
     * Get commentable name
     */
    protected function getCommentableName(): string
    {
        $commentable = $this->comment->commentable;
        
        if ($commentable instanceof \App\Models\Task) {
            return "la tarea: {$commentable->title}";
        } elseif ($commentable instanceof \App\Models\Project) {
            return "el proyecto: {$commentable->name}";
        }
        
        return 'un elemento';
    }

    /**
     * Get notification type for preferences
     */
    public function getType(): string
    {
        return $this->isMention 
            ? NotificationPreference::TYPE_COMMENT_MENTIONED 
            : NotificationPreference::TYPE_COMMENT_CREATED;
    }
}
