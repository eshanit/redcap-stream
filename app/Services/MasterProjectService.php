<?php

namespace App\Services;

use App\Models\ProjectData3;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MasterProjectService
{
    /**
     * Get the active patients for a given project.
     */
    public function getActivePatients(int $projectId): Collection
    {
        return ProjectData3::query()
            ->where('project_id', $projectId)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get the project details by ID.
     */
    public function getProjectById(int $projectId): ?ProjectData3
    {
        return ProjectData3::find($projectId);
    }

    /**
     * Get the project data for a specific date range.
     */
    public function getProjectDataByDateRange(int $projectId, string $datefield, Carbon $startDate, Carbon $endDate): Collection
    {
        return ProjectData3::query()
            ->where('project_id', $projectId)
            ->whereBetween($datefield, [$startDate, $endDate])
            ->get();
    }

    /**
     * Get the unique count of items in the 'record' column for a project.
     */
    public function getUniqueRecordCount(int $projectId): int
    {
        return ProjectData3::query()
            ->where('project_id', $projectId)
            ->distinct('record')
            ->count('record');
    }
}
