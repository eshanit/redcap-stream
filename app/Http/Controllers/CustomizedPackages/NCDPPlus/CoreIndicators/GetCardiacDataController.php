<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Services\ProjectCardiacDataService;

class GetCardiacDataController extends Controller
{
    protected $projectCardiacDataService;

    public function __construct(ProjectCardiacDataService $projectCardiacDataService)
    {
        $this->projectCardiacDataService = $projectCardiacDataService;
    }

    public function __invoke(int $projectId, string $status, string $condition)
    {
        if ($status == 'activepatients') {
            $data = $this->projectCardiacDataService->getActiveRespondents($projectId, $condition);
        }
        if ($status == 'femalepatients') {
            $data = $this->projectCardiacDataService->getActiveFemales($projectId, $condition);
        }
        if ($status == 'lastthreemonths') {
            $data = $this->projectCardiacDataService->getEnrolledLastThreeMonths($projectId, $condition);
        }
        if ($status == 'lastthreemonthsltfu') {
            $data = $this->projectCardiacDataService->getNewlyLTFU($projectId, $condition);
        }
        if ($status == 'mortalitythreemonths') {
            $data = $this->projectCardiacDataService->getRecentCardiacDeaths($projectId, $condition);
        }

        if (empty($data) || $data->isEmpty()) {
            return response()->json(['message' => 'No data found.'], 404);
        }

        return response()->json(['data' => $data], 200);
    }
}
