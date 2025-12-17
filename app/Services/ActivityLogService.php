<?php

namespace App\Services;

use App\Events\ActivityLogged;
use App\Models\ActivityLog;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    /**
     * Registrar una actividad y disparar evento
     */
    public function log(
        string $action,
        Model $subject,
        $user,
        ?Project $project = null,
        ?array $changes = null,
        ?string $description = null
    ): ActivityLog {
        $activity = ActivityLog::create([
            'action' => $action,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'user_id' => $user->id,
            'project_id' => $project?->id ?? $this->getProjectFromSubject($subject),
            'changes' => $changes,
            'description' => $description ?? $this->generateDescription($action, $subject, $user),
        ]);

        // Disparar evento de broadcasting
        broadcast(new ActivityLogged($activity))->toOthers();

        return $activity;
    }

    /**
     * Obtener el proyecto desde el sujeto
     */
    protected function getProjectFromSubject(Model $subject): ?int
    {
        if ($subject instanceof \App\Models\Project) {
            return $subject->id;
        }

        if ($subject instanceof \App\Models\Task) {
            return $subject->project_id;
        }

        if ($subject instanceof \App\Models\Comment) {
            $commentable = $subject->commentable;
            if ($commentable instanceof \App\Models\Task) {
                return $commentable->project_id;
            }
        }

        return null;
    }

    /**
     * Generar descripción automática
     */
    protected function generateDescription(string $action, Model $subject, $user): string
    {
        $subjectName = class_basename($subject);
        $userName = $user->name;

        return match ($action) {
            'created' => "{$userName} creó {$subjectName}",
            'updated' => "{$userName} actualizó {$subjectName}",
            'deleted' => "{$userName} eliminó {$subjectName}",
            'moved' => "{$userName} movió {$subjectName}",
            'assigned' => "{$userName} asignó {$subjectName}",
            default => "{$userName} realizó acción en {$subjectName}",
        };
    }
}

