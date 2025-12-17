<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'channel',
        'enabled',
        'settings',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'settings' => 'array',
    ];

    // Constantes para tipos de notificación
    const TYPE_TASK_CREATED = 'task.created';
    const TYPE_TASK_UPDATED = 'task.updated';
    const TYPE_TASK_DELETED = 'task.deleted';
    const TYPE_TASK_ASSIGNED = 'task.assigned';
    const TYPE_TASK_MOVED = 'task.moved';
    const TYPE_COMMENT_CREATED = 'comment.created';
    const TYPE_COMMENT_MENTIONED = 'comment.mentioned';
    const TYPE_PROJECT_UPDATED = 'project.updated';
    const TYPE_PROJECT_MEMBER_ADDED = 'project.member_added';

    // Constantes para canales
    const CHANNEL_IN_APP = 'in_app';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_BOTH = 'both';
    const CHANNEL_NONE = 'none';

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
