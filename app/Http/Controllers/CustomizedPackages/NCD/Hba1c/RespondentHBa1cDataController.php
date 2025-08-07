<?php

namespace App\Http\Controllers\CustomizedPackages\NCD\Hba1c;

use App\Http\Controllers\Controller;
use App\Models\ProjectData3;
use Carbon\Carbon;

class RespondentHBa1cDataController extends Controller
{
    public function __invoke(int $projectId, string $record)
    {
        // Fetch all relevant data for this patient
        $rawData = ProjectData3::where('record', $record)
            ->whereIn('field_name', [
                'ncd_hba1c',
                'ncd_health_facility',
                'ncd_age_2',
                'ncd_gender',
                'ncd_visit_date',
            ])
            ->get();

        // Check if we have any data
        if ($rawData->isEmpty()) {
            return response()->json(['message' => 'No data found for this patient.'], 404);
        }

        // Extract gender and health facility from the first record
        $gender = $rawData->where('field_name', 'ncd_gender')->first()->value ?? null;
        $facility = $rawData->where('field_name', 'ncd_health_facility')->first()->value ?? null;

        // Group by instance (visit) and then by field_name
        $groupedByVisit = $rawData->groupBy('instance');

        // Process each visit
        $processedVisits = [];
        $hba1cValues = collect(); // Store all HbA1c values for stats

        foreach ($groupedByVisit as $instance => $entries) {
            // Structure data by field_name for easy access
            $visitData = $entries->groupBy('field_name')->map(function ($items) {
                return $items->first()->value; // Take the first value (assuming no duplicates)
            });

            // Store HbA1c value for stats
            if (isset($visitData['ncd_hba1c'])) {
                $hba1cValues->push((float) $visitData['ncd_hba1c']);

                $processedVisits[] = [
                    'instance' => $instance ?: 1, // Use 1 if instance is empty
                    'visit_date' => $visitData['ncd_visit_date'] ?? null,
                    'hba1c' => (float) $visitData['ncd_hba1c'],
                    'health_facility' => $facility,
                    'age' => isset($visitData['ncd_age_2']) ? (int) $visitData['ncd_age_2'] : null,
                    'gender' => $gender,
                ];
            }
        }

        // No HbA1c data found
        if ($hba1cValues->isEmpty()) {
            return response()->json(['message' => 'No HbA1c data found for this patient.'], 404);
        }

        // Calculate statistics
        $stats = $this->calculateStats($hba1cValues, $processedVisits);

        return response()->json([
            'record' => $record,
            'demographics' => [
                'health_facility' => $facility,
                'age' => $processedVisits[0]['age'] ?? null,
                'gender' => $this->getGender($gender),
            ],
            'statistics' => $stats,
            'visits' => $processedVisits,
        ]);
    }

    private function getGender($genderCode)
    {
        if ($genderCode == 1) {
            return 'Male';
        } elseif ($genderCode == 2) {
            return 'Female';
        }

        return 'Unknown'; // Handle unexpected gender codes
    }

    /**
     * Calculate all statistics for HbA1c values.
     */
    private function calculateStats($hba1cValues, $visits)
    {
        $count = $hba1cValues->count();
        $mean = $hba1cValues->avg();
        $min = $hba1cValues->min();
        $max = $hba1cValues->max();

        // Standard Deviation
        $variance = $hba1cValues->reduce(fn ($carry, $value) => $carry + pow($value - $mean, 2), 0) / max(1, $count - 1);
        $stdDev = sqrt($variance);

        // Median & Quartiles
        $sorted = $hba1cValues->sort()->values();
        $median = $sorted[$count / 2] ?? null;
        if ($count % 2 === 0) {
            $median = ($sorted[$count / 2 - 1] + $sorted[$count / 2]) / 2;
        }

        $q1 = $sorted[floor($count * 0.25)];
        $q3 = $sorted[floor($count * 0.75)];
        $iqr = $q3 - $q1;

        // Coefficient of Variation
        $cv = ($mean != 0) ? ($stdDev / $mean) * 100 : 0;

        // Clinical Thresholds
        $thresholds = [
            'normal' => $hba1cValues->filter(fn ($v) => $v < 5.7)->count(),
            'prediabetes' => $hba1cValues->filter(fn ($v) => $v >= 5.7 && $v < 6.5)->count(),
            'diabetes' => $hba1cValues->filter(fn ($v) => $v >= 6.5)->count(),
        ];

        // Time-based analysis (if visit dates exist)
        $timeStats = [];
        $visitDates = collect($visits)->pluck('visit_date')->filter()->sort();

        if ($visitDates->count() > 1) {
            $firstDate = Carbon::parse($visitDates->first());
            $lastDate = Carbon::parse($visitDates->last());
            $timeStats = [
                'days_between_first_last_visit' => $firstDate->diffInDays($lastDate),
                'rate_of_change_per_day' => ($max - $min) / max(1, $firstDate->diffInDays($lastDate)),
            ];
        }

        return [
            'basic' => [
                'count' => $count,
                'min' => round($min, 2),
                'max' => round($max, 2),
                'mean' => round($mean, 2),
                'median' => round($median, 2),
                'std_dev' => round($stdDev, 2),
            ],
            'spread' => [
                'q1' => round($q1, 2),
                'q3' => round($q3, 2),
                'iqr' => round($iqr, 2),
                'cv' => round($cv, 2).'%',
            ],
            'clinical_thresholds' => $thresholds,
            'time_analysis' => $timeStats,
        ];
    }
}
