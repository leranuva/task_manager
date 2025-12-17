<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use App\Services\PermissionService;

class TeamPolicy
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->permissionService->hasGlobalPermission($user, 'teams.view') ||
               $user->teams()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        // Super Admin puede ver todo
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Owner puede ver su equipo
        if ($team->owner_id === $user->id) {
            return true;
        }

        // Miembro del equipo puede ver
        if ($user->teams()->where('teams.id', $team->id)->exists()) {
            return true;
        }

        return $this->permissionService->hasGlobalPermission($user, 'teams.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->permissionService->hasGlobalPermission($user, 'teams.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        // Owner puede actualizar
        if ($team->owner_id === $user->id) {
            return true;
        }

        // Admin del equipo puede actualizar
        $teamRole = $user->teams()->where('teams.id', $team->id)->first()?->pivot->role;
        if ($teamRole === 'admin') {
            return true;
        }

        return $this->permissionService->hasTeamPermission($user, $team, 'teams.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        // Solo el owner puede eliminar
        if ($team->owner_id === $user->id) {
            return true;
        }

        return $this->permissionService->hasGlobalPermission($user, 'teams.delete') &&
               $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        // Owner puede gestionar miembros
        if ($team->owner_id === $user->id) {
            return true;
        }

        return $this->permissionService->hasTeamPermission($user, $team, 'teams.manage_members');
    }
}
