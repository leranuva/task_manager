<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Services\PermissionService;

class ProjectPolicy
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->permissionService->hasGlobalPermission($user, 'projects.view') ||
               $user->projects()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // Super Admin puede ver todo
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Owner puede ver
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Miembro del proyecto puede ver
        if ($user->projects()->where('projects.id', $project->id)->exists()) {
            return true;
        }

        // Miembro del equipo puede ver proyectos del equipo
        if ($user->teams()->where('teams.id', $project->team_id)->exists()) {
            return true;
        }

        return $this->permissionService->hasProjectPermission($user, $project, 'projects.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $team = null): bool
    {
        if ($team) {
            return $this->permissionService->hasTeamPermission($user, $team, 'projects.create');
        }

        return $this->permissionService->hasGlobalPermission($user, 'projects.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // Owner puede actualizar
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Admin del proyecto puede actualizar
        $projectRole = $user->projects()->where('projects.id', $project->id)->first()?->pivot->role;
        if ($projectRole === 'admin' || $projectRole === 'owner') {
            return true;
        }

        return $this->permissionService->hasProjectPermission($user, $project, 'projects.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // Solo el owner puede eliminar
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $this->permissionService->hasGlobalPermission($user, 'projects.delete') &&
               $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        // Owner puede gestionar miembros
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $this->permissionService->hasProjectPermission($user, $project, 'projects.manage_members');
    }

    /**
     * Determine whether the user can manage settings.
     */
    public function manageSettings(User $user, Project $project): bool
    {
        // Owner puede gestionar configuración
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $this->permissionService->hasProjectPermission($user, $project, 'projects.manage_settings');
    }
}
