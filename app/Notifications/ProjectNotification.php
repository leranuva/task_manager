<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $project;
    public $action;
    public $user;
    public $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project, string $action, $user, array $data = [])
    {
        $this->project = $project;
        $this->action = $action;
        $this->user = $user;
        $this->data = $data;
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
        $url = route('projects.show', $this->project->id);
        
        $message = match($this->action) {
            'updated' => "{$this->user->name} actualizó el proyecto: {$this->project->name}",
            'member_added' => "Te han agregado al proyecto: {$this->project->name}",
            'member_removed' => "Te han removido del proyecto: {$this->project->name}",
            default => "Nueva actividad en el proyecto: {$this->project->name}",
        };

        return (new MailMessage)
            ->subject($message)
            ->line($message)
            ->action('Ver Proyecto', $url)
            ->line('Gracias por usar nuestra aplicación!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project',
            'action' => $this->action,
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'subject_type' => Project::class,
            'subject_id' => $this->project->id,
            'data' => $this->data,
        ];
    }

    /**
     * Get notification type for preferences
     */
    public function getType(): string
    {
        return "project.{$this->action}";
    }
}
