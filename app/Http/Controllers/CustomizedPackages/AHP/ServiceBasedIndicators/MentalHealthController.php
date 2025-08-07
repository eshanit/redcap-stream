<?php

namespace App\Http\Controllers\CustomizedPackages\AHP\ServiceBasedIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AHP\ProjectService;

class MentalHealthController extends Controller
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
        return inertia('Customizations/AHP/ServiceBasedIndicators/MentalHealth', [
            'project' => Project::query()->select('project_id', 'app_title')->findOrFail($projectId),
            'mentalHealthData' => $this->projectService->getMentalHealthScreenings($projectId),
        ]);
    }
}
