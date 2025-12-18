<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Events\UserJoinedProject;
use App\Events\ProjectUpdated;
use App\Services\ActivityLogService;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Team;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Project::with(['team', 'owner', 'taskStatuses'])
            ->withCount(['tasks', 'taskStatuses'])
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('users', function ($query) use ($user) {
                      $query->where('users.id', $user->id);
                  })
                  ->orWhereHas('team.users', function ($query) use ($user) {
                      $query->where('users.id', $user->id);
                  });
            });

        // Filtros
        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->where('is_archived', false);
            } elseif ($request->status === 'archived') {
                $query->where('is_archived', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $projects = $query->latest()->paginate(12);

        // Obtener equipos del usuario para el filtro
        $teams = Team::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->orWhere('owner_id', $user->id)->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'teams' => $teams,
            'filters' => $request->only(['team_id', 'status', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        
        // Obtener equipos del usuario
        $teams = Team::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->orWhere('owner_id', $user->id)->get();

        // Obtener plantillas disponibles
        $templates = ProjectTemplate::where('is_active', true)
            ->where(function ($q) {
                $q->where('is_system', true)
                  ->orWhere('created_by', $user->id);
            })
            ->get();

        return Inertia::render('Projects/Create', [
            'teams' => $teams,
            'templates' => $templates,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $user = $request->user();
        $team = Team::findOrFail($request->team_id);

        // Verificar permisos
        $this->authorize('create', [Project::class, $team]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
            'team_id' => $request->team_id,
            'owner_id' => $user->id,
            'color' => $request->color ?? '#3b82f6',
            'icon' => $request->icon,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'is_active' => true,
            'is_archived' => false,
        ]);

        // Si se seleccionó una plantilla, aplicar configuración
        if ($request->template_id) {
            $template = ProjectTemplate::findOrFail($request->template_id);
            $this->applyTemplate($project, $template);
        } else {
            // Crear estados por defecto
            $this->createDefaultStatuses($project);
        }

        // ⚠️ IMPORTANTE: El owner NO se agrega a project_user
        // El owner se gestiona exclusivamente mediante projects.owner_id
        // Solo se almacena en projects.owner_id, no en la tabla pivote

        // Disparar evento de proyecto creado (opcional)
        // broadcast(new ProjectCreated($project, $user))->toOthers();

        return redirect()->route('projects.show', $project)
            ->with('message', 'Proyecto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load([
            'team',
            'owner',
            'users',
            'taskStatuses' => function ($query) {
                $query->orderBy('position');
            },
            'tasks' => function ($query) {
                $query->with(['status', 'assignedTo', 'createdBy'])->latest()->limit(10);
            },
        ]);

        // Estadísticas
        $stats = [
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('is_completed', true)->count(),
            'pending_tasks' => $project->tasks()->where('is_completed', false)->count(),
            'total_members' => $project->users()->count() + 1, // +1 para el owner
            'statuses_count' => $project->taskStatuses()->count(),
        ];

        // Tareas por estado
        $tasksByStatus = $project->taskStatuses()
            ->withCount('tasks')
            ->get()
            ->map(function ($status) {
                return [
                    'name' => $status->name,
                    'color' => $status->color,
                    'count' => $status->tasks_count,
                ];
            });

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'stats' => $stats,
            'tasksByStatus' => $tasksByStatus,
        ]);
    }

    /**
     * Display the Kanban board for the project.
     */
    public function kanban(Project $project)
    {
        $this->authorize('view', $project);

        // Obtener estados ordenados por posición
        $statuses = $project->taskStatuses()
            ->orderBy('position')
            ->get();

        // Obtener todas las tareas agrupadas por estado
        $tasksByStatus = [];
        foreach ($statuses as $status) {
            $tasksByStatus[$status->id] = $project->tasks()
                ->where('status_id', $status->id)
                ->with(['assignedTo', 'createdBy', 'dependencies.dependsOn'])
                ->orderBy('position')
                ->get()
                ->map(function ($task) use ($status) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'priority' => $task->priority,
                        'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                        'is_completed' => $task->is_completed,
                        'status_id' => $status->id,
                        'has_blocking_dependencies' => $task->hasBlockingDependencies(),
                        'assigned_to' => $task->assignedTo ? [
                            'id' => $task->assignedTo->id,
                            'name' => $task->assignedTo->name,
                        ] : null,
                        'created_by' => $task->createdBy ? [
                            'id' => $task->createdBy->id,
                            'name' => $task->createdBy->name,
                        ] : null,
                    ];
                })
                ->values()
                ->toArray();
        }

        // Obtener usuarios del proyecto para asignación
        $users = $project->users()->get()->merge([$project->owner]);

        return Inertia::render('Projects/Kanban', [
            'project' => $project->load(['team', 'owner']),
            'statuses' => $statuses,
            'tasksByStatus' => $tasksByStatus,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $project->load(['team', 'owner', 'users']);

        // Obtener equipos del usuario
        $user = request()->user();
        $teams = Team::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->orWhere('owner_id', $user->id)->get();

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'teams' => $teams,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $oldAttributes = $project->getAttributes();
        $changes = [];

        $project->update($request->validated());

        // Actualizar slug si cambió el nombre
        if ($request->has('name') && $project->name !== $request->name) {
            $project->slug = Str::slug($request->name);
            $project->save();
        }

        // Detectar cambios
        foreach ($request->validated() as $key => $value) {
            if (isset($oldAttributes[$key]) && $oldAttributes[$key] != $value) {
                $changes[$key] = [
                    'old' => $oldAttributes[$key],
                    'new' => $value,
                ];
            }
        }

        // Registrar actividad y disparar evento
        if (!empty($changes)) {
            app(ActivityLogService::class)->log(
                'updated',
                $project->fresh(),
                $request->user(),
                $project,
                $changes
            );
            
            broadcast(new ProjectUpdated($project->fresh(), $request->user(), $changes))->toOthers();
        }

        return redirect()->route('projects.show', $project)
            ->with('message', 'Proyecto actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('message', 'Proyecto eliminado exitosamente.');
    }

    /**
     * Aplicar plantilla a un proyecto
     */
    protected function applyTemplate(Project $project, ProjectTemplate $template)
    {
        // Aplicar estados por defecto de la plantilla
        if ($template->default_statuses) {
            foreach ($template->default_statuses as $index => $status) {
                TaskStatus::create([
                    'name' => $status['name'],
                    'slug' => Str::slug($status['name']),
                    'project_id' => $project->id,
                    'position' => $index,
                    'color' => $status['color'] ?? '#6b7280',
                    'is_default' => $status['is_default'] ?? false,
                    'is_final' => $status['is_final'] ?? false,
                ]);
            }
        } else {
            // Si no hay estados en la plantilla, crear los por defecto
            $this->createDefaultStatuses($project);
        }
    }

    // Gestión de miembros
    public function members(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $members = $project->users()
            ->withPivot('role', 'joined_at')
            ->get()
            ->map(function ($user) use ($project) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'joined_at' => $user->pivot->joined_at,
                    'is_owner' => $project->owner_id === $user->id,
                ];
            });

        // Agregar el owner si no está en la lista
        if (!$members->contains('id', $project->owner_id)) {
            $owner = $project->owner;
            $members->prepend([
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'role' => 'owner',
                'joined_at' => $project->created_at,
                'is_owner' => true,
            ]);
        }

        // Si el proyecto tiene equipo, agregar miembros del equipo que no estén en el proyecto
        if ($project->team) {
            $teamMembers = $project->team->users()
                ->whereNotIn('users.id', $members->pluck('id'))
                ->where('users.id', '!=', $project->owner_id)
                ->withPivot('role')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->pivot->role . ' (heredado del equipo)',
                        'joined_at' => $user->pivot->joined_at,
                        'is_owner' => false,
                        'is_from_team' => true,
                    ];
                });
            
            $members = $members->merge($teamMembers);
        }

        return response()->json($members->values());
    }

    public function addMember(Request $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string', 'in:admin,editor,viewer'],
        ]);

        // ⚠️ IMPORTANTE: El owner NO se almacena en project_user
        // El owner se gestiona exclusivamente mediante projects.owner_id
        if ($request->user_id === $project->owner_id) {
            return back()->withErrors(['user_id' => 'El propietario del proyecto no se agrega como miembro. El owner se gestiona mediante owner_id.']);
        }

        // Verificar que el usuario no esté ya en el proyecto
        if ($project->users()->where('users.id', $request->user_id)->exists()) {
            return back()->withErrors(['user_id' => 'El usuario ya es miembro del proyecto.']);
        }

        $project->users()->attach($request->user_id, [
            'role' => $request->role,
            'joined_at' => now(),
        ]);

        return back()->with('message', 'Miembro agregado exitosamente.');
    }

    public function updateMemberRole(Request $request, Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        // No permitir cambiar el rol del owner
        if ($project->owner_id === $user->id) {
            return back()->withErrors(['role' => 'No se puede cambiar el rol del propietario del proyecto.']);
        }

        $request->validate([
            'role' => ['required', 'string', 'in:admin,editor,viewer'],
        ]);

        $project->users()->updateExistingPivot($user->id, [
            'role' => $request->role,
        ]);

        return back()->with('message', 'Rol actualizado exitosamente.');
    }

    public function removeMember(Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        // No permitir eliminar al owner
        if ($project->owner_id === $user->id) {
            return back()->withErrors(['user' => 'No se puede eliminar al propietario del proyecto.']);
        }

        $project->users()->detach($user->id);

        return back()->with('message', 'Miembro eliminado exitosamente.');
    }

    /**
     * Crear estados por defecto para un proyecto
     */
    protected function createDefaultStatuses(Project $project)
    {
        $defaultStatuses = [
            ['name' => 'Por hacer', 'color' => '#6b7280', 'is_default' => true, 'is_final' => false],
            ['name' => 'En progreso', 'color' => '#3b82f6', 'is_default' => false, 'is_final' => false],
            ['name' => 'En revisión', 'color' => '#f59e0b', 'is_default' => false, 'is_final' => false],
            ['name' => 'Completado', 'color' => '#10b981', 'is_default' => false, 'is_final' => true],
        ];

        foreach ($defaultStatuses as $index => $status) {
            TaskStatus::create([
                'name' => $status['name'],
                'slug' => Str::slug($status['name']),
                'project_id' => $project->id,
                'position' => $index,
                'color' => $status['color'],
                'is_default' => $status['is_default'],
                'is_final' => $status['is_final'],
            ]);
        }
    }
}
