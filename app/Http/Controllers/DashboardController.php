<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with KPIs and metrics
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Obtener proyectos del usuario
        $userProjects = Project::where(function ($q) use ($user) {
            $q->where('owner_id', $user->id)
              ->orWhereHas('users', function ($query) use ($user) {
                  $query->where('users.id', $user->id);
              });
        })
        ->where('is_active', true)
        ->where('is_archived', false)
        ->get();

        $projectIds = $userProjects->pluck('id');

        // Métricas principales
        $metrics = [
            'total_tasks' => Task::whereIn('project_id', $projectIds)->count(),
            'pending_tasks' => Task::whereIn('project_id', $projectIds)
                ->where('is_completed', false)
                ->count(),
            'completed_tasks' => Task::whereIn('project_id', $projectIds)
                ->where('is_completed', true)
                ->count(),
            'overdue_tasks' => Task::whereIn('project_id', $projectIds)
                ->where('is_completed', false)
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->count(),
            'active_projects' => $userProjects->count(),
            'tasks_due_today' => Task::whereIn('project_id', $projectIds)
                ->where('is_completed', false)
                ->whereDate('due_date', today())
                ->count(),
            'tasks_due_this_week' => Task::whereIn('project_id', $projectIds)
                ->where('is_completed', false)
                ->whereBetween('due_date', [now(), now()->addWeek()])
                ->count(),
        ];

        // Calcular cumplimiento de fechas
        $totalTasksWithDueDate = Task::whereIn('project_id', $projectIds)
            ->whereNotNull('due_date')
            ->where('is_completed', true)
            ->count();
        
        $completedOnTime = Task::whereIn('project_id', $projectIds)
            ->where('is_completed', true)
            ->whereNotNull('due_date')
            ->whereNotNull('completed_at')
            ->whereRaw('DATE(completed_at) <= DATE(due_date)')
            ->count();

        $onTimeRate = $totalTasksWithDueDate > 0 
            ? round(($completedOnTime / $totalTasksWithDueDate) * 100, 2)
            : 0;

        $metrics['on_time_completion_rate'] = $onTimeRate;

        // Tareas por prioridad
        $tasksByPriority = Task::whereIn('project_id', $projectIds)
            ->where('is_completed', false)
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Tareas por estado
        $tasksByStatus = Task::whereIn('project_id', $projectIds)
            ->where('is_completed', false)
            ->with('status')
            ->get()
            ->groupBy(function ($task) {
                return $task->status ? $task->status->name : 'Sin estado';
            })
            ->map->count()
            ->toArray();

        // Tareas completadas por día (últimos 30 días)
        $completedTasksByDay = Task::whereIn('project_id', $projectIds)
            ->where('is_completed', true)
            ->where('completed_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(completed_at) as date'),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            })
            ->toArray();

        // Proyectos por estado
        $projectsByStatus = [
            'active' => $userProjects->where('is_active', true)->count(),
            'archived' => Project::where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('users', function ($query) use ($user) {
                      $query->where('users.id', $user->id);
                  });
            })->where('is_archived', true)->count(),
        ];

        // Tareas asignadas a mí
        $myTasks = Task::whereIn('project_id', $projectIds)
            ->where('assigned_to', $user->id)
            ->where('is_completed', false)
            ->with(['project', 'status'])
            ->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc')
            ->limit(10)
            ->get();

        // Proyectos recientes
        $recentProjects = $userProjects
            ->sortByDesc('updated_at')
            ->take(5)
            ->values();

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'tasksByPriority' => $tasksByPriority,
            'tasksByStatus' => $tasksByStatus,
            'completedTasksByDay' => $completedTasksByDay,
            'projectsByStatus' => $projectsByStatus,
            'myTasks' => $myTasks,
            'recentProjects' => $recentProjects,
        ]);
    }
}
