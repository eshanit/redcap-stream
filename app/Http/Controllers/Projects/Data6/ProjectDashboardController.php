<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\PatientTimelineService;
use App\Services\ProjectData6Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'serviceTotals' => $data6Service->serviceTotals(),
        ]);
    }

    /**
     * Renders the record's whole history as Inertia props - no separate
     * JSON API/client-side fetch, since (unlike the indicator deep-dives)
     * there's no period filter to refetch against. A record ID like
     * "MUS/2026/00058" contains literal slashes, which would make a
     * `{record}/timeline`-style API route ambiguous to match; embedding
     * the data directly in the page sidesteps that entirely.
     */
    public function showTimeline(string $record, PatientTimelineService $timelineService)
    {
        return Inertia::render('Data6/PatientFlow', [
            'appTitle' => config('redcap.data6_unit.title'),
            'record' => $record,
            'patient' => $timelineService->find($record),
        ]);
    }

    public function report(Request $request, ProjectData6Service $data6Service)
    {
        $validated = $request->validate([
            'project_id' => ['nullable', 'integer', Rule::in($data6Service->data6ProjectIds())],
            'service' => ['nullable', 'string', 'max:100'],
            'facility' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        return response()->json([
            'filters' => $validated,
            'rows' => $data6Service->serviceReport(
                isset($validated['project_id']) ? [(int) $validated['project_id']] : null,
                $validated['service'] ?? null,
                $validated['facility'] ?? null,
                $validated['from'] ?? null,
                $validated['to'] ?? null,
            ),
        ]);
    }
}