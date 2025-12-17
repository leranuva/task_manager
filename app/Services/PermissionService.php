<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

class PermissionService
{
    /**
     * Verificar si el usuario tiene un permiso global
     */
    public function hasGlobalPermission(User $user, string $permission): bool
    {
        // Super Admin tiene todos los permisos
        if ($this->hasGlobalRole($user, 'super-admin')) {
            return true;
        }

        return $user->roles()
            ->where('scope', 'global')
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    /**
     * Verificar si el usuario tiene un permiso en un equipo
     */
    public function hasTeamPermission(User $user, Team $team, string $permission): bool
    {
        // Verificar rol global primero
        if ($this->hasGlobalPermission($user, $permission)) {
            return true;
        }

        // Verificar si es owner del equipo
        if ($team->owner_id === $user->id) {
            return true;
        }

        // Verificar rol en el equipo
        $teamRole = $user->teams()
            ->where('teams.id', $team->id)
            ->first()?->pivot->role;

        if ($teamRole === 'owner' || $teamRole === 'admin') {
            return true;
        }

        // Verificar permisos del rol del equipo
        return $user->roles()
            ->where('scope', 'team')
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    /**
     * Verificar si el usuario tiene un permiso en un proyecto
     */
    public function hasProjectPermission(User $user, Project $project, string $permission): bool
    {
        // Verificar permiso global
        if ($this->hasGlobalPermission($user, $permission)) {
            return true;
        }

        // Verificar permiso en el equipo
        if ($this->hasTeamPermission($user, $project->team, $permission)) {
            return true;
        }

        // Verificar si es owner del proyecto
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Verificar rol en el proyecto
        $projectRole = $user->projects()
            ->where('projects.id', $project->id)
            ->first()?->pivot->role;

        if ($projectRole === 'owner' || $projectRole === 'admin') {
            return true;
        }

        // Verificar permisos del rol del proyecto
        return $user->roles()
            ->where('scope', 'project')
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })
            ->exists();
    }

    /**
     * Verificar si el usuario tiene un rol global
     */
    public function hasGlobalRole(User $user, string $roleSlug): bool
    {
        return $user->roles()
            ->where('scope', 'global')
            ->where('slug', $roleSlug)
            ->exists();
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

