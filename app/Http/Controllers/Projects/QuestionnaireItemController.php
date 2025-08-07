<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\ProjectData3;
use Illuminate\Http\JsonResponse;

class QuestionnaireItemController extends Controller
{
    public function __invoke(int $projectId, string $fieldName): JsonResponse
    {
        // Fetch data with the specified project ID and field name
        $data = ProjectData3::with('project_metadata')
            ->where('project_id', $projectId)
            ->where('field_name', $fieldName)
            ->get()
            ->map(fn ($point) => [
                'record' => $point->record,
                'field_name' => $point->field_name,
                'value' => $point->value,
                'instance' => $point->instance,
                'element_type' => $point->project_metadata->element_type,
                'element_enum' => $point->project_metadata->element_enum,
            ]);

        // Check if data is found
        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No data found.',
            ], 404);
        }

        //
        return response()->json([
            'data' => $data,
        ], 200);
    }
}
