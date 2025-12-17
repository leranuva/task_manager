<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Notifications\CommentNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Task $task)
    {
        $this->authorize('view', $task);

        $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
            'attachment_ids' => ['nullable', 'array'],
            'attachment_ids.*' => ['exists:file_attachments,id'],
        ]);

        $comment = Comment::create([
            'body' => $request->body,
            'user_id' => $request->user()->id,
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'parent_id' => $request->parent_id,
        ]);

        // Adjuntar archivos si se proporcionaron
        if ($request->has('attachment_ids') && !empty($request->attachment_ids)) {
            // Los archivos ya deben estar subidos previamente
            // Solo actualizamos su attachable para que apunten al comentario
            \App\Models\FileAttachment::whereIn('id', $request->attachment_ids)
                ->where('attachable_type', Task::class)
                ->where('attachable_id', $task->id)
                ->update([
                    'attachable_type' => Comment::class,
                    'attachable_id' => $comment->id,
                ]);
        }

        // Registrar actividad
        app(ActivityLogService::class)->log(
            'created',
            $comment,
            $request->user(),
            $project
        );

        // Obtener usuarios a notificar
        $usersToNotify = $this->getUsersToNotify($task, $request->user(), $comment);

        // Enviar notificaciones
        $notificationService = app(NotificationService::class);
        foreach ($usersToNotify as $user) {
            $isMention = $this->isMentioned($user, $comment->body);
            $notification = new CommentNotification($comment->fresh(), $request->user(), $project, $isMention);
            $notificationService->notify([$user], $notification);
        }

        // Disparar evento de broadcasting
        broadcast(new CommentCreated($comment->fresh(), $request->user(), $project->id))->toOthers();

        return response()->json([
            'success' => true,
            'comment' => $comment->load(['user', 'replies']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Task $task, Comment $comment)
    {
        $this->authorize('update', $comment);

        $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment->update([
            'body' => $request->body,
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Task $task, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comentario eliminado exitosamente.',
        ]);
    }

    /**
     * Obtener usuarios a notificar por un comentario
     */
    protected function getUsersToNotify($commentable, $commenter, $comment): array
    {
        $users = collect();

        // Si es una tarea, notificar a:
        if ($commentable instanceof Task) {
            // Asignado a la tarea
            if ($commentable->assignedTo && $commentable->assignedTo->id !== $commenter->id) {
                $users->push($commentable->assignedTo);
            }

            // Creador de la tarea
            if ($commentable->createdBy && $commentable->createdBy->id !== $commenter->id) {
                $users->push($commentable->createdBy);
            }

            // Otros usuarios que han comentado en la tarea
            $otherCommenters = Comment::where('commentable_type', Task::class)
                ->where('commentable_id', $commentable->id)
                ->where('user_id', '!=', $commenter->id)
                ->with('user')
                ->get()
                ->pluck('user')
                ->unique('id');
            
            $users = $users->merge($otherCommenters);
        }

        // Si es un proyecto, notificar a miembros
        if ($commentable instanceof Project) {
            $members = $commentable->users()->get()->merge([$commentable->owner]);
            $users = $users->merge($members->where('id', '!=', $commenter->id));
        }

        // Detectar menciones (@usuario)
        $mentions = $this->extractMentions($comment->body);
        if (!empty($mentions)) {
            $mentionedUsers = \App\Models\User::whereIn('name', $mentions)
                ->orWhereIn('email', $mentions)
                ->get();
            $users = $users->merge($mentionedUsers);
        }

        return $users->unique('id')->values()->all();
    }

    /**
     * Extraer menciones del texto
     */
    protected function extractMentions(string $text): array
    {
        preg_match_all('/@(\w+)/', $text, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Verificar si un usuario fue mencionado
     */
    protected function isMentioned($user, string $text): bool
    {
        $mentions = $this->extractMentions($text);
        return in_array($user->name, $mentions) || in_array($user->email, $mentions);
    }
}
