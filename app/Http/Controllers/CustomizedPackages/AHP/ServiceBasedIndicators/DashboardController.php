<?php

namespace App\Http\Controllers\CustomizedPackages\AHP\ServiceBasedIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId)
    {
        //

        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        //
        return Inertia::render('Customizations/AHP/ServiceBasedIndicators/Index', [
            'project' => $project,
        ]);
    }
}
