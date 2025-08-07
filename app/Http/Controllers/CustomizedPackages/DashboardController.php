<?php

namespace App\Http\Controllers\CustomizedPackages;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $customProjectData = DB::table('project_customization')
            ->where('project_id', $projectId)->get();

        return Inertia::render('Customizations/Index',
            [
                'project' => $project,
                'customProjectData' => $customProjectData,
            ]

        );
    }
}
