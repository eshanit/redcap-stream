<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;

class EnrolledLastThreeMonthsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId)
    {
        //
        // Get basic project info
        $project = Project::query()
            ->select('project_id', 'app_title')
            ->findOrFail($projectId);

        return Inertia::render('Customizations/NCDPPlus/CoreIndicators/EnrolledLastThreeMonths', [
            'project' => $project,
        ]);

    }
}
