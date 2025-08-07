<?php

namespace App\Http\Controllers\CustomizedPackages\AHP\ServiceBasedIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AHP\ProjectService;

class HIVHealthController extends Controller
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
        return inertia('Customizations/AHP/ServiceBasedIndicators/HIVHealth', [
            'project' => Project::query()->select('project_id', 'app_title')->findOrFail($projectId),
            'hivHealthData' => $this->projectService->getHIVlHealthScreenings($projectId),
        ]);
    }
}
