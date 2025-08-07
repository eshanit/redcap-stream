<?php

namespace App\Http\Controllers\CustomizedPackages\AHP\ServiceBasedIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AHP\ProjectService;

class MaternalHealthController extends Controller
{
    protected $projecService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(int $projectId)
    {
        //
        return inertia('Customizations/AHP/ServiceBasedIndicators/MaternalHealth', [
            'project' => Project::query()->select('project_id', 'app_title')->findOrFail($projectId),
            'ancRegistrants' => $this->projectService->getANCRegistrants($projectId),
            'ancFirstBookings' => $this->projectService->getANCFirstBookings($projectId),
            'ancVisits' => $this->projectService->getANCVisits($projectId),
            'pncVisits' => $this->projectService->getPNCVisits($projectId),
        ]);

    }
}
