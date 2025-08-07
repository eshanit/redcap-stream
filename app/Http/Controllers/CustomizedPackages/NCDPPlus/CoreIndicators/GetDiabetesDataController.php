<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Services\ProjectDiabetesDataService;

class GetDiabetesDataController extends Controller
{
    protected $projectDiabetesDataService;

    public function __construct(ProjectDiabetesDataService $projectDiabetesDataService)
    {
        $this->projectDiabetesDataService = $projectDiabetesDataService;
    }

    public function __invoke(int $projectId, string $status, string $condition)
    {
        if ($status == 'activepatients') {
            $data = $this->projectDiabetesDataService->getActiveRespondents($projectId, $condition);
        }
        if ($status == 'femalepatients') {
            $data = $this->projectDiabetesDataService->getActiveFemales($projectId, $condition);
        }
        if ($status == 'lastthreemonths') {
            $data = $this->projectDiabetesDataService->getEnrolledLastThreeMonths($projectId, $condition);
        }
        if ($status == 'lastthreemonthsltfu') {
            $data = $this->projectDiabetesDataService->getNewlyLTFU($projectId, $condition);
        }
        if ($status == 'mortalitythreemonths') {
            $data = $this->projectDiabetesDataService->getRecentDiabetesDeaths($projectId, $condition);
        }
        if (empty($data) || $data->isEmpty()) {
            return response()->json(['message' => 'No data found.'], 404);
        }

        return response()->json(['data' => $data], 200);
    }
}
