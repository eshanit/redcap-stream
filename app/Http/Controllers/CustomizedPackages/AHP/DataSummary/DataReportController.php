<?php

namespace App\Http\Controllers\CustomizedPackages\AHP\DataSummary;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectDataService;
use Inertia\Inertia;

class DataReportController extends Controller
{
    private $projectDataService;

    public function __construct(ProjectDataService $projectDataService)
    {
        $this->projectDataService = $projectDataService;
    }

    /**
     * Handle the incoming request.
     */
    // public function __invoke(int $projectId)
    // {
    //     $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);
        
    //     $metrics = $this->projectDataService->getMetricsWithFacilityBreakdown($project);
    //     $respondentCount = $this->projectDataService->getTotalRespondents($project);
    //     $facilityBreakdown = $this->projectDataService->getFacilityBreakdown($project);

    //     return Inertia::render('Customizations/AHP/DataSummary/Index', [
    //         'project' => $project,
    //         'respondentCount' => $respondentCount,
    //         'facilityBreakdown' => $facilityBreakdown,
    //         'metrics' => $metrics,
    //     ]);
    // }

    public function __invoke(int $projectId)
{
    $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);
    
    $metrics = $this->projectDataService->getAllMetricsWithFacilityBreakdown($projectId);
    $respondentCount = $this->projectDataService->getTotalRespondents($project);
    $facilityBreakdown = $this->projectDataService->getFacilityBreakdown($project);

    return Inertia::render('Customizations/AHP/DataSummary/Index', [
        'project' => $project,
        'respondentCount' => $respondentCount,
        'facilityBreakdown' => $facilityBreakdown,
        'metrics' => $metrics,
    ]);
}
}