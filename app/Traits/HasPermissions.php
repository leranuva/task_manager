<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team;
use App\Services\PermissionService;

trait HasPermissions
{
    /**
     * Verificar si el usuario tiene un permiso global
     */
    public function hasPermission(string $permission): bool
    {
        return app(PermissionService::class)->hasGlobalPermission($this, $permission);
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
     * Verificar si el usuario tiene un rol global
     */
    public function hasRole(string $roleSlug): bool
    {
        return app(PermissionService::class)->hasGlobalRole($this, $roleSlug);
    }

    /**
     * Verificar si el usuario es Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Verificar si el usuario es Admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->isSuperAdmin();
    }

    /**
     * Asignar un rol global al usuario
     */
    public function assignGlobalRole(string $roleSlug, $roleable = null): void
    {
        $role = Role::where('slug', $roleSlug)->where('scope', 'global')->first();
        
        if ($role) {
            $this->roles()->attach($role->id, [
                'roleable_type' => $roleable ? get_class($roleable) : null,
                'roleable_id' => $roleable?->id,
            ]);
        }
    }

    /**
     * Asignar un rol de equipo al usuario
     */
    public function assignTeamRole(Team $team, string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->where('scope', 'team')->first();
        
        if ($role) {
            $this->roles()->attach($role->id, [
                'roleable_type' => Team::class,
                'roleable_id' => $team->id,
            ]);
        }
    }

    /**
     * Asignar un rol de proyecto al usuario
     */
    public function assignProjectRole(Project $project, string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->where('scope', 'project')->first();
        
        if ($role) {
            $this->roles()->attach($role->id, [
                'roleable_type' => Project::class,
                'roleable_id' => $project->id,
            ]);
        }
    }
}

