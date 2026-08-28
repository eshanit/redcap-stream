<?php

namespace App\Services;

use App\Models\ProjectData6;
use Illuminate\Database\Eloquent\Builder;

class ProjectData6Service
{
    public function data6ProjectIds(): array
    {
        return config('redcap.data6_unit.project_ids', array_values(config('redcap.data6_projects', [])));
    }

    public function rows(?array $projectIds = null): Builder
    {
        $projectIds ??= $this->data6ProjectIds();

        return ProjectData6::query()->forProjects($projectIds);
    }

    public function recordRows(string $record, ?array $projectIds = null): Builder
    {
        return $this->rows($projectIds)->forRecord($record);
    }

    public function uniqueRecordCount(?array $projectIds = null): int
    {
        return $this->rows($projectIds)
            ->distinct('record')
            ->count('record');
    }

    public function recordsByProject(?array $projectIds = null): array
    {
        return $this->rows($projectIds)
            ->select('project_id')
            ->selectRaw('COUNT(DISTINCT record) AS record_count')
            ->groupBy('project_id')
            ->pluck('record_count', 'project_id')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }
}