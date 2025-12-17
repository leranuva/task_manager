<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activities for a project.
     */
    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $query = ActivityLog::where('project_id', $project->id)
            ->with(['user', 'subject'])
            ->latest();

        // Filtros
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('subject_type') && $request->subject_type) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Paginación
        $activities = $query->paginate($request->get('per_page', 20));

        return Inertia::render('Projects/ActivityLog', [
            'project' => $project->load(['team', 'owner']),
            'activities' => $activities,
            'filters' => $request->only(['action', 'user_id', 'subject_type', 'date_from', 'date_to']),
            'users' => $project->users()->get()->merge([$project->owner]),
        ]);
    }
}
