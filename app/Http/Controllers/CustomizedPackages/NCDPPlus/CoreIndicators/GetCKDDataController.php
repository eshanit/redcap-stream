<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Services\ProjectCKDDataService;

class GetCKDDataController extends Controller
{
    protected $projectCKDDataService;

    public function __construct(ProjectCKDDataService $projectCKDDataService)
    {
        $this->projectCKDDataService = $projectCKDDataService;
    }

    public function __invoke(int $projectId, string $status, string $condition)
    {
        if ($status == 'activepatients') {
            $data = $this->projectCKDDataService->getActiveRespondents($projectId, $condition);
        }
        if ($status == 'femalepatients') {
            $data = $this->projectCKDDataService->getActiveFemales($projectId, $condition);
        }
        if ($status == 'lastthreemonths') {
            $data = $this->projectCKDDataService->getEnrolledLastThreeMonths($projectId, $condition);
        }
        if ($status == 'lastthreemonthsltfu') {
            $data = $this->projectCKDDataService->getNewlyLTFU($projectId, $condition);
        }
        if ($status == 'mortalitythreemonths') {
            $data = $this->projectCKDDataService->getRecentCKDDeaths($projectId, $condition);
        }

        if (empty($data) || $data->isEmpty()) {
            return response()->json(['message' => 'No data found.'], 404);
        }

        return response()->json(['data' => $data], 200);
    }
}
