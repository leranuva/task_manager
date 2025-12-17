<?php

namespace App\Http\Controllers;

use App\Events\CursorMoved;
use App\Events\UserTyping;
use App\Models\Project;
use Illuminate\Http\Request;

class RealtimeController extends Controller
{
    /**
     * Notificar que el usuario está escribiendo
     */
    public function typing(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $request->validate([
            'context' => ['required', 'string', 'in:task,comment,project'],
            'context_id' => ['required', 'integer'],
            'is_typing' => ['boolean'],
        ]);

        broadcast(new UserTyping(
            $request->user(),
            $project->id,
            $request->context,
            $request->context_id,
            $request->input('is_typing', true)
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Notificar movimiento del cursor
     */
    public function cursor(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $request->validate([
            'context' => ['required', 'string', 'in:kanban,task,comment'],
            'context_id' => ['required', 'integer'],
            'position' => ['required', 'array'],
            'position.x' => ['numeric'],
            'position.y' => ['numeric'],
            'position.column' => ['integer'],
            'position.row' => ['integer'],
        ]);

        broadcast(new CursorMoved(
            $request->user(),
            $project->id,
            $request->context,
            $request->context_id,
            $request->position
        ))->toOthers();

        return response()->json(['success' => true]);
    }
}
