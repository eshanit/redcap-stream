<?php

namespace App\Http\Controllers\CustomizedPackages\AHP\ServiceBasedIndicators;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AHP\ProjectService;

class SexAndReproHealthController extends Controller
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
        return inertia('Customizations/AHP/ServiceBasedIndicators/SexualAndReproHealth', [
            'project' => Project::query()->select('project_id', 'app_title')->findOrFail($projectId),
            'stiScreened' => $this->projectService->getSTIScreened($projectId),
            'stiTreated' => $this->projectService->getSTICasesTreated($projectId),
            'contraceptionMethods' => $this->projectService->getContraceptionMethods($projectId),
        ]);
    }
}
