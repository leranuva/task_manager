<?php

namespace App\Traits;

use App\Models\Project;
use App\Models\Team;
use App\Services\PermissionService;

trait HasPermissions
{
    /**
     * Verificar si el usuario es Super Admin
     * Único rol global del sistema
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Verificar si el usuario tiene un rol en un equipo
     */
    public function hasTeamRole(Team $team, string $role): bool
    {
        // Si es owner del equipo, tiene todos los roles
        if ($team->owner_id === $this->id) {
            return true;
        }

        $member = $team->users()->where('users.id', $this->id)->first();
        return $member?->pivot->role === $role;
    }

    /**
     * Verificar si el usuario tiene un rol en un proyecto
     */
    public function hasProjectRole(Project $project, string $role): bool
    {
        // Si es owner del proyecto, tiene todos los roles
        if ($project->owner_id === $this->id) {
            return true;
        }

        // Verificar rol directo en el proyecto
        $member = $project->users()->where('users.id', $this->id)->first();
        if ($member && $member->pivot->role === $role) {
            return true;
        }

        // Si es miembro del equipo, hereda acceso (pero no roles específicos)
        if ($project->team && $project->team->hasMember($this)) {
            // Los miembros del equipo pueden acceder, pero el rol específico se verifica en el proyecto
            return false; // El rol específico debe estar en project_user
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene un permiso en un equipo
     */
    public function hasTeamPermission(Team $team, string $permission): bool
    {
        return app(PermissionService::class)->hasTeamPermission($this, $team, $permission);
    }

    /**
     * Verificar si el usuario tiene un permiso en un proyecto
     */
    public function hasProjectPermission(Project $project, string $permission): bool
    {
        return app(PermissionService::class)->hasProjectPermission($this, $project, $permission);
    }

    /**
     * Obtener el rol del usuario en un equipo
     */
    public function getTeamRole(Team $team): ?string
    {
        if ($team->owner_id === $this->id) {
            return 'owner';
        }

        $member = $team->users()->where('users.id', $this->id)->first();
        return $member?->pivot->role;
    }

    /**
     * Obtener el rol del usuario en un proyecto
     */
    public function getProjectRole(Project $project): ?string
    {
        if ($project->owner_id === $this->id) {
            return 'owner';
        }

        $member = $project->users()->where('users.id', $this->id)->first();
        return $member?->pivot->role;
    }
}
