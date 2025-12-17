<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'depends_on_task_id',
        'type',
    ];

    // Relaciones
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }

    // Constantes para tipos de dependencia
    public const TYPE_BLOCKS = 'blocks';
    public const TYPE_RELATES_TO = 'relates_to';
    public const TYPE_DUPLICATES = 'duplicates';

    public static function getTypes(): array
    {
        return [
            self::TYPE_BLOCKS => 'Bloquea',
            self::TYPE_RELATES_TO => 'Relacionado con',
            self::TYPE_DUPLICATES => 'Duplica',
        ];
    }
}
