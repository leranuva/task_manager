<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado para notificaciones de usuario
Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal de presencia para proyectos (muestra usuarios conectados)
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    // Verificar si el usuario tiene acceso al proyecto
    $project = \App\Models\Project::find($projectId);
    
    if (!$project) {
        return false;
    }

    // Owner puede acceder
    if ($project->owner_id === $user->id) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    // Miembro del proyecto puede acceder
    if ($project->users()->where('users.id', $user->id)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    // Miembro del equipo puede acceder
    if ($project->team && $project->team->users()->where('users.id', $user->id)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    return false;
});
