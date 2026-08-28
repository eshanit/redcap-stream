<?php

namespace App\Console\Commands;

use App\Services\ProjectData6Service;
use Illuminate\Console\Command;

class SyncData6Tracking extends Command
{
    protected $signature = 'data6:sync {--project= : Limit synchronization to one project ID}';

    protected $description = 'Build the Data6 source-record, patient, and encounter read models';

    public function handle(ProjectData6Service $service): int
    {
        $project = $this->option('project');
        $projectIds = $project === null
            ? $service->data6ProjectIds()
            : [(int) $project];

        $records = $service->rows($projectIds)
            ->select(['project_id', 'record'])
            ->whereNotNull('record')
            ->where('record', '<>', '')
            ->distinct()
            ->orderBy('project_id')
            ->orderBy('record')
            ->cursor();

        $count = 0;
        foreach ($records as $source) {
            $service->syncRecord((int) $source->project_id, (string) $source->record);
            $count++;
        }

        $this->info("Synchronized {$count} source records.");

        return self::SUCCESS;
    }
}