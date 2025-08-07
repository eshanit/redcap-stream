<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;

class EnrollmentAnalysisController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId)
    {
        // Get basic project info
        $project = Project::query()
            ->select('project_id', 'app_title')
            ->findOrFail($projectId);

        // Get all required data in one query
        $data = $project->project_data()
            ->select('record', 'field_name', 'value')
            ->whereIn('field_name', [
                'gender_demo',
                'facility_demo',
                'age_demo',
                'enroll_date_demo',
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($recordItems, $recordId) {
                // Transform each record's items into a structured format including the record ID
                $gender = $recordItems->firstWhere('field_name', 'gender_demo')?->value;

                if ($gender == 1) {
                    $gender = 'Male';
                } elseif ($gender == 2) {
                    $gender = 'Female';
                }

                $age = $recordItems->firstWhere('field_name', 'age_demo')?->value;

                return [
                    'record' => $recordId,
                    'gender_demo' => $gender,
                    'facility_demo' => $recordItems->firstWhere('field_name', 'facility_demo')?->value,
                    'age_demo' => (int) $age,
                    'enroll_date_demo' => $recordItems->firstWhere('field_name', 'enroll_date_demo')?->value,
                ];
            });

        // Calculate counts from the grouped data
        $uniqueRecordCount = $data->count();

        $genderCounts = $data->pluck('gender_demo')
            ->filter()
            ->countBy();

        $healthFacilityCounts = $data->pluck('facility_demo')
            ->filter()
            ->countBy();

        return Inertia::render('Customizations/NCDPPlus/CoreIndicators/Enrollment', [
            'respondentCount' => $uniqueRecordCount,
            'respondentGender' => $genderCounts,
            'respondentFacility' => $healthFacilityCounts,
            'enrolled' => $data->values(), // Reset keys to sequential numbers
            'lostToFollowUp' => [],
            'project' => $project,
        ]);
    }
}
