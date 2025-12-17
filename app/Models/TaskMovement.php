<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'from_status_id',
        'to_status_id',
        'from_position',
        'to_position',
        'notes',
    ];

    protected $casts = [
        'from_position' => 'integer',
        'to_position' => 'integer',
    ];

    // Relaciones
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'to_status_id');
    }
}
