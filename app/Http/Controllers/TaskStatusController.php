<?php

namespace App\Http\Controllers;

use App\Events\TaskStatusUpdated;
use App\Http\Requests\StoreTaskStatusRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Project;
use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TaskStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $statuses = $project->taskStatuses()
            ->orderBy('position')
            ->withCount('tasks')
            ->get();

        return Inertia::render('Projects/Statuses/Index', [
            'project' => $project->load(['team', 'owner']),
            'statuses' => $statuses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $this->authorize('manageSettings', $project);

        return Inertia::render('Projects/Statuses/Create', [
            'project' => $project,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskStatusRequest $request, Project $project)
    {
        $this->authorize('manageSettings', $project);

        // Obtener la siguiente posición
        $maxPosition = $project->taskStatuses()->max('position') ?? -1;
        $position = $request->position ?? ($maxPosition + 1);

        // Si hay un estado por defecto y este es el nuevo por defecto, quitar el anterior
        if ($request->is_default) {
            $project->taskStatuses()->update(['is_default' => false]);
        }

        $status = TaskStatus::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'project_id' => $project->id,
            'color' => $request->color,
            'position' => $position,
            'is_default' => $request->is_default ?? false,
            'is_final' => $request->is_final ?? false,
        ]);

        return redirect()->route('projects.statuses.index', $project)
            ->with('message', 'Estado creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project, TaskStatus $taskStatus)
    {
        $this->authorize('manageSettings', $project);

        return Inertia::render('Projects/Statuses/Edit', [
            'project' => $project,
            'status' => $taskStatus,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskStatusRequest $request, Project $project, TaskStatus $taskStatus)
    {
        $this->authorize('manageSettings', $project);

        // Si se está marcando como por defecto, quitar el anterior
        if ($request->is_default && !$taskStatus->is_default) {
            $project->taskStatuses()->where('id', '!=', $taskStatus->id)->update(['is_default' => false]);
        }

        $taskStatus->update([
            'name' => $request->name ?? $taskStatus->name,
            'slug' => $request->name ? Str::slug($request->name) : $taskStatus->slug,
            'color' => $request->color ?? $taskStatus->color,
            'position' => $request->position ?? $taskStatus->position,
            'is_default' => $request->has('is_default') ? $request->is_default : $taskStatus->is_default,
            'is_final' => $request->has('is_final') ? $request->is_final : $taskStatus->is_final,
        ]);

        // Disparar evento de actualización
        broadcast(new TaskStatusUpdated($taskStatus->fresh(), $project->id, $request->user()))->toOthers();

        return redirect()->route('projects.statuses.index', $project)
            ->with('message', 'Estado actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, TaskStatus $taskStatus)
    {
        $this->authorize('manageSettings', $project);

        // No permitir eliminar si tiene tareas asignadas
        if ($taskStatus->tasks()->count() > 0) {
            return redirect()->route('projects.statuses.index', $project)
                ->with('error', 'No se puede eliminar un estado que tiene tareas asignadas.');
        }

        $taskStatus->delete();

        return redirect()->route('projects.statuses.index', $project)
            ->with('message', 'Estado eliminado exitosamente.');
    }

    /**
     * Reordenar los estados
     */
    public function reorder(Request $request, Project $project)
    {
        $this->authorize('manageSettings', $project);

        $request->validate([
            'statuses' => 'required|array',
            'statuses.*.id' => 'required|exists:task_statuses,id',
            'statuses.*.position' => 'required|integer',
        ]);

        foreach ($request->statuses as $statusData) {
            TaskStatus::where('id', $statusData['id'])
                ->where('project_id', $project->id)
                ->update(['position' => $statusData['position']]);
        }

        return response()->json(['message' => 'Estados reordenados exitosamente.']);
    }
}
