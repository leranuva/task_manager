<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;
    public $action;
    public $user;
    public $project;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, string $action, $user, $project = null)
    {
        $this->task = $task;
        $this->action = $action;
        $this->user = $user;
        $this->project = $project ?? $task->project;
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
        $url = route('projects.tasks.show', [$this->project->id, $this->task->id]);
        
        $message = match($this->action) {
            'created' => "{$this->user->name} creó una nueva tarea: {$this->task->title}",
            'updated' => "{$this->user->name} actualizó la tarea: {$this->task->title}",
            'deleted' => "{$this->user->name} eliminó la tarea: {$this->task->title}",
            'assigned' => "Te han asignado la tarea: {$this->task->title}",
            'moved' => "{$this->user->name} movió la tarea: {$this->task->title}",
            default => "Nueva actividad en la tarea: {$this->task->title}",
        };

        return (new MailMessage)
            ->subject($message)
            ->line($message)
            ->line("Proyecto: {$this->project->name}")
            ->action('Ver Tarea', $url)
            ->line('Gracias por usar nuestra aplicación!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task',
            'action' => $this->action,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'subject_type' => Task::class,
            'subject_id' => $this->task->id,
        ];
    }

    /**
     * Get notification type for preferences
     */
    public function getType(): string
    {
        return "task.{$this->action}";
    }
}
