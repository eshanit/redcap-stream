<?php

namespace App\Services;

use App\Models\ProjectData3;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectCardiacDataService
{
    public function getAllRespondents(int $projectId, string $condition): Collection
    {
        $records = $this->getRecords($projectId, $condition);

        return $this->processRecords($records);
    }

    public function getActiveRespondents(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['status'] === 'Active')
            ->values();
    }

    public function getLostToFollowUpRespondents(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['status'] === 'Lost to Followup')
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

    //
    // In ProjectCardiacDataService.php
    public function getRecentCardiacDeaths(int $projectId, string $condition): Collection
    {
        $cardiacPatients = $this->getAllRespondents($projectId, $condition);

        // dd($cardiacPatients);

        /** @var Collection $cardiacDeaths */
        $cardiacDeaths = app(ProjectDataService::class)->getCardiacDeathRecords($projectId);

        $threeMonthsAgo = now()->subMonths(3);

        return $cardiacPatients->filter(function ($patient) use ($cardiacDeaths, $threeMonthsAgo) {
            $deathRecord = $cardiacDeaths->firstWhere('record', $patient['record']);

            return $deathRecord &&
                   $deathRecord['death_date']->gte($threeMonthsAgo) &&
                   $deathRecord['death_date']->lte(now());
        })->values();
    }

    //
    // females
    public function getActiveFemales(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['gender'] == 2 && $respondent['status'] == 'Active')
            ->values();
    }

    //

    private function getRecords(int $projectId, string $condition): Collection
    {
        $diagnosisValue = $this->getDiagnosisValue($condition);
        if ($diagnosisValue === null) {
            return collect();
        }

        $diagnosisRecordIds = ProjectData3::where('project_id', $projectId)
            ->where('field_name', 'card_diagnosis')
            ->where('value', $diagnosisValue)
            ->pluck('record');

        if ($diagnosisRecordIds->isEmpty()) {
            return collect();
        }

        return ProjectData3::where('project_id', $projectId)
            ->whereIn('record', $diagnosisRecordIds)
            ->whereIn('field_name', [
                'gender_demo',
                'facility_demo',
                'age_demo',
                'card_enrollment_date',
                'card_next_appo_date',
                'enroll_date_demo',
                'card_outcome',
                'date_a', // for death calc
            ])
            ->select('record', 'field_name', 'value', 'instance')
            ->get()
            ->map(function ($item) {
                $item->instance = $item->instance ?? 1;

                return $item;
            });
    }

    private function processRecords(Collection $records): Collection
    {
        // dd($records->groupBy('record')->);
        return $records->groupBy('record')->map(function (Collection $recordItems, string $recordId) {
            $groupedByInstance = $recordItems->groupBy('instance');
            $latestInstanceNum = $groupedByInstance->keys()->max();
            $latestInstance = $groupedByInstance->get($latestInstanceNum);
            $gender = $groupedByInstance->get(1)?->firstWhere('field_name', 'gender_demo')?->value;
            $initenrollment = $groupedByInstance->get(1)?->firstWhere('field_name', 'enroll_date_demo')?->value;
            $age = $groupedByInstance->get(1)?->firstWhere('field_name', 'age_demo')?->value;
            $facility = $groupedByInstance->get(1)?->firstWhere('field_name', 'facility_demo')?->value;
            $dmOutcome = $latestInstance->firstWhere('field_name', 'card_outcome')?->value;
            $latestOutcomeDate = $latestInstance->firstWhere('field_name', 'date_a')?->value;
            $nextAppoDate = $latestInstance->firstWhere('field_name', 'card_next_appo_date')?->value;
            $dmEnrollDate = $groupedByInstance->get(1)?->firstWhere('field_name', 'card_enrollment_date')?->value;

            $status = $this->determineStatus($dmOutcome, $nextAppoDate);
            $daysAfterEnroll = $this->calcDaysAfterEnroll($dmEnrollDate);
            // dd($nextAppoDate);
            $daysLTFU = $this->calcNewlyLTFU($nextAppoDate);

            if ($gender == 1) {
                $realGender = 'Male';
            } else {
                $realGender = 'Female';
            }

            return [
                'record' => $recordId,
                'status' => $status,
                'age' => (int) $age,
                'gender' => $gender,
                'real_gender' => $realGender,
                'facility' => $facility,
                'initial_enrollment' => $initenrollment,
                'latest_instance' => $latestInstanceNum,
                'card_next_appo_date' => $nextAppoDate,
                'card_enrollment_date' => $dmEnrollDate,
                'card_outcome' => $dmOutcome,
                'latest_outcome_date' => $latestOutcomeDate,
                'days_after_enroll' => $daysAfterEnroll,
                'days_after_ltfu' => $daysLTFU,
            ];
        })->values();
    }

    private function determineStatus($dmOutcome, $nextAppoDate): string
    {

        // dd($dmOutcome, $nextAppoDate);
        // Immediate LTFU if card_outcome is 2
        // if ($dmOutcome == 2) {
        //     return 'Lost to Followup';
        // }

        // Check for active status (1 or 3)
        if (in_array((int) $dmOutcome, [1, 3])) {
            if (! $nextAppoDate) {
                return 'Unknown'; // Missing appointment date
            }

            $nextAppoDateCarbon = Carbon::parse($nextAppoDate);
            $daysDifference = $nextAppoDateCarbon->diffInDays(now(), false); // Negative if future

            if ($daysDifference >= 0) { // Past appointment
                return $daysDifference >= 60 ? 'Lost to Followup' : 'Active';
            }

            return 'Active'; // Future appointment
        }

        return 'Not Active'; // card_outcome not 1, 2, or 3
    }

    // last 3 months
    private function calcDaysAfterEnroll(string $dmEnrollDate)
    {
        $dmEnrollDateCarbon = Carbon::parse($dmEnrollDate);

        $daysDifference = $dmEnrollDateCarbon->diffInDays(now(), false);

        return $daysDifference;

    }

    // last 3 months
    private function calcNewlyLTFU(?string $nextAppoDate)
    {
        $nextAppoDateCarbon = Carbon::parse($nextAppoDate);
        $daysDifference = $nextAppoDateCarbon->diffInDays(now(), false); // Negative if future

        return $daysDifference ?? null;

    }

    private function getDiagnosisValue(string $condition): ?string
    {
        return match ($condition) {
            'rheumatic' => '2',
            'congenital' => '5',
            'heart_failure' => '6',
            default => null,
        };
    }
}
