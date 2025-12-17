<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TaskStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'project_id',
        'position',
        'color',
        'is_default',
        'is_final',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_final' => 'boolean',
        'position' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($status) {
            if (empty($status->slug)) {
                $status->slug = Str::slug($status->name);
            }
        });

        static::updating(function ($status) {
            if ($status->isDirty('name') && empty($status->slug)) {
                $status->slug = Str::slug($status->name);
            }
        });
    }

    // Relaciones
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'status_id');
    }
}
