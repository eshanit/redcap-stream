<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Services\ProjectCLDDataService;

class GetCLDDataController extends Controller
{
    protected $projectCLDDataService;

    public function __construct(ProjectCLDDataService $projectCLDDataService)
    {
        $this->projectCLDDataService = $projectCLDDataService;
    }

    public function __invoke(int $projectId, string $status, string $condition)
    {
        if ($status == 'activepatients') {
            $data = $this->projectCLDDataService->getActiveRespondents($projectId, $condition);
        }
        if ($status == 'femalepatients') {
            $data = $this->projectCLDDataService->getActiveFemales($projectId, $condition);
        }
        if ($status == 'lastthreemonths') {
            $data = $this->projectCLDDataService->getEnrolledLastThreeMonths($projectId, $condition);
        }
        if ($status == 'lastthreemonthsltfu') {
            $data = $this->projectCLDDataService->getNewlyLTFU($projectId, $condition);
        }
        if ($status == 'mortalitythreemonths') {
            $data = $this->projectCLDDataService->getRecentCLDDeaths($projectId, $condition);
        }

        if (empty($data) || $data->isEmpty()) {
            return response()->json(['message' => 'No data found.'], 404);
        }

        return response()->json(['data' => $data], 200);
    }
}
