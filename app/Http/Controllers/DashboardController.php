<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        //
        return Inertia::render('Dashboard/Index', [
            'projects' => Project::whereIn('project_id', array_merge(
                config('redcap.legacy_projects', []),
                []
            ))->get(),
            'data6Project' => [
                'project_id' => 0,
                'app_title' => config('redcap.data6_unit.title'),
                'project_name' => 'data6',
                'creation_time' => null,
                'production_time' => null,
                'status' => 'active',
            ],
        ]);

    }
}
