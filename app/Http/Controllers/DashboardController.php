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
            'projects' => Project::whereIn('project_id', [32, 39, 50])->get(),
        ]);

    }
}
