<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Models\Data6Patient;
use App\Models\Data6SourceRecord;
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
        ]);
    }

    public function timeline(Data6Patient $patient, ProjectData6Service $data6Service)
    {
        return response()->json([
            'patient_id' => $patient->id,
            'source_records' => $patient->sourceRecords()->get([
                'data6_source_records.id',
                'project_id',
                'redcap_record',
            ]),
            'encounters' => $data6Service->timeline($patient),
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

    public function linkSourceRecord(
        Request $request,
        Data6Patient $patient,
        Data6SourceRecord $sourceRecord,
        ProjectData6Service $data6Service,
    ) {
        $validated = $request->validate([
            'match_method' => ['required', 'in:confirmed_identifier,manual_review'],
            'match_confidence' => ['nullable', 'numeric', 'between:0,1'],
        ]);

        $data6Service->linkSourceRecord(
            $patient,
            $sourceRecord,
            $validated['match_method'],
            isset($validated['match_confidence']) ? (float) $validated['match_confidence'] : null,
        );

        return response()->json(['linked' => true]);
    }
}