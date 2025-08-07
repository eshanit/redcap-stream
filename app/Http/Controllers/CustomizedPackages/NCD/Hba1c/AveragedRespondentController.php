<?php

namespace App\Http\Controllers\CustomizedPackages\NCD\Hba1c;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectDataService;
use Inertia\Inertia;

class AveragedRespondentController extends Controller
{
    protected $projectDataService;

    public function __construct(ProjectDataService $projectDataService)
    {
        $this->projectDataService = $projectDataService;
    }

    public function __invoke(int $projectId)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);

        $packageData = $this->projectDataService->getCleanedHba1cData($projectId);

        $groupedData = $packageData->groupBy('record');

        // Initialize counters
        $normalCount = 0;
        $prediabeticCount = 0;
        $diabeticCount = 0;

        $recordsWithStats = $groupedData->map(function ($entries, $recordId) use (&$normalCount, &$prediabeticCount, &$diabeticCount) {
            $hba1cValues = $entries->pluck('ncd_hb1ac')->filter()->values();

            if ($hba1cValues->isEmpty()) {
                return null;
            }

            $count = $hba1cValues->count();
            $mean = $hba1cValues->avg();
            $min = $hba1cValues->min();
            $max = $hba1cValues->max();

            // Classify based on average HbA1c
            if ($mean < 5.7) {
                $classification = 'normal';
                $normalCount++;
            } elseif ($mean >= 5.7 && $mean < 6.5) {
                $classification = 'prediabetic';
                $prediabeticCount++;
            } else {
                $classification = 'diabetic';
                $diabeticCount++;
            }

            // Standard deviation
            $variance = $hba1cValues->reduce(fn ($c, $i) => $c + pow($i - $mean, 2), 0) / max(1, $count - 1);
            $stdDev = sqrt($variance);

            // Median
            $sorted = $hba1cValues->sort()->values();
            $median = $sorted[$count / 2] ?? null;
            if ($count % 2 === 0) {
                $median = ($sorted[$count / 2 - 1] + $sorted[$count / 2]) / 2;
            }

            $firstEntry = $entries->first();

            return [
                'record' => $recordId,
                'min_hba1c' => round($min, 2),
                'max_hba1c' => round($max, 2),
                'mean' => round($mean, 2),
                'std_dev' => round($stdDev, 2),
                'count' => $count,
                'classification' => $classification,
                'health_facility' => $firstEntry['ncd_health_facility'],
                'age' => (int) $firstEntry['ncd_age'],
                'gender' => $firstEntry['ncd_gender'],
            ];
        })->filter()->values();

        // Create flattened data for the table
        $tableData = $recordsWithStats->map(function ($record) {
            return [
                ...$record,
            ];
        });

        // dd($tableData);

        return Inertia::render('Customizations/NCD/Hba1c/AveragedRespondents', [
            'project' => $project,
            'tableData' => $tableData,
            'numRespondents' => $groupedData->count(),
            'classificationCounts' => [
                'normal' => $normalCount,
                'prediabetic' => $prediabeticCount,
                'diabetic' => $diabeticCount,
            ],
            'classificationPercentages' => [
                'normal' => $groupedData->count() > 0 ? round(($normalCount / $groupedData->count()) * 100, 2) : 0,
                'prediabetic' => $groupedData->count() > 0 ? round(($prediabeticCount / $groupedData->count()) * 100, 2) : 0,
                'diabetic' => $groupedData->count() > 0 ? round(($diabeticCount / $groupedData->count()) * 100, 2) : 0,
            ],
        ]);
    }
}
