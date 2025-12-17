<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Services\PermissionService;

class CommentPolicy
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, $task = null): bool
    {
        if ($task) {
            return $this->permissionService->hasProjectPermission($user, $task->project, 'comments.view');
        }

        return $this->permissionService->hasGlobalPermission($user, 'comments.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        // Super Admin puede ver todo
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Creador puede ver
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $this->permissionService->hasProjectPermission($user, $comment->task->project, 'comments.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $task = null): bool
    {
        if ($task) {
            return $this->permissionService->hasProjectPermission($user, $task->project, 'comments.create');
        }

        return $this->permissionService->hasGlobalPermission($user, 'comments.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Super Admin puede actualizar
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Solo el creador puede actualizar
        if ($comment->user_id !== $user->id) {
            return false;
        }

        return $this->permissionService->hasProjectPermission($user, $comment->task->project, 'comments.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Super Admin puede eliminar
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Creador puede eliminar
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $this->permissionService->hasProjectPermission($user, $comment->task->project, 'comments.delete');
    }
}
