<?php

namespace App\Http\Controllers\CustomizedPackages\NCD\Hba1c;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;

class FacilityHBa1cDataController extends Controller
{
    //
    public function __invoke(int $projectId)
    {

        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        return Inertia::render('Customizations/NCD/Hba1c/FacilityAnalysis', [
            'project' => $project,
        ]);
    }
}
