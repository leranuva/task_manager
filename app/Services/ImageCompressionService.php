<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageCompressionService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Compress image if it's too large
     */
    public function compressIfNeeded(string $path, int $maxWidth = 1920, int $maxHeight = 1080, int $quality = 85): ?string
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            
            // Check if file exists
            if (!file_exists($fullPath)) {
                return null;
            }

            // Check if it's an image
            $mimeType = mime_content_type($fullPath);
            if (!str_starts_with($mimeType, 'image/')) {
                return null;
            }

            // Load image
            $image = $this->manager->read($fullPath);
            $originalWidth = $image->width();
            $originalHeight = $image->height();

            // Check if compression is needed
            if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                return null; // No compression needed
            }

            // Calculate new dimensions maintaining aspect ratio
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $newWidth = (int) ($originalWidth * $ratio);
            $newHeight = (int) ($originalHeight * $ratio);

            // Resize image
            $image->scale($newWidth, $newHeight);

            // Save compressed image
            $image->toJpeg($quality)->save($fullPath);

            // Get new file size
            $newSize = filesize($fullPath);

            Log::info('Image compressed', [
                'path' => $path,
                'original_size' => $originalWidth . 'x' . $originalHeight,
                'new_size' => $newWidth . 'x' . $newHeight,
                'file_size' => $newSize,
            ]);

            return $path;
        } catch (\Exception $e) {
            Log::error('Error compressing image', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create thumbnail
     */
    public function createThumbnail(string $path, int $width = 300, int $height = 300): ?string
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            
            if (!file_exists($fullPath)) {
                return null;
            }

            $mimeType = mime_content_type($fullPath);
            if (!str_starts_with($mimeType, 'image/')) {
                return null;
            }

            // Generate thumbnail path
            $pathInfo = pathinfo($path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

            // Load and resize
            $image = $this->manager->read($fullPath);
            $image->cover($width, $height);

            // Save thumbnail
            $thumbnailFullPath = Storage::disk('public')->path($thumbnailPath);
            $thumbnailDir = dirname($thumbnailFullPath);
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            $image->toJpeg(85)->save($thumbnailFullPath);

            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::error('Error creating thumbnail', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}

