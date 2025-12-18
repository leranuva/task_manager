<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;

class PermissionService
{
    /**
     * Verificar si el usuario tiene un permiso en un equipo
     * 
     * Lógica:
     * 1. Super Admin → Acceso total
     * 2. Owner del equipo → Acceso total
     * 3. Rol en el equipo → Según permisos del rol
     * 4. Miembro del equipo → Acceso básico
     */
    public function hasTeamPermission(User $user, Team $team, string $permission): bool
    {
        // Super Admin tiene todos los permisos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Owner siempre tiene permisos
        if ($team->owner_id === $user->id) {
            return true;
        }

        // Verificar rol en el equipo
        $teamRole = $user->teams()
            ->where('teams.id', $team->id)
            ->first()?->pivot->role;

        // Mapeo de roles a permisos (simplificado)
        $rolePermissions = [
            'admin' => ['teams.view', 'teams.update', 'teams.manage_members', 'projects.create', 'projects.view'],
            'member' => ['teams.view', 'projects.view', 'projects.create'],
            'viewer' => ['teams.view', 'projects.view'],
        ];

        if ($teamRole && isset($rolePermissions[$teamRole])) {
            return in_array($permission, $rolePermissions[$teamRole]);
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene un permiso en un proyecto
     * 
     * Lógica:
     * 1. Super Admin → Acceso total
     * 2. Owner del proyecto → Acceso total
     * 3. Permisos del equipo → Herencia
     * 4. Rol en el proyecto → Según permisos del rol
     */
    public function hasProjectPermission(User $user, Project $project, string $permission): bool
    {
        // Super Admin tiene todos los permisos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Owner siempre tiene permisos
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Verificar permisos del equipo (herencia)
        if ($project->team && $this->hasTeamPermission($user, $project->team, $permission)) {
            return true;
        }

        // Verificar rol directo en el proyecto
        $projectRole = $user->projects()
            ->where('projects.id', $project->id)
            ->first()?->pivot->role;

        // Mapeo de roles a permisos
        $rolePermissions = [
            'admin' => ['projects.view', 'projects.update', 'projects.manage_members', 'tasks.create', 'tasks.update', 'tasks.delete'],
            'editor' => ['projects.view', 'tasks.create', 'tasks.update'],
            'viewer' => ['projects.view', 'tasks.view'],
        ];

        if ($projectRole && isset($rolePermissions[$projectRole])) {
            return in_array($permission, $rolePermissions[$projectRole]);
        }

        return false;
    }

    /**
     * Verificar si el usuario puede mover una tarea (verificar dependencias)
     */
    public function canMoveTask(User $user, $task, $newStatusId): bool
    {
        // Verificar permiso básico
        if (!$this->hasProjectPermission($user, $task->project, 'tasks.move')) {
            return false;
        }

        // Verificar dependencias bloqueantes
        $blockingDependencies = $task->dependencies()
            ->where('type', 'blocks')
            ->whereHas('dependsOn', function ($query) {
                $query->where('is_completed', false);
            })
            ->exists();

        if ($blockingDependencies) {
            return false;
        }

        return true;
    }
}
