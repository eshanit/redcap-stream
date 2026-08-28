<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\ProjectData6Service;
use Inertia\Inertia;

class ProjectDashboardController extends Controller
{
    public function __invoke(ProjectData6Service $data6Service)
    {
        $projectId = request()->route('project_id');
        $projectId = $projectId === null ? null : (int) $projectId;

        if ($projectId !== null) {
            abort_unless(in_array($projectId, $data6Service->data6ProjectIds(), true), 404);
        }

        $project = [
            'project_id' => $projectId,
            'app_title' => config('redcap.data6_unit.title'),
            'project_name' => 'data6',
        ];

        return Inertia::render('Data6/Index', [
            'project' => $project,
            'recordCount' => $data6Service->uniqueRecordCount(),
            'recordsByProject' => $data6Service->recordsByProject(),
        ]);
    }
}