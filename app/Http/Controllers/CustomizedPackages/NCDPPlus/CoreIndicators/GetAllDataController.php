<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectDataService;
use Carbon\Carbon;

class GetAllDataController extends Controller
{
    protected $projectDataService;

    public function __construct(ProjectDataService $projectDataService)
    {
        $this->projectDataService = $projectDataService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId, string $status, string $condition)
    {
        //
        if ($status == 'activepatients') {
            $data = $this->projectDataService->getAllActive($projectId, $condition)->flatten(2);
        }
        if ($status == 'femalepatients') {
            $data = $this->projectDataService->getAllFemaleActive($projectId, $condition)->flatten(2);
        }
        if ($status == 'lastthreemonths') {

            // dd($condition);

            if ($condition == 'all') {
                // Get basic project info
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
                        $enrollDate = $recordItems->firstWhere('field_name', 'enroll_date_demo')?->value;

                        // Check if the enrollment date is within the last 3 months
                        if ($enrollDate && Carbon::parse($enrollDate)->greaterThanOrEqualTo(Carbon::now()->subMonths(3))) {
                            return [
                                'record' => $recordId,
                                'gender_demo' => $gender,
                                'facility_demo' => $recordItems->firstWhere('field_name', 'facility_demo')?->value,
                                'age_demo' => (int) $age,
                                'enroll_date_demo' => $enrollDate,
                            ];
                        }
                    })->filter(); // Remove null entries

                // dd($data);

            } else {
                $data = $this->projectDataService->getAllNewlyEnrolled($projectId, $condition)->flatten(2);
            }

            // dd($data);

        }
        if ($status == 'lastthreemonthsltfu') {
            $data = $this->projectDataService->getAllNewlyLTFU($projectId, $condition)->flatten(2);
        }
        if ($status == 'mortalitythreemonths') {
            $data = $this->projectDataService->getAllNewlyDeaths($projectId, $condition)->flatten(2);
        }
        if (empty($data) || $data->isEmpty()) {
            return response()->json(['message' => 'No data found.'], 404);
        }

        // dd($data);

        return response()->json(['data' => $data], 200);

    }
}
