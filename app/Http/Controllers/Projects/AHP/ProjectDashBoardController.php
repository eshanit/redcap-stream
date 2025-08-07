<?php

namespace App\Http\Controllers\Projects\AHP;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProjectDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId)
    {
        //
        // Retrieve the project
        $project = Project::where('project_id', $projectId)->firstOrFail();

        // Get the unique count of items in the 'record' column
        $uniqueRecordCount = $project->project_data()->distinct('record')->count('record');

        $genderCounts = $project->project_data()
            ->where('field_name', 'demog_gender')
            ->groupBy('value')
            ->select('value', DB::raw('count(*) as count'))
            ->pluck('count', 'value');

        // Count by 'value' where field_name = 'ncd_health_facility'
        $healthFacilityCounts = $project->project_data()
            ->where('field_name', 'demog_facility')
            ->groupBy('value')
            ->select('value', DB::raw('count(*) as count'))
            ->pluck('count', 'value');

        $ltfuCount = [];

        return Inertia::render('Projects/NCD/Index', [
            'respondentCount' => $uniqueRecordCount,
            'respondentGender' => $genderCounts,
            'respondentFacility' => $healthFacilityCounts,
            'lostToFollowUp' => $ltfuCount,
            'project' => $project,
        ]);

    }
}
