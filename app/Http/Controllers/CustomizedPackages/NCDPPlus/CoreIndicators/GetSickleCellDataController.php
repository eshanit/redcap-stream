<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Services\ProjectSickleCellDataService;

class GetSickleCellDataController extends Controller
{
    protected $projectSickleCellDataService;

    public function __construct(ProjectSickleCellDataService $projectSickleCellDataService)
    {
        $this->projectSickleCellDataService = $projectSickleCellDataService;
    }

    public function __invoke(int $projectId, string $status, string $condition)
    {
        if ($status == 'activepatients') {
            $data = $this->projectSickleCellDataService->getActiveRespondents($projectId, $condition);
        }
        if ($status == 'femalepatients') {
            $data = $this->projectSickleCellDataService->getActiveFemales($projectId, $condition);
        }
        if ($status == 'lastthreemonths') {
            $data = $this->projectSickleCellDataService->getEnrolledLastThreeMonths($projectId, $condition);
        }
        if ($status == 'lastthreemonthsltfu') {
            $data = $this->projectSickleCellDataService->getNewlyLTFU($projectId, $condition);
        }
        if ($status == 'mortalitythreemonths') {
            $data = $this->projectSickleCellDataService->getRecentSickleCellDeaths($projectId, $condition);
        }

        if (empty($data) || $data->isEmpty()) {
            return response()->json(['message' => 'No data found.'], 404);
        }

        return response()->json(['data' => $data], 200);
    }
}
