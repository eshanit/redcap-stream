<?php

namespace App\Http\Controllers\CustomizedPackages\NCD\Hba1c;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectDataService;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class GenderHBa1cDataController extends Controller
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

        // Group data by gender first, then by record
        $genderGroups = $packageData->groupBy('ncd_gender');

        $genderAnalysis = $genderGroups->map(function (Collection $genderData, string $gender) {
            // Further group by individual records
            $records = $genderData->groupBy('record');

            $stats = $records->map(function (Collection $entries) {
                //  dd($entries);
                $hba1cValues = $entries->pluck('ncd_hb1ac')->filter()->values();

                if ($hba1cValues->isEmpty()) {
                    return null; // No HbA1c values for this record
                }

                $mean = $hba1cValues->avg();

                // Classify based on average HbA1c
                $classification = '';
                if ($mean < 5.7) {
                    $classification = 'normal';
                } elseif ($mean >= 5.7 && $mean < 6.5) {
                    $classification = 'prediabetic';
                } else {
                    $classification = 'diabetic';
                }

                return [
                    'mean_hba1c' => round($mean, 2),
                    'classification' => $classification,
                    'visit_count' => $hba1cValues->count(),
                    'health_facility' => $entries->first()['ncd_health_facility'] ?? null,
                    'age' => $entries->first()['ncd_age'] ?? null,
                ];
            })->filter();

            // Calculate gender-wide statistics
            $allValues = $records->flatMap(fn ($r) => $r->pluck('ncd_hb1ac')->filter());

            $normalCount = 0;
            $prediabeticCount = 0;
            $diabeticCount = 0;

            $normalCount = $stats->filter(fn ($r) => $r['classification'] === 'normal')->count();
            $prediabeticCount = $stats->filter(fn ($r) => $r['classification'] === 'prediabetic')->count();
            $diabeticCount = $stats->filter(fn ($r) => $r['classification'] === 'diabetic')->count();

            return [
                'count' => $records->count(),
                'mean_hba1c' => $allValues->isNotEmpty() ? round($allValues->avg(), 2) : null,
                'classifications' => [
                    'normal' => $normalCount,
                    'prediabetic' => $prediabeticCount,
                    'diabetic' => $diabeticCount,
                ],
                'classificationPercentages' => [
                    'normal' => $records->count() > 0 ? round(($normalCount / $records->count()) * 100, 2) : 0,
                    'prediabetic' => $records->count() > 0 ? round(($prediabeticCount / $records->count()) * 100, 2) : 0,
                    'diabetic' => $records->count() > 0 ? round(($diabeticCount / $records->count()) * 100, 2) : 0,
                ],
                'records' => $stats,
            ];
        });

        return Inertia::render('Customizations/NCD/Hba1c/GenderAnalysis', [
            'project' => $project,
            'genderData' => $genderAnalysis,
            'totalRespondents' => $packageData->groupBy('record')->count(),
        ]);
    }
}
