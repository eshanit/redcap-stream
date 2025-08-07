<?php

namespace App\Services;

use App\Models\ProjectData3;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectRespiratoryDataService
{
    // Cache frequently used field names
    protected const DIAGNOSIS_FIELD = 'main_diagnosis';

    protected const STATUS_FIELDS = [
        'gender_demo',
        'facility_demo',
        'age_demo',
        'enroll_date_demo',
        'resp_enrollment_date',
        'nxt_appo_date',
        'outcome',
        'when',
    ];

    /**
     * Get all respondents with respiratory diagnosis
     */
    public function getAllRespondents(int $projectId, string $condition): Collection
    {
        // dd($this->processRecords($this->getRecords($projectId)));
        return $this->processRecords($this->getRecords($projectId));
    }

    /**
     * Get only active respondents
     */
    public function getActiveRespondents(int $projectId, string $condition): Collection
    {
        return $this->filterRespondentsByStatus($projectId, $condition, 'Active');
    }

    /**
     * Get respondents lost to follow-up
     */
    public function getLostToFollowUpRespondents(int $projectId, string $condition): Collection
    {
        return $this->filterRespondentsByStatus($projectId, $condition, 'Lost to Followup');
    }

    //
    public function getRecentRespiratoryDeaths(int $projectId, string $condition): Collection
    {
        $respiratoryPatients = $this->getAllRespondents($projectId, $condition);

        /** @var Collection $respiratoryDeaths */
        $respiratoryDeaths = app(ProjectDataService::class)->getRespiratoryDeathRecords($projectId);

        $threeMonthsAgo = now()->subMonths(3);

        return $respiratoryPatients->filter(function ($patient) use ($respiratoryDeaths, $threeMonthsAgo) {
            $deathRecord = $respiratoryDeaths->firstWhere('record', $patient['record']);

            return $deathRecord &&
                   $deathRecord['death_date']->gte($threeMonthsAgo) &&
                   $deathRecord['death_date']->lte(now());
        })->values();
    }

    /**
     * Filter respondents by status (reusable method)
     */
    private function filterRespondentsByStatus(int $projectId, string $condition, string $status): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['status'] === $status)
            ->values();
    }

    public function getEnrolledLastThreeMonths(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['days_after_enroll'] <= 90)
            ->values();
    }

    public function getNewlyLTFU(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['days_after_ltfu'] >= 60 && $respondent['days_after_ltfu'] <= 120)
            ->values();
    }

    // females
    public function getActiveFemales(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['gender'] == 2 && $respondent['status'] == 'Active')
            ->values();
    }

    /**
     * Get base records for respiratory diagnosis patients
     */
    private function getRecords(int $projectId): Collection
    {
        $diagnosisRecordIds = ProjectData3::where('project_id', $projectId)
            ->where('field_name', self::DIAGNOSIS_FIELD)
            ->whereIn('value', [1, 2, 3])
            ->pluck('record');

        // dd($diagnosisRecordIds);

        if ($diagnosisRecordIds->isEmpty()) {
            return collect();
        }

        return ProjectData3::where('project_id', $projectId)
            ->whereIn('record', $diagnosisRecordIds)
            ->whereIn('field_name', self::STATUS_FIELDS)
            ->select('record', 'field_name', 'value', 'instance')
            ->get()
            ->map(function ($item) {
                $item->instance = $item->instance ?? 1;

                return $item;
            });
    }

    /**
     * Process raw records into respondent data structure
     */
    private function processRecords(Collection $records): Collection
    {
        return $records->groupBy('record')
            ->map(function (Collection $recordItems, string $recordId) {
                $groupedByInstance = $recordItems->groupBy('instance');
                $latestInstanceNum = $groupedByInstance->keys()->max();
                $latestInstance = $groupedByInstance->get($latestInstanceNum);
                $facility = $groupedByInstance->get(1)?->firstWhere('field_name', 'facility_demo')?->value;
                $initenrollment = $groupedByInstance->get(1)?->firstWhere('field_name', 'enroll_date_demo')?->value;
                $dmOutcome = $this->getFieldValue($latestInstance, 'outcome');
                $latestOutcomeDate = $this->getFieldValue($latestInstance, 'when');
                $nextAppoDate = $this->getFieldValue($latestInstance, 'nxt_appo_date');
                $dmEnrollDate = $this->getFieldValue($groupedByInstance->get(1), 'resp_enrollment_date');
                $gender = $groupedByInstance->get(1)?->firstWhere('field_name', 'gender_demo')?->value;
                $age = $groupedByInstance->get(1)?->firstWhere('field_name', 'age_demo')?->value;

                $daysAfterEnroll = $this->calcDaysAfterEnroll($dmEnrollDate);
                $daysLTFU = $this->calcNewlyLTFU($nextAppoDate);

                if ($gender == 1) {
                    $realGender = 'Male';
                } else {
                    $realGender = 'Female';
                }

                return [
                    'record' => $recordId,
                    'status' => $this->determineStatus($dmOutcome, $nextAppoDate),
                    'age' => (int) $age,
                    'gender' => $gender,
                    'real_gender' => $realGender,
                    'facility' => $facility,
                    'latest_instance' => $latestInstanceNum,
                    'initial_enrollment' => $initenrollment,
                    'nxt_appo_date' => $nextAppoDate,
                    'latest_outcome_date' => $latestOutcomeDate,
                    'resp_enrollment_date' => $dmEnrollDate,
                    'outcome' => $dmOutcome,
                    'days_after_enroll' => $daysAfterEnroll,
                    'days_after_ltfu' => $daysLTFU,
                ];
            })
            ->values();
    }

    /**
     * Helper to get field value from a grouped instance
     */
    private function getFieldValue(?Collection $instance, string $fieldName): mixed
    {
        return $instance?->firstWhere('field_name', $fieldName)?->value;
    }

    /**
     * Determine respondent status based on outcome and appointment date
     */
    private function determineStatus($dmOutcome, $nextAppoDate): string
    {
        //  dd($dmOutcome);

        // if ($dmOutcome == 2) {
        //     return 'Lost to Followup';
        // }

        if (in_array((int) $dmOutcome, [1, 3], true)) {
            if (empty($nextAppoDate)) {
                return 'Unknown';
            }

            try {
                $nextAppoDateCarbon = Carbon::parse($nextAppoDate);
                $daysDifference = $nextAppoDateCarbon->diffInDays(now(), false);
                //  dd($daysDifference);

                return $daysDifference >= 0
                    ? ($daysDifference >= 60 ? 'Lost to Followup' : 'Active')
                    : 'Active';
            } catch (\Exception $e) {
                return 'Unknown'; // Invalid date format
            }
        }

        return 'Not Active';
    }

    // last 3 months
    private function calcDaysAfterEnroll(string $dmEnrollDate)
    {
        $dmEnrollDateCarbon = Carbon::parse($dmEnrollDate);

        $daysDifference = $dmEnrollDateCarbon->diffInDays(now(), false);

        return $daysDifference;

    }

    // last 3 months
    private function calcNewlyLTFU(string $nextAppoDate)
    {
        $nextAppoDateCarbon = Carbon::parse($nextAppoDate);
        $daysDifference = $nextAppoDateCarbon->diffInDays(now(), false); // Negative if future

        return $daysDifference;

    }
}
