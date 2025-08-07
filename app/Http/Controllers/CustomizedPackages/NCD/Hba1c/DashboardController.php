<?php

namespace App\Http\Controllers\CustomizedPackages\NCD\Hba1c;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        //
        return Inertia::render('Customizations/NCD/Hba1c/Index', [
            'project' => $project,
        ]);

    }
}
