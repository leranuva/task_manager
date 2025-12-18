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
        // Super Admin puede ver todo
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Cualquier usuario puede ver sus equipos
        return $user->teams()->exists() || $user->ownedTeams()->exists();
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
        return $team->hasMember($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super Admin puede crear equipos
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Cualquier usuario autenticado puede crear equipos
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        // Super Admin puede actualizar todo
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Owner puede actualizar
        if ($team->owner_id === $user->id) {
            return true;
        }

        // Admin del equipo puede actualizar
        $teamRole = $user->getTeamRole($team);
        return $teamRole === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        // Super Admin puede eliminar
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Solo el owner puede eliminar
        return $team->owner_id === $user->id;
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Team $team): bool
    {
        // Super Admin puede gestionar miembros
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Owner puede gestionar miembros
        if ($team->owner_id === $user->id) {
            return true;
        }

        // Admin del equipo puede gestionar miembros
        $teamRole = $user->getTeamRole($team);
        return $teamRole === 'admin';
    }

    /**
     * Determine whether the user can transfer ownership.
     */
    public function transferOwnership(User $user, Team $team): bool
    {
        // Super Admin puede transferir ownership
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Solo el owner actual puede transferir ownership
        return $team->owner_id === $user->id;
    }

    /**
     * Determine whether the user can archive the team.
     */
    public function archive(User $user, Team $team): bool
    {
        // Super Admin puede archivar
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Solo el owner puede archivar
        return $team->owner_id === $user->id;
    }
}
