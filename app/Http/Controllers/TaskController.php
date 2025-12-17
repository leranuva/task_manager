<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\TaskMovement;
use App\Events\TaskMoved;
use App\Events\TaskUpdated;
use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Notifications\TaskNotification;
use App\Services\ActivityLogService;
use App\Services\ConflictResolutionService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $query = $project->tasks()->with([
            'status',
            'assignedTo',
            'createdBy',
            'dependsOnTasks.dependsOn',
        ]);

        // Filtros
        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $tasks = $query->orderBy('position')->orderBy('created_at', 'desc')->paginate(20);

        // Obtener estados y usuarios del proyecto
        $statuses = $project->taskStatuses()->orderBy('position')->get();
        $users = $project->users()->get()->merge([$project->owner]);

        return Inertia::render('Projects/Tasks/Index', [
            'project' => $project->load(['team', 'owner']),
            'tasks' => $tasks,
            'statuses' => $statuses,
            'users' => $users,
            'filters' => $request->only(['status_id', 'assigned_to', 'priority', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $this->authorize('create', [Task::class, $project]);

        $statuses = $project->taskStatuses()->orderBy('position')->get();
        $users = $project->users()->get()->merge([$project->owner]);
        $tasks = $project->tasks()->where('is_completed', false)->get();

        return Inertia::render('Projects/Tasks/Create', [
            'project' => $project,
            'statuses' => $statuses,
            'users' => $users,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, Project $project)
    {
        $this->authorize('create', [Task::class, $project]);

        DB::beginTransaction();
        try {
            // Obtener la siguiente posición
            $maxPosition = $project->tasks()
                ->where('status_id', $request->status_id)
                ->max('position') ?? -1;
            $position = $request->position ?? ($maxPosition + 1);

            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'project_id' => $project->id,
                'status_id' => $request->status_id,
                'assigned_to' => $request->assigned_to,
                'created_by' => $request->user()->id,
                'priority' => $request->priority ?? Task::PRIORITY_NORMAL,
                'due_date' => $request->due_date,
                'position' => $position,
                'is_completed' => false,
            ]);

            // Crear dependencias si se proporcionaron
            if ($request->has('dependency_ids') && is_array($request->dependency_ids)) {
                foreach ($request->dependency_ids as $dependencyId) {
                    // Verificar que la dependencia pertenece al mismo proyecto
                    $dependencyTask = Task::where('id', $dependencyId)
                        ->where('project_id', $project->id)
                        ->first();

                    if ($dependencyTask && $dependencyTask->id !== $task->id) {
                        TaskDependency::create([
                            'task_id' => $task->id,
                            'depends_on_task_id' => $dependencyTask->id,
                            'type' => TaskDependency::TYPE_BLOCKS,
                        ]);
                    }
                }
            }

            DB::commit();

            $freshTask = $task->fresh();

            // Registrar actividad
            app(ActivityLogService::class)->log(
                'created',
                $freshTask,
                $request->user(),
                $project
            );

            // Enviar notificaciones
            $usersToNotify = $this->getUsersToNotifyForTask($freshTask, $request->user());
            $notificationService = app(NotificationService::class);
            foreach ($usersToNotify as $user) {
                $notification = new TaskNotification($freshTask, 'created', $request->user(), $project);
                $notificationService->notify([$user], $notification);
            }

            // Disparar evento de creación
            broadcast(new TaskCreated($freshTask, $request->user()))->toOthers();

            return redirect()->route('projects.tasks.show', [$project->id, $task->id])
                ->with('message', 'Tarea creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la tarea: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Task $task)
    {
        $this->authorize('view', $task);

        $task->load([
            'project',
            'status',
            'assignedTo',
            'createdBy',
            'dependencies.dependsOn',
            'dependents.task',
            'comments.user',
            'comments.attachments.uploadedBy',
            'tags',
            'attachments.uploadedBy',
        ]);

        // Obtener tareas bloqueantes
        $blockingTasks = $task->getBlockingTasks();

        return Inertia::render('Projects/Tasks/Show', [
            'project' => $project,
            'task' => $task,
            'blockingTasks' => $blockingTasks,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project, Task $task)
    {
        $this->authorize('update', $task);

        $statuses = $project->taskStatuses()->orderBy('position')->get();
        $users = $project->users()->get()->merge([$project->owner]);
        $tasks = $project->tasks()->where('id', '!=', $task->id)->get();

        $task->load(['dependencies.dependsOn']);

        return Inertia::render('Projects/Tasks/Edit', [
            'project' => $project,
            'task' => $task,
            'statuses' => $statuses,
            'users' => $users,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        $this->authorize('update', $task);

        // Verificar conflictos de edición simultánea
        $conflictService = app(ConflictResolutionService::class);
        $validated = $request->validated();
        
        if ($request->has('version')) {
            if ($conflictService->hasConflict($task, $request->input('version'))) {
                $conflictInfo = $conflictService->getConflictInfo($task, $validated);
                $resolved = $conflictService->resolveTaskConflict($task, $validated, $request->input('version'));
                
                // Usar datos resueltos
                $validated = array_merge($validated, $resolved);
                
                // Opcional: retornar información de conflicto al frontend
                // return response()->json([
                //     'conflict' => true,
                //     'conflict_info' => $conflictInfo,
                //     'resolved' => $resolved,
                // ], 409);
            }
        }

        // Si se está cambiando el estado, verificar dependencias bloqueantes
        if (isset($validated['status_id']) && $validated['status_id'] !== $task->status_id) {
            if (!$task->canMoveToStatus($validated['status_id'])) {
                $blockingTasks = $task->getBlockingTasks();
                $blockingTitles = $blockingTasks->pluck('title')->implode(', ');
                
                return back()->withErrors([
                    'status_id' => "No se puede mover la tarea. Tiene dependencias bloqueantes sin completar: {$blockingTitles}",
                ]);
            }
        }

        $oldAttributes = $task->getAttributes();
        $changes = [];
        
        $task->update($validated);
        $task->refresh();
        
        // Detectar cambios después de actualizar
        foreach ($validated as $key => $value) {
            if (isset($oldAttributes[$key]) && $oldAttributes[$key] != $value) {
                $changes[$key] = [
                    'old' => $oldAttributes[$key],
                    'new' => $value,
                ];
            }
        }

        // Si se completó la tarea, actualizar completed_at
        $wasCompleted = $oldAttributes['is_completed'] ?? false;
        if ($request->has('is_completed') && $request->is_completed && !$wasCompleted) {
            $task->update(['completed_at' => now()]);
            $changes['is_completed'] = ['old' => false, 'new' => true];
        } elseif ($request->has('is_completed') && !$request->is_completed && $wasCompleted) {
            $task->update(['completed_at' => null]);
            $changes['is_completed'] = ['old' => true, 'new' => false];
        }

        // Si se asignó la tarea a alguien, enviar notificación
        if (isset($validated['assigned_to']) && $validated['assigned_to'] !== $oldAttributes['assigned_to']) {
            $assignedUser = \App\Models\User::find($validated['assigned_to']);
            if ($assignedUser) {
                $notification = new TaskNotification($task->fresh(), 'assigned', $request->user(), $project);
                app(NotificationService::class)->notify([$assignedUser], $notification);
            }
        }

        // Registrar actividad y enviar notificaciones
        if (!empty($changes)) {
            $freshTask = $task->fresh();
            
            app(ActivityLogService::class)->log(
                'updated',
                $freshTask,
                $request->user(),
                $project,
                $changes
            );

            // Enviar notificaciones
            $usersToNotify = $this->getUsersToNotifyForTask($freshTask, $request->user());
            $notificationService = app(NotificationService::class);
            foreach ($usersToNotify as $user) {
                $notification = new TaskNotification($freshTask, 'updated', $request->user(), $project);
                $notificationService->notify([$user], $notification);
            }
            
            // Disparar evento de actualización
            broadcast(new TaskUpdated($freshTask, $request->user(), $changes))->toOthers();
        }

        return redirect()->route('projects.tasks.show', [$project->id, $task->id])
            ->with('message', 'Tarea actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project, Task $task)
    {
        $this->authorize('delete', $task);

        $taskId = $task->id;
        $statusId = $task->status_id;
        $projectId = $project->id;

        // Registrar actividad antes de eliminar
        app(ActivityLogService::class)->log(
            'deleted',
            $task,
            $request->user(),
            $project
        );

        // Enviar notificaciones antes de eliminar
        $usersToNotify = $this->getUsersToNotifyForTask($task, $request->user());
        $notificationService = app(NotificationService::class);
        foreach ($usersToNotify as $user) {
            $notification = new TaskNotification($task, 'deleted', $request->user(), $project);
            $notificationService->notify([$user], $notification);
        }

        $task->delete();

        // Disparar evento de eliminación
        broadcast(new TaskDeleted($taskId, $projectId, $statusId, $request->user()))->toOthers();

        return redirect()->route('projects.tasks.index', $project->id)
            ->with('message', 'Tarea eliminada exitosamente.');
    }

    /**
     * Obtener usuarios a notificar por una tarea
     */
    protected function getUsersToNotifyForTask(Task $task, $actionUser): array
    {
        $users = collect();

        // Asignado a la tarea
        if ($task->assignedTo && $task->assignedTo->id !== $actionUser->id) {
            $users->push($task->assignedTo);
        }

        // Creador de la tarea
        if ($task->createdBy && $task->createdBy->id !== $actionUser->id) {
            $users->push($task->createdBy);
        }

        // Miembros del proyecto
        $projectMembers = $task->project->users()->get()->merge([$task->project->owner]);
        $users = $users->merge($projectMembers->where('id', '!=', $actionUser->id));

        return $users->unique('id')->values()->all();
    }

    /**
     * Mover tarea a otro estado (con validación de bloqueo)
     */
    public function move(MoveTaskRequest $request, Project $project, Task $task)
    {
        $this->authorize('update', $task);

        DB::beginTransaction();
        try {
            // Verificar si la tarea puede ser movida
            if (!$task->canMoveToStatus($request->status_id)) {
                $blockingTasks = $task->getBlockingTasks();
                
                DB::rollBack();
                return response()->json([
                    'error' => true,
                    'message' => 'No se puede mover la tarea. Tiene dependencias bloqueantes sin completar.',
                    'blocking_tasks' => $blockingTasks->map(function ($t) {
                        return [
                            'id' => $t->id,
                            'title' => $t->title,
                        ];
                    }),
                ], 422);
            }

            $oldStatusId = $task->status_id;
            $newStatusId = $request->status_id;

            // Si cambió de estado, ajustar posiciones
            if ($oldStatusId !== $newStatusId) {
                // Reordenar tareas en el estado anterior
                $project->tasks()
                    ->where('status_id', $oldStatusId)
                    ->where('position', '>', $task->position)
                    ->decrement('position');

                // Ajustar posiciones en el nuevo estado si es necesario
                $position = $request->position ?? 0;
                $project->tasks()
                    ->where('status_id', $newStatusId)
                    ->where('position', '>=', $position)
                    ->where('id', '!=', $task->id)
                    ->increment('position');
            } else {
                // Solo reordenar dentro del mismo estado
                $oldPosition = $task->position;
                $newPosition = $request->position ?? $oldPosition;

                if ($oldPosition !== $newPosition) {
                    if ($oldPosition < $newPosition) {
                        // Mover hacia abajo
                        $project->tasks()
                            ->where('status_id', $oldStatusId)
                            ->where('position', '>', $oldPosition)
                            ->where('position', '<=', $newPosition)
                            ->where('id', '!=', $task->id)
                            ->decrement('position');
                    } else {
                        // Mover hacia arriba
                        $project->tasks()
                            ->where('status_id', $oldStatusId)
                            ->where('position', '>=', $newPosition)
                            ->where('position', '<', $oldPosition)
                            ->where('id', '!=', $task->id)
                            ->increment('position');
                    }
                }
            }

            $oldPosition = $task->position;
            $newPosition = $request->position ?? $oldPosition;

            // Actualizar la tarea
            $task->update([
                'status_id' => $newStatusId,
                'position' => $newPosition,
            ]);

            // Registrar el movimiento
            TaskMovement::create([
                'task_id' => $task->id,
                'user_id' => $request->user()->id,
                'from_status_id' => $oldStatusId !== $newStatusId ? $oldStatusId : null,
                'to_status_id' => $newStatusId,
                'from_position' => $oldStatusId !== $newStatusId ? $oldPosition : null,
                'to_position' => $newPosition,
            ]);

            // Disparar evento de broadcasting
            broadcast(new TaskMoved($task, $request->user(), $oldStatusId, $newStatusId))->toOthers();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarea movida exitosamente.',
                'task' => $task->fresh(['status', 'assignedTo']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error al mover la tarea: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reordenar tareas dentro de un estado
     */
    public function reorder(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.position' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->tasks as $taskData) {
                Task::where('id', $taskData['id'])
                    ->where('project_id', $project->id)
                    ->update(['position' => $taskData['position']]);
            }

            DB::commit();
            return response()->json(['message' => 'Tareas reordenadas exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al reordenar tareas'], 500);
        }
    }

    /**
     * Obtener tareas bloqueantes de una tarea
     */
    public function blocking(Project $project, Task $task)
    {
        $this->authorize('view', $task);

        $blockingTasks = $task->getBlockingTasks();

        return response()->json([
            'blocking_tasks' => $blockingTasks->map(function ($t) {
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'status' => $t->status ? [
                        'id' => $t->status->id,
                        'name' => $t->status->name,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * Obtener historial de movimientos de una tarea
     */
    public function movements(Project $project, Task $task)
    {
        $this->authorize('view', $task);

        $movements = $task->movements()
            ->with(['user', 'fromStatus', 'toStatus'])
            ->latest()
            ->paginate(20);

        return response()->json($movements);
    }
}
