<?php

namespace App\Http\Controllers\CustomizedPackages\NCD\Hba1c;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectDataService;
use Inertia\Inertia;

class AllHba1cController extends Controller
{
    protected $projectDataService;

    protected $outlierMultiplier = 1.5; // Standard IQR multiplier for outliers

    public function __construct(ProjectDataService $projectDataService)
    {
        $this->projectDataService = $projectDataService;
    }

    public function __invoke(int $projectId)
    {
        $project = Project::query()->select('project_id', 'app_title')->findOrFail($projectId);
        $packageData = $this->projectDataService->getCleanedHba1cData($projectId);

        // Extract and clean HbA1c values
        $hba1cValues = $this->cleanHba1cValues(
            collect($packageData)->pluck('ncd_hb1ac')->toArray()
        );

        // Calculate statistics with and without outliers
        $statsWithOutliers = $this->calculateStatistics($hba1cValues['all']);
        $statsWithoutOutliers = $this->calculateStatistics($hba1cValues['filtered']);

        // Prepare summaries
        $summaries = $this->generateSummaries($statsWithOutliers, $statsWithoutOutliers);

        return Inertia::render('Customizations/NCD/Hba1c/AllData', [
            'project' => $project,
            'packageData' => $packageData,
            'statsWithOutliers' => $statsWithOutliers,
            'statsWithoutOutliers' => $statsWithoutOutliers,
            'summaries' => $summaries,
            'outlierInfo' => [
                'count' => count($hba1cValues['all']) - count($hba1cValues['filtered']),
                'percentage' => count($hba1cValues['all']) > 0
                    ? round((count($hba1cValues['all']) - count($hba1cValues['filtered'])) / count($hba1cValues['all']) * 100, 2)
                    : 0,
            ],
        ]);
    }

    /**
     * Clean and separate HbA1c values into all values and non-outliers
     */
    protected function cleanHba1cValues(array $values): array
    {
        // Convert to numbers and remove invalid values
        $cleanedValues = array_filter(array_map(function ($value) {
            return is_numeric($value) ? (float) $value : null;
        }, $values), function ($value) {
            return $value !== null;
        });

        if (empty($cleanedValues)) {
            return ['all' => [], 'filtered' => []];
        }

        // Calculate IQR bounds
        $q1 = $this->calculatePercentile($cleanedValues, 25);
        $q3 = $this->calculatePercentile($cleanedValues, 75);
        $iqr = $q3 - $q1;

        $lowerBound = $q1 - ($this->outlierMultiplier * $iqr);
        $upperBound = $q3 + ($this->outlierMultiplier * $iqr);

        // Filter outliers
        $filteredValues = array_filter($cleanedValues, function ($value) use ($lowerBound, $upperBound) {
            return $value >= $lowerBound && $value <= $upperBound;
        });

        return [
            'all' => $cleanedValues,
            'filtered' => array_values($filteredValues), // Reindex array
        ];
    }

    /**
     * Calculate all statistics for a given dataset
     */
    protected function calculateStatistics(array $data): array
    {
        return [
            'mean' => $this->calculateMean($data),
            'median' => $this->calculateMedian($data),
            'stdDev' => $this->calculateStandardDeviation($data),
            'q1' => $this->calculatePercentile($data, 25),
            'q3' => $this->calculatePercentile($data, 75),
            'min' => ! empty($data) ? min($data) : null,
            'max' => ! empty($data) ? max($data) : null,
            'count' => count($data),
        ];
    }

    /**
     * Generate human-readable summaries
     */
    protected function generateSummaries(array $withOutliers, array $withoutOutliers): array
    {
        $summaries = [];

        // Mean comparison
        if ($withOutliers['mean'] !== null && $withoutOutliers['mean'] !== null) {
            $summaries['mean'] = sprintf(
                'The average HbA1c level is %.1f%% (%.1f%% when excluding outliers). %s',
                $withOutliers['mean'],
                $withoutOutliers['mean'],
                $this->getDifferenceComment($withOutliers['mean'], $withoutOutliers['mean'])
            );
        } else {
            $summaries['mean'] = 'No HbA1c data available to calculate the average.';
        }

        // Median comparison
        if ($withOutliers['median'] !== null && $withoutOutliers['median'] !== null) {
            $summaries['median'] = sprintf(
                'The median HbA1c level is %.1f%% (%.1f%% without outliers). Half of the data false below (or above) this value.',
                $withOutliers['median'],
                $withoutOutliers['median']
            );
        } else {
            $summaries['median'] = 'No HbA1c data available to calculate the median.';
        }

        // Standard deviation comparison
        if ($withOutliers['stdDev'] !== null && $withoutOutliers['stdDev'] !== null) {
            $summaries['stdDev'] = sprintf(
                'Standard deviation is %.1f%% (%.1f%% without outliers), showing %s variability when outliers are excluded.',
                $withOutliers['stdDev'],
                $withoutOutliers['stdDev'],
                $withoutOutliers['stdDev'] < $withOutliers['stdDev'] ? 'reduced' : 'increased'
            );
        } else {
            $summaries['stdDev'] = 'No HbA1c data available to calculate standard deviation.';
        }

        // Quartile information - FIXED THE ISSUE HERE
        if ($withOutliers['q1'] !== null && $withOutliers['q3'] !== null &&
            $withoutOutliers['q1'] !== null && $withoutOutliers['q3'] !== null) {
            $summaries['iqr'] = sprintf(
                'The interquartile range (middle 50%% of values) is %.1f%% to %.1f%%. '.
                'Without outliers, this range is %.1f%% to %.1f%%.',
                $withOutliers['q1'],
                $withOutliers['q3'],
                $withoutOutliers['q1'],
                $withoutOutliers['q3']
            );
        } else {
            $summaries['iqr'] = 'No HbA1c data available to calculate quartiles.';
        }

        return $summaries;
    }

    /**
     * Helper to comment on differences between values
     */
    protected function getDifferenceComment(float $with, float $without): string
    {
        $difference = $with - $without;
        $percentDifference = ($difference / $without) * 100;

        if (abs($percentDifference) < 5) {
            return 'Outliers have minimal impact on the average.';
        } elseif ($percentDifference > 0) {
            return 'Outliers are pulling the average higher by '.abs($percentDifference).'%.';
        } else {
            return 'Outliers are pulling the average lower by '.abs($percentDifference).'%.';
        }
    }

    /**
     * Calculate the mean of an array of numbers.
     */
    protected function calculateMean(array $data): ?float
    {
        $count = count($data);
        if ($count === 0) {
            return null; // Or throw an exception, depending on your needs
        }

        return (float) array_sum($data) / $count;
    }

    /**
     * Calculate the median of an array of numbers.
     */
    protected function calculateMedian(array $data): ?float
    {
        $count = count($data);
        if ($count === 0) {
            return null; // Or throw an exception
        }

        $values = $data; // Create a copy to avoid modifying the original array
        sort($values);

        $middle = (int) floor($count / 2);

        if ($count % 2 === 0) { // Even number of elements
            return ($values[$middle - 1] + $values[$middle]) / 2;
        } else { // Odd number of elements
            return $values[$middle];
        }
    }

    /**
     * Calculate the standard deviation of an array of numbers.
     */
    protected function calculateStandardDeviation(array $data): ?float
    {
        $count = count($data);
        if ($count === 0) {
            return null; // Or throw an exception
        }

        $mean = $this->calculateMean($data);
        if ($mean === null) {
            return null; // Cannot calculate standard deviation without a mean
        }

        $sumOfSquaredDifferences = 0;
        foreach ($data as $value) {
            $sumOfSquaredDifferences += pow($value - $mean, 2);
        }

        $variance = $sumOfSquaredDifferences / $count;

        return (float) sqrt($variance);
    }

    /**
     * Calculate a percentile (e.g., Q1, Q3) of an array of numbers.
     *
     * @param  float  $percentile  (0-100)
     */
    protected function calculatePercentile(array $data, float $percentile): ?float
    {
        $count = count($data);
        if ($count === 0) {
            return null;
        }

        $values = $data;
        sort($values);

        $index = ($percentile / 100) * ($count - 1);

        if (floor($index) == $index) {
            return $values[(int) $index];
        }

        $i = (int) floor($index);
        $fractionalPart = $index - $i;

        return $values[$i] + ($values[$i + 1] - $values[$i]) * $fractionalPart;
    }
}
