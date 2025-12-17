<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileAttachmentRequest;
use App\Models\FileAttachment;
use App\Models\FileVersion;
use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;
use App\Services\ImageCompressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileAttachmentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFileAttachmentRequest $request)
    {
        $file = $request->file('file');
        $attachableType = $request->attachable_type;
        $attachableId = $request->attachable_id;

        // Verificar que el attachable existe y el usuario tiene acceso
        $attachable = $this->getAttachable($attachableType, $attachableId);
        if (!$attachable) {
            return response()->json(['error' => 'Recurso no encontrado'], 404);
        }

        // Verificar permisos
        if (!$this->canAttachFile($request->user(), $attachable)) {
            return response()->json(['error' => 'No tienes permisos para adjuntar archivos'], 403);
        }

        // Generar nombre único para el archivo
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        
        // Determinar la ruta según el tipo
        $folder = $this->getStorageFolder($attachableType);
        $path = $file->storeAs($folder, $fileName, 'public');

        // Comprimir imagen si es necesario
        $compressionService = app(ImageCompressionService::class);
        $compressedPath = $compressionService->compressIfNeeded($path);
        if ($compressedPath) {
            $path = $compressedPath;
            // Actualizar tamaño después de compresión
            $fileSize = Storage::disk('public')->size($path);
        } else {
            $fileSize = $file->getSize();
        }

        // Crear thumbnail si es imagen
        $thumbnailPath = null;
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $thumbnailPath = $compressionService->createThumbnail($path);
        }

        // Obtener el proyecto para la respuesta
        $project = $this->getProjectFromAttachable($attachable);

        // Crear el registro en la base de datos
        $attachment = FileAttachment::create([
            'name' => $fileName,
            'original_name' => $originalName,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $fileSize,
            'attachable_type' => $attachableType,
            'attachable_id' => $attachableId,
            'uploaded_by' => $request->user()->id,
        ]);

        // Crear versión inicial
        FileVersion::create([
            'file_attachment_id' => $attachment->id,
            'version_number' => 'v1',
            'name' => $fileName,
            'original_name' => $originalName,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $fileSize,
            'uploaded_by' => $request->user()->id,
            'change_description' => 'Versión inicial',
        ]);

        return response()->json([
            'success' => true,
            'attachment' => $attachment->load('uploadedBy'),
            'project_id' => $project?->id,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FileAttachment $fileAttachment)
    {
        $this->authorize('view', $fileAttachment);

        return response()->json([
            'attachment' => $fileAttachment->load('uploadedBy'),
        ]);
    }

    /**
     * Download the file.
     */
    public function download(FileAttachment $fileAttachment)
    {
        $this->authorize('view', $fileAttachment);

        if (!Storage::disk('public')->exists($fileAttachment->path)) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }

        return Storage::disk('public')->download(
            $fileAttachment->path,
            $fileAttachment->original_name
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FileAttachment $fileAttachment)
    {
        $this->authorize('delete', $fileAttachment);

        // Eliminar archivo físico
        if (Storage::disk('public')->exists($fileAttachment->path)) {
            Storage::disk('public')->delete($fileAttachment->path);
        }

        // Eliminar thumbnails
        $pathInfo = pathinfo($fileAttachment->path);
        $thumbnailPath = $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        if (Storage::disk('public')->exists($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
        }

        // Eliminar versiones
        foreach ($fileAttachment->versions as $version) {
            if (Storage::disk('public')->exists($version->path)) {
                Storage::disk('public')->delete($version->path);
            }
        }

        // Eliminar registro
        $fileAttachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Archivo eliminado exitosamente.',
        ]);
    }

    /**
     * Search files
     */
    public function search(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        // Buscar archivos de tareas del proyecto
        $taskIds = $project->tasks()->pluck('id');
        $commentIds = Comment::where('commentable_type', Task::class)
            ->whereIn('commentable_id', $taskIds)
            ->pluck('id');

        $query = FileAttachment::where(function ($q) use ($project, $taskIds, $commentIds) {
            // Archivos de tareas
            $q->where(function ($subQ) use ($taskIds) {
                $subQ->where('attachable_type', Task::class)
                    ->whereIn('attachable_id', $taskIds);
            })
            // Archivos del proyecto
            ->orWhere(function ($subQ) use ($project) {
                $subQ->where('attachable_type', Project::class)
                    ->where('attachable_id', $project->id);
            })
            // Archivos de comentarios
            ->orWhere(function ($subQ) use ($commentIds) {
                $subQ->where('attachable_type', Comment::class)
                    ->whereIn('attachable_id', $commentIds);
            });
        });

        // Filtrar por nombre
        if ($request->has('search')) {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        // Filtrar por tipo MIME
        if ($request->has('mime_type')) {
            $query->where('mime_type', 'like', $request->mime_type . '%');
        }

        // Filtrar por tipo de archivo
        if ($request->has('file_type')) {
            switch ($request->file_type) {
                case 'image':
                    $query->where('mime_type', 'like', 'image/%');
                    break;
                case 'pdf':
                    $query->where('mime_type', 'application/pdf');
                    break;
                case 'document':
                    $query->whereIn('mime_type', [
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]);
                    break;
            }
        }

        $files = $query->with('uploadedBy')->latest()->paginate(20);

        return response()->json($files);
    }

    /**
     * Get file versions
     */
    public function versions(FileAttachment $fileAttachment)
    {
        $this->authorize('view', $fileAttachment);

        $versions = $fileAttachment->versions()
            ->with('uploadedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($versions);
    }

    /**
     * Upload new version
     */
    public function uploadVersion(Request $request, FileAttachment $fileAttachment)
    {
        $this->authorize('update', $fileAttachment->attachable);

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'change_description' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $folder = dirname($fileAttachment->path);
        $path = $file->storeAs($folder, $fileName, 'public');

        // Comprimir si es imagen
        $compressionService = app(ImageCompressionService::class);
        $compressedPath = $compressionService->compressIfNeeded($path);
        if ($compressedPath) {
            $path = $compressedPath;
        }

        // Obtener siguiente número de versión
        $lastVersion = $fileAttachment->versions()->latest()->first();
        $versionNumber = $lastVersion 
            ? 'v' . ((int) str_replace('v', '', $lastVersion->version_number) + 1)
            : 'v2';

        // Crear nueva versión
        $version = FileVersion::create([
            'file_attachment_id' => $fileAttachment->id,
            'version_number' => $versionNumber,
            'name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => Storage::disk('public')->size($path),
            'uploaded_by' => $request->user()->id,
            'change_description' => $request->change_description,
        ]);

        // Actualizar archivo principal
        $oldPath = $fileAttachment->path;
        $fileAttachment->update([
            'path' => $path,
            'name' => $fileName,
            'size' => Storage::disk('public')->size($path),
        ]);

        // Guardar versión anterior si no existe
        if ($lastVersion && $lastVersion->path !== $oldPath) {
            // El path anterior ya está guardado en la última versión
        }

        return response()->json([
            'success' => true,
            'version' => $version->load('uploadedBy'),
        ], 201);
    }

    /**
     * Get attachable model
     */
    protected function getAttachable(string $type, int $id)
    {
        return match ($type) {
            'App\Models\Task' => Task::find($id),
            'App\Models\Project' => Project::find($id),
            'App\Models\Comment' => Comment::find($id),
            default => null,
        };
    }

    /**
     * Check if user can attach files
     */
    protected function canAttachFile($user, $attachable): bool
    {
        if ($attachable instanceof Task) {
            return $user->can('view', $attachable);
        }

        if ($attachable instanceof Project) {
            return $user->can('view', $attachable);
        }

        if ($attachable instanceof Comment) {
            return $user->can('view', $attachable);
        }

        return false;
    }

    /**
     * Get storage folder for attachable type
     */
    protected function getStorageFolder(string $type): string
    {
        return match ($type) {
            'App\Models\Task' => 'attachments/tasks',
            'App\Models\Project' => 'attachments/projects',
            'App\Models\Comment' => 'attachments/comments',
            default => 'attachments',
        };
    }

    /**
     * Get project from attachable
     */
    protected function getProjectFromAttachable($attachable): ?Project
    {
        if ($attachable instanceof Task) {
            return $attachable->project;
        }
        
        if ($attachable instanceof Project) {
            return $attachable;
        }
        
        if ($attachable instanceof Comment) {
            $commentable = $attachable->commentable;
            if ($commentable instanceof Task) {
                return $commentable->project;
            }
            if ($commentable instanceof Project) {
                return $commentable;
            }
        }
        
        return null;
    }
}
