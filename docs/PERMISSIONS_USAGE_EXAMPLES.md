# 📚 Ejemplos de Uso del Sistema de Permisos

## 🔧 Uso en Controllers

### Ejemplo 1: Verificar permisos con Policies

```php
<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        // Verificar permiso para crear tareas
        $this->authorize('create', [Task::class, $project]);

        // Crear la tarea
        $task = $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => auth()->id(),
            'status_id' => $request->status_id,
        ]);

        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        // Verificar permiso para actualizar
        $this->authorize('update', $task);

        $task->update($request->validated());

        return response()->json($task);
    }

    public function move(Request $request, Task $task)
    {
        // Verificar permiso y dependencias
        $this->authorize('move', [$task, $request->status_id]);

        // Verificar dependencias bloqueantes
        if ($task->hasBlockingDependencies()) {
            return response()->json([
                'error' => 'Cannot move task. Blocking dependencies not completed.',
                'blocking_tasks' => $task->getBlockingDependencies()->pluck('dependsOn.title')
            ], 422);
        }

        $task->update(['status_id' => $request->status_id]);

        return response()->json($task);
    }
}
```

### Ejemplo 2: Usar Gates

```php
<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        // Verificar permiso con Gate
        Gate::authorize('manage-projects');

        $project = Project::create($request->validated());

        return response()->json($project);
    }

    public function update(Request $request, Project $project)
    {
        // Verificar permiso específico del proyecto
        Gate::authorize('manage-projects', $project);

        $project->update($request->validated());

        return response()->json($project);
    }
}
```

### Ejemplo 3: Usar el Trait HasPermissions

```php
<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function show(Project $project)
    {
        $user = auth()->user();

        // Verificar permisos usando el trait
        if (!$user->hasProjectPermission($project, 'projects.view')) {
            abort(403, 'You do not have permission to view this project.');
        }

        // Verificar si es admin
        if ($user->isAdmin()) {
            // Mostrar información adicional
        }

        return view('projects.show', compact('project'));
    }
}
```

---

## 🛡️ Uso en Middleware

### En routes/web.php

```php
use Illuminate\Support\Facades\Route;

// Permiso global
Route::middleware(['auth', 'permission:teams.create'])->group(function () {
    Route::post('/teams', [TeamController::class, 'store']);
});

// Permiso de equipo
Route::middleware(['auth', 'permission:teams.update,team,{team}'])->group(function () {
    Route::put('/teams/{team}', [TeamController::class, 'update']);
});

// Permiso de proyecto
Route::middleware(['auth', 'permission:tasks.create,project,{project}'])->group(function () {
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
});
```

---

## 🎨 Uso en Blade Templates

```blade
{{-- Verificar permiso para ver --}}
@can('view', $project)
    <div class="project-details">
        {{ $project->name }}
    </div>
@endcan

{{-- Verificar permiso para editar --}}
@can('update', $project)
    <a href="{{ route('projects.edit', $project) }}">Editar</a>
@endcan

{{-- Verificar permiso para mover tarea --}}
@can('move', [$task, $newStatusId])
    <button onclick="moveTask({{ $task->id }}, {{ $newStatusId }})">
        Mover Tarea
    </button>
@else
    <span class="text-muted">No se puede mover (dependencias bloqueantes)</span>
@endcan

{{-- Verificar si es Super Admin --}}
@if(auth()->user()->isSuperAdmin())
    <a href="{{ route('admin.dashboard') }}">Panel de Administración</a>
@endif
```

---

## 🔄 Verificación de Dependencias

### En el Controller

```php
public function moveTask(Request $request, Task $task)
{
    // Verificar permiso
    $this->authorize('move', [$task, $request->status_id]);

    // Verificar dependencias bloqueantes
    if ($task->hasBlockingDependencies()) {
        $blockingTasks = $task->getBlockingDependencies()
            ->map(fn($dep) => $dep->dependsOn->title);

        return response()->json([
            'error' => 'Cannot move task',
            'message' => 'This task has blocking dependencies that must be completed first.',
            'blocking_tasks' => $blockingTasks
        ], 422);
    }

    // Mover la tarea
    $task->update(['status_id' => $request->status_id]);

    return response()->json($task);
}
```

### En el Modelo Task

```php
// Verificar si puede moverse
if ($task->canMoveToStatus($newStatusId)) {
    $task->update(['status_id' => $newStatusId]);
} else {
    // Mostrar error
}
```

---

## 👥 Asignar Roles a Usuarios

### Asignar Rol Global

```php
$user = User::find(1);
$user->assignGlobalRole('admin');
```

### Asignar Rol de Equipo

```php
$user = User::find(1);
$team = Team::find(1);
$user->assignTeamRole($team, 'team-admin');
```

### Asignar Rol de Proyecto

```php
$user = User::find(1);
$project = Project::find(1);
$user->assignProjectRole($project, 'project-editor');
```

---

## 🔍 Verificar Permisos Programáticamente

```php
$user = auth()->user();
$project = Project::find(1);

// Verificar permiso global
if ($user->hasPermission('tasks.create')) {
    // Usuario puede crear tareas globalmente
}

// Verificar permiso en equipo
if ($user->hasTeamPermission($project->team, 'projects.create')) {
    // Usuario puede crear proyectos en el equipo
}

// Verificar permiso en proyecto
if ($user->hasProjectPermission($project, 'tasks.create')) {
    // Usuario puede crear tareas en el proyecto
}

// Verificar rol
if ($user->hasRole('admin')) {
    // Usuario es admin
}

if ($user->isSuperAdmin()) {
    // Usuario es super admin
}
```

---

## 🚫 Manejo de Errores

### En Controllers

```php
try {
    $this->authorize('update', $project);
    // Continuar con la acción
} catch (\Illuminate\Auth\Access\AuthorizationException $e) {
    return response()->json([
        'error' => 'Unauthorized',
        'message' => $e->getMessage()
    ], 403);
}
```

### Respuesta Personalizada

```php
public function update(Request $request, Project $project)
{
    if (!auth()->user()->hasProjectPermission($project, 'projects.update')) {
        return response()->json([
            'error' => 'Forbidden',
            'message' => 'You do not have permission to update this project.'
        ], 403);
    }

    // Continuar...
}
```

---

## 📋 Ejemplo Completo: TaskController

```php
<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        // Verificar permiso para ver tareas
        $this->authorize('viewAny', [Task::class, $project]);

        $tasks = $project->tasks()
            ->with(['assignedTo', 'status', 'tags'])
            ->get();

        return response()->json($tasks);
    }

    public function store(Request $request, Project $project)
    {
        // Verificar permiso para crear
        $this->authorize('create', [Task::class, $project]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'integer|min:0|max:3',
            'due_date' => 'nullable|date',
        ]);

        $task = $project->tasks()->create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task)
    {
        // Verificar permiso para actualizar
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'sometimes|exists:task_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'integer|min:0|max:3',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    public function move(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:task_statuses,id',
        ]);

        // Verificar permiso y dependencias
        $this->authorize('move', [$task, $validated['status_id']]);

        // Verificar dependencias bloqueantes
        if ($task->hasBlockingDependencies()) {
            $blockingTasks = $task->getBlockingDependencies()
                ->map(fn($dep) => [
                    'id' => $dep->dependsOn->id,
                    'title' => $dep->dependsOn->title,
                ]);

            return response()->json([
                'error' => 'Cannot move task',
                'message' => 'This task has blocking dependencies that must be completed first.',
                'blocking_tasks' => $blockingTasks
            ], 422);
        }

        $task->update(['status_id' => $validated['status_id']]);

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        // Verificar permiso para eliminar
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
```

---

## 🎯 Mejores Prácticas

1. **Siempre verificar permisos antes de acciones**
   ```php
   $this->authorize('action', $resource);
   ```

2. **Usar Policies para lógica compleja**
   - Mejor que verificar permisos manualmente
   - Centraliza la lógica de autorización

3. **Verificar dependencias antes de mover tareas**
   ```php
   if ($task->hasBlockingDependencies()) {
       // Mostrar error
   }
   ```

4. **Usar el trait HasPermissions para métodos helper**
   ```php
   $user->hasProjectPermission($project, 'tasks.create');
   ```

5. **Super Admin siempre tiene acceso**
   - Verificar primero si es Super Admin
   - Evitar verificaciones innecesarias

---

**Última actualización**: Diciembre 2025

