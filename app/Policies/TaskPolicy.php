<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Services\PermissionService;

class TaskPolicy
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, $project = null): bool
    {
        if ($project) {
            return $this->permissionService->hasProjectPermission($user, $project, 'tasks.view');
        }

        return $this->permissionService->hasGlobalPermission($user, 'tasks.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Super Admin puede ver todo
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Asignado a la tarea puede ver
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // Creador puede ver
        if ($task->created_by === $user->id) {
            return true;
        }

        return $this->permissionService->hasProjectPermission($user, $task->project, 'tasks.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $project = null): bool
    {
        if ($project) {
            return $this->permissionService->hasProjectPermission($user, $project, 'tasks.create');
        }

        return $this->permissionService->hasGlobalPermission($user, 'tasks.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Super Admin puede actualizar todo
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Asignado a la tarea puede actualizar
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // Creador puede actualizar
        if ($task->created_by === $user->id) {
            return true;
        }

        return $this->permissionService->hasProjectPermission($user, $task->project, 'tasks.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Super Admin puede eliminar
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Creador puede eliminar
        if ($task->created_by === $user->id) {
            return true;
        }

        return $this->permissionService->hasProjectPermission($user, $task->project, 'tasks.delete');
    }

    /**
     * Determine whether the user can assign the task.
     */
    public function assign(User $user, Task $task): bool
    {
        return $this->permissionService->hasProjectPermission($user, $task->project, 'tasks.assign');
    }

    /**
     * Determine whether the user can move the task.
     */
    public function move(User $user, Task $task, $newStatusId): bool
    {
        // Verificar permiso básico
        if (!$this->permissionService->hasProjectPermission($user, $task->project, 'tasks.move')) {
            return false;
        }

        // Verificar dependencias bloqueantes
        return $this->permissionService->canMoveTask($user, $task, $newStatusId);
    }
}
