<?php

namespace App\Http\Controllers\CustomizedPackages\AHP\ServiceBasedIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AHP\ProjectService;

class PrEPHealthController extends Controller
{
     /**
     * Handle the incoming request.
     */
    protected $projecService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }
    public function __invoke(int $projectId)
    {
        //
        return inertia('Customizations/AHP/ServiceBasedIndicators/PrEPHealth', [
            'project' => Project::query()->select('project_id', 'app_title')->findOrFail($projectId),
            'prepHealthData' => $this->projectService->getPrEPData($projectId),
        ]);
    }
}
