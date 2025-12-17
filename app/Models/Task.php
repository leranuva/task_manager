<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'status_id',
        'assigned_to',
        'created_by',
        'priority',
        'due_date',
        'position',
        'custom_fields',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'custom_fields' => 'array',
        'is_completed' => 'boolean',
        'priority' => 'string',
        'position' => 'integer',
    ];

    // Constantes para prioridades
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
        ];
    }

    // Relaciones
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Nuevas relaciones
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
    }

    /**
     * Obtener las tareas de las que depende esta tarea (a través de task_dependencies)
     */
    public function dependsOnTasks()
    {
        return $this->hasMany(TaskDependency::class, 'task_id')
            ->with('dependsOn');
    }

    /**
     * Obtener las tareas que dependen de esta tarea
     */
    public function dependentTasks()
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id')
            ->with('task');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(FileAttachment::class, 'attachable');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(TaskMovement::class)->orderBy('created_at', 'desc');
    }

    /**
     * Verificar si la tarea tiene dependencias bloqueantes sin completar
     */
    public function hasBlockingDependencies(): bool
    {
        return $this->dependencies()
            ->where('type', TaskDependency::TYPE_BLOCKS)
            ->whereHas('dependsOn', function ($query) {
                $query->where('is_completed', false);
            })
            ->exists();
    }

    /**
     * Obtener todas las dependencias bloqueantes
     */
    public function getBlockingDependencies()
    {
        return $this->dependencies()
            ->where('type', 'blocks')
            ->whereHas('dependsOn', function ($query) {
                $query->where('is_completed', false);
            })
            ->with('dependsOn')
            ->get();
    }

    /**
     * Obtener todas las tareas bloqueantes (de las que depende)
     */
    public function getBlockingTasks()
    {
        return $this->dependencies()
            ->where('type', TaskDependency::TYPE_BLOCKS)
            ->with('dependsOn')
            ->get()
            ->map(function ($dependency) {
                return $dependency->dependsOn;
            })
            ->filter(function ($task) {
                return $task && !$task->is_completed;
            })
            ->values();
    }

    /**
     * Verificar si la tarea puede ser movida a un nuevo estado
     */
    public function canMoveToStatus($newStatusId): bool
    {
        // Si tiene dependencias bloqueantes, no puede moverse
        if ($this->hasBlockingDependencies()) {
            return false;
        }

        // Verificar si hay dependencias circulares
        if ($this->hasCircularDependency($newStatusId)) {
            return false;
        }

        return true;
    }

    /**
     * Verificar dependencias circulares
     */
    protected function hasCircularDependency($newStatusId): bool
    {
        // Verificar si mover esta tarea crearía una dependencia circular
        $blockingTasks = $this->getBlockingTasks();
        
        foreach ($blockingTasks as $blockingTask) {
            // Si la tarea bloqueante depende de esta tarea, hay circularidad
            if ($blockingTask->dependsOnTasks()->where('task_id', $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener el color de la prioridad
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => '#6b7280',
            self::PRIORITY_NORMAL => '#3b82f6',
            self::PRIORITY_HIGH => '#f59e0b',
            self::PRIORITY_URGENT => '#ef4444',
            default => '#6b7280',
        };
    }

    /**
     * Accessor para obtener el color de prioridad
     */
    public function getGetPriorityColorAttribute(): string
    {
        return $this->getPriorityColor();
    }

    /**
     * Accessor para verificar si tiene dependencias bloqueantes
     */
    public function getHasBlockingDependenciesAttribute(): bool
    {
        return $this->hasBlockingDependencies();
    }
}
