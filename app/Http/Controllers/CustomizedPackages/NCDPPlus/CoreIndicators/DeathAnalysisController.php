<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectDataService;
use Inertia\Inertia;

class DeathAnalysisController extends Controller
{
    protected $projectDataService;

    public function __construct(ProjectDataService $projectDataService)
    {
        $this->projectDataService = $projectDataService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId)
    {
        //
        // Get basic project info
        $project = Project::query()
            ->select('project_id', 'app_title')
            ->findOrFail($projectId);

        $deathStatistics = $this->projectDataService->getDeathStatistics($projectId);

        return Inertia::render('Customizations/NCDPPlus/CoreIndicators/DeathAnalysis', [
            'project' => $project,
            'deathStatistics' => $deathStatistics,
        ]);

    }
}
