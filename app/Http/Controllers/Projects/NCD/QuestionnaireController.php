<?php

namespace App\Http\Controllers\Projects\NCD;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMetaData;
use Inertia\Inertia;

class QuestionnaireController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId)
    {
        // Retrieve the project
        $project = Project::where('project_id', $projectId)->firstOrFail();

        return Inertia::render('Projects/NCD/Questions/Index', [
            'project' => $project,
            'projectForms' => $project
                ->project_metadata()
                ->distinct()
                ->get('form_name'),
            'metadata' => ProjectMetadata::where('project_id', $projectId)
                ->orderBy('field_order', 'asc')
                ->get(),
        ]);
    }
}
