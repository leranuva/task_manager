<?php

namespace App\Policies;

use App\Models\FileAttachment;
use App\Models\User;

class FileAttachmentPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FileAttachment $fileAttachment): bool
    {
        $attachable = $fileAttachment->attachable;
        
        if ($attachable instanceof \App\Models\Task) {
            return $user->can('view', $attachable);
        }
        
        if ($attachable instanceof \App\Models\Project) {
            return $user->can('view', $attachable);
        }
        
        if ($attachable instanceof \App\Models\Comment) {
            return $user->can('view', $attachable);
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FileAttachment $fileAttachment): bool
    {
        // El usuario que subió el archivo puede eliminarlo
        if ($fileAttachment->uploaded_by === $user->id) {
            return true;
        }
        
        // O si tiene permisos de administración en el recurso
        $attachable = $fileAttachment->attachable;
        
        if ($attachable instanceof \App\Models\Task) {
            return $user->can('update', $attachable);
        }
        
        if ($attachable instanceof \App\Models\Project) {
            return $user->can('update', $attachable);
        }
        
        if ($attachable instanceof \App\Models\Comment) {
            return $user->can('update', $attachable);
        }
        
        return false;
    }
}
