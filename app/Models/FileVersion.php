<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FileVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_attachment_id',
        'version_number',
        'name',
        'original_name',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
        'change_description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected $appends = ['url', 'formatted_size'];

    // Relaciones
    public function fileAttachment(): BelongsTo
    {
        return $this->belongsTo(FileAttachment::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the URL to access the file
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
