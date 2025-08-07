<?php

namespace App\Http\Controllers\Projects\NCD;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProjectDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @params int $projectId
     */
    public function __invoke(int $projectId)
    {
        // Retrieve the project
        $project = Project::where('project_id', $projectId)->firstOrFail();

        // Get the unique count of items in the 'record' column
        $uniqueRecordCount = $project->project_data()->distinct('record')->count('record');

        // Count by 'value' where field_name = 'ncd_gender'
        $genderCounts = $project->project_data()
            ->where('field_name', 'ncd_gender')
            ->groupBy('value')
            ->select('value', DB::raw('count(*) as count'))
            ->pluck('count', 'value');

        // Count by 'value' where field_name = 'ncd_health_facility'
        $healthFacilityCounts = $project->project_data()
            ->where('field_name', 'ncd_health_facility')
            ->groupBy('value')
            ->select('value', DB::raw('count(*) as count'))
            ->pluck('count', 'value');

        // Get the count of respondents who are LTFU based on the latest instance
        $ltfuCount = $project->project_data()
            ->where('field_name', 'ncd_next_review')
            ->whereIn('instance', function ($query) {
                $query->select(DB::raw('MAX(instance)'))
                    ->from('redcap_data3') // Use the correct table name here
                    ->groupBy('record');
            })
            ->where('value', '<=', Carbon::now()->subDays(60)) // Check if the review date is 60 days past
            ->distinct('record') // Ensure we only count unique records
            ->count('record'); // Count distinct records

        return Inertia::render('Projects/NCD/Index', [
            'respondentCount' => $uniqueRecordCount,
            'respondentGender' => $genderCounts,
            'respondentFacility' => $healthFacilityCounts,
            'lostToFollowUp' => $ltfuCount,
            'project' => $project,
        ]);
    }
}
