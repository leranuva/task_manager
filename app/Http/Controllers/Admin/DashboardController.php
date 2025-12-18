<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the Super Admin dashboard.
     */
    public function index(Request $request)
    {
        // Solo Super Admin puede acceder
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Solo los Super Administradores pueden acceder a este panel.');
        }

        // Estadísticas generales
        $stats = [
            'total_users' => User::count(),
            'super_admins' => User::where('is_super_admin', true)->count(),
            'regular_users' => User::where('is_super_admin', false)->count(),
            'total_teams' => Team::count(),
            'active_teams' => Team::where('is_active', true)->count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::where('is_active', true)->count(),
            'archived_projects' => Project::where('is_archived', true)->count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::whereHas('status', function ($q) {
                $q->where('is_final', true);
            })->count(),
        ];

        // Usuarios recientes
        $recentUsers = User::latest()
            ->take(10)
            ->get(['id', 'name', 'email', 'is_super_admin', 'created_at']);

        // Equipos más activos (por número de proyectos)
        $mostActiveTeams = Team::withCount('projects')
            ->orderBy('projects_count', 'desc')
            ->take(10)
            ->get(['id', 'name', 'owner_id', 'is_active', 'created_at']);

        // Proyectos más activos (por número de tareas)
        $mostActiveProjects = Project::withCount('tasks')
            ->orderBy('tasks_count', 'desc')
            ->take(10)
            ->get(['id', 'name', 'team_id', 'owner_id', 'is_active', 'created_at']);

        // Usuarios por mes (últimos 12 meses)
        $usersByMonth = User::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Equipos por mes (últimos 12 meses)
        $teamsByMonth = Team::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Proyectos por mes (últimos 12 meses)
        $projectsByMonth = Project::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'mostActiveTeams' => $mostActiveTeams,
            'mostActiveProjects' => $mostActiveProjects,
            'usersByMonth' => $usersByMonth,
            'teamsByMonth' => $teamsByMonth,
            'projectsByMonth' => $projectsByMonth,
        ]);
    }
}
