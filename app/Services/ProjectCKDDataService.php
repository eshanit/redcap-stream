<?php

namespace App\Services;

use App\Models\ProjectData3;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectCKDDataService
{
    // Cache frequently used field names
    protected const DIAGNOSIS_FIELD = 'ckd_new_patient';

    protected const STATUS_FIELDS = [
        'gender_demo',
        'facility_demo',
        'age_demo',
        'enroll_date_demo',
        'ckd_enrollment_date',
        'meds_next_appo_date',
        'final_outcome',
        'final_outcome_date',
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

    public function getEnrolledLastThreeMonths(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['days_after_enroll'] <= 90)
            ->values();
    }

    // /kideny
    public function getRecentCKDDeaths(int $projectId, string $condition): Collection
    {
        $ckdPatients = $this->getAllRespondents($projectId, $condition);

        // dd($ckdPatients);

        /** @var Collection $ckdDeaths */
        $ckdDeaths = app(ProjectDataService::class)->getCKDDeathRecords($projectId);

        $threeMonthsAgo = now()->subMonths(3);

        return $ckdPatients->filter(function ($patient) use ($ckdDeaths, $threeMonthsAgo) {
            $deathRecord = $ckdDeaths->firstWhere('record', $patient['record']);

            return $deathRecord &&
                   $deathRecord['death_date']->gte($threeMonthsAgo) &&
                   $deathRecord['death_date']->lte(now());
        })->values();
    }

    //

    public function getActiveFemales(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['gender'] == 2 && $respondent['status'] == 'Active')
            ->values();
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

    public function getNewlyLTFU(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['days_after_ltfu'] >= 60 && $respondent['days_after_ltfu'] <= 120)
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
                $gender = $groupedByInstance->get(1)?->firstWhere('field_name', 'gender_demo')?->value;
                $age = $groupedByInstance->get(1)?->firstWhere('field_name', 'age_demo')?->value;
                $initenrollment = $groupedByInstance->get(1)?->firstWhere('field_name', 'enroll_date_demo')?->value;
                $facility = $groupedByInstance->get(1)?->firstWhere('field_name', 'facility_demo')?->value;
                $latestOutcomeDate = $this->getFieldValue($latestInstance, 'final_outcome_date');
                $dmOutcome = $this->getFieldValue($latestInstance, 'final_outcome');
                $nextAppoDate = $this->getFieldValue($latestInstance, 'meds_next_appo_date');
                $dmEnrollDate = $this->getFieldValue($groupedByInstance->get(1), 'ckd_enrollment_date');
                $daysAfterEnroll = $this->calcDaysAfterEnroll($dmEnrollDate);
                $daysLTFU = $this->calcNewlyLTFU($nextAppoDate);

                if ($gender == 1) {
                    $realGender = 'Male';
                } else {
                    $realGender = 'Female';
                }

                return [
                    'record' => $recordId,
                    'age' => (int) $age,
                    'gender' => $gender,
                    'real_gender' => $realGender,
                    'facility' => $facility,
                    'initial_enrollment' => $initenrollment,
                    'status' => $this->determineStatus($dmOutcome, $nextAppoDate),
                    'latest_instance' => $latestInstanceNum,
                    'meds_next_appo_date' => $nextAppoDate,
                    'ckd_enrollment_date' => $dmEnrollDate,
                    'latest_outcome_date' => $latestOutcomeDate,
                    'final_outcome' => $dmOutcome,
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
     * Determine respondent status based on final_outcome and appointment date
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
    private function calcNewlyLTFU(string $nextAppoDate)
    {
        $nextAppoDateCarbon = Carbon::parse($nextAppoDate);
        $daysDifference = $nextAppoDateCarbon->diffInDays(now(), false); // Negative if future

        return $daysDifference;

    }

    // last 3 months
    private function calcDaysAfterEnroll(string $dmEnrollDate)
    {
        $dmEnrollDateCarbon = Carbon::parse($dmEnrollDate);

        $daysDifference = $dmEnrollDateCarbon->diffInDays(now(), false);

        return $daysDifference;

    }
}
