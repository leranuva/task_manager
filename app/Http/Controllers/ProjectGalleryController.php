<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectGalleryController extends Controller
{
    /**
     * Display the gallery of images for a project
     */
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/Gallery', [
            'project' => $project->load(['team', 'owner']),
        ]);
    }
}
