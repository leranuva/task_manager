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
        // Super Admin puede ver todo
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Cualquier usuario puede ver sus proyectos
        return $user->projects()->exists() || 
               $user->ownedProjects()->exists() ||
               $user->teams()->has('projects')->exists();
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
        if ($project->hasMember($user)) {
            return true;
        }

        // Miembro del equipo puede ver proyectos del equipo
        if ($project->team && $project->team->hasMember($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $team = null): bool
    {
        // Super Admin puede crear proyectos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Si hay un equipo, verificar permisos en el equipo
        if ($team) {
            return $this->permissionService->hasTeamPermission($user, $team, 'projects.create');
        }

        // Cualquier usuario autenticado puede crear proyectos
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // Super Admin puede actualizar todo
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Owner puede actualizar
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Admin del proyecto puede actualizar
        $projectRole = $user->getProjectRole($project);
        if ($projectRole === 'admin') {
            return true;
        }

        // Verificar permisos del equipo
        if ($project->team) {
            return $this->permissionService->hasTeamPermission($user, $project->team, 'projects.update');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // Super Admin puede eliminar
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Solo el owner puede eliminar
        return $project->owner_id === $user->id;
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        // Super Admin puede gestionar miembros
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Owner puede gestionar miembros
        if ($project->owner_id === $user->id) {
            return true;
        }

        // Admin del proyecto puede gestionar miembros
        $projectRole = $user->getProjectRole($project);
        return $projectRole === 'admin';
    }

    /**
     * Determine whether the user can manage settings.
     */
    public function manageSettings(User $user, Project $project): bool
    {
        // Super Admin puede gestionar configuración
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Owner puede gestionar configuración
        return $project->owner_id === $user->id;
    }

    /**
     * Determine whether the user can transfer ownership.
     */
    public function transferOwnership(User $user, Project $project): bool
    {
        // Super Admin puede transferir ownership
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Solo el owner actual puede transferir ownership
        return $project->owner_id === $user->id;
    }

    /**
     * Determine whether the user can archive the project.
     */
    public function archive(User $user, Project $project): bool
    {
        // Super Admin puede archivar
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Solo el owner puede archivar
        return $project->owner_id === $user->id;
    }
}
