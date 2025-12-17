<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'default_statuses',
        'default_settings',
        'is_active',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'default_statuses' => 'array',
        'default_settings' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
