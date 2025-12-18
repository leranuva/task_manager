<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'team_id',
        'owner_id',
        'color',
        'icon',
        'start_date',
        'due_date',
        'is_active',
        'is_archived',
        'template_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'is_active' => 'boolean',
        'is_archived' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
            }
        });
    }

    // Relaciones
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function taskStatuses(): HasMany
    {
        return $this->hasMany(TaskStatus::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectTemplate::class, 'template_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(FileAttachment::class, 'attachable');
    }

    public function invitations(): MorphMany
    {
        return $this->morphMany(Invitation::class, 'invitable');
    }

    // Métodos helper
    public function hasMember(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists() || 
               $this->owner_id === $user->id ||
               ($this->team && $this->team->hasMember($user));
    }

    public function getMemberRole(User $user): ?string
    {
        if ($this->owner_id === $user->id) {
            return 'owner';
        }
        
        $member = $this->users()->where('users.id', $user->id)->first();
        if ($member) {
            return $member->pivot->role;
        }
        
        // Si es miembro del equipo, hereda permisos del equipo
        if ($this->team && $this->team->hasMember($user)) {
            return $this->team->getMemberRole($user);
        }
        
        return null;
    }
}
