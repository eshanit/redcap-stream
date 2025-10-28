<?php

namespace App\Services;

use App\Models\ProjectData3;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectDiabetesDataService
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

    // liver
    public function getRecentDiabetesDeaths(int $projectId, string $condition): Collection
    {
        $diabetesPatients = $this->getAllRespondents($projectId, $condition);

        /** @var Collection $diabetesDeaths */
        $diabetesDeaths = app(ProjectDataService::class)->getDiabetesDeathRecords($projectId);

        $threeMonthsAgo = now()->subMonths(3);

        return $diabetesPatients->filter(function ($patient) use ($diabetesDeaths, $threeMonthsAgo) {
            $deathRecord = $diabetesDeaths->firstWhere('record', $patient['record']);

            return $deathRecord &&
                   $deathRecord['death_date']->gte($threeMonthsAgo) &&
                   $deathRecord['death_date']->lte(now());
        })->values();
    }

    // females
    public function getActiveFemales(int $projectId, string $condition): Collection
    {
        return $this->getAllRespondents($projectId, $condition)
            ->filter(fn ($respondent) => $respondent['gender'] == 2 && $respondent['status'] == 'Active')
            ->values();
    }

    private function getRecords(int $projectId, string $condition): Collection
    {
        $diagnosisValue = $this->getDiagnosisValue($condition);
        if ($diagnosisValue === null) {
            return collect();
        }

        $diagnosisRecordIds = ProjectData3::where('project_id', $projectId)
            ->where('field_name', 'db_diagnosis')
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
                'enroll_date_demo',
                'dm_enrollment_date',
                'next_appo_date',
                'dm_outcome',
                'outcome_date',
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
        return $records->groupBy('record')->map(function (Collection $recordItems, string $recordId) {
            $groupedByInstance = $recordItems->groupBy('instance');
            $latestInstanceNum = $groupedByInstance->keys()->max();
            $latestInstance = $groupedByInstance->get($latestInstanceNum);
            $facility = $groupedByInstance->get(1)?->firstWhere('field_name', 'facility_demo')?->value;
            $initenrollment = $groupedByInstance->get(1)?->firstWhere('field_name', 'enroll_date_demo')?->value;
            $dmOutcome = $latestInstance->firstWhere('field_name', 'dm_outcome')?->value;
            $nextAppoDate = $latestInstance->firstWhere('field_name', 'next_appo_date')?->value;
            $latestOutcomeDate = $latestInstance->firstWhere('field_name', 'outcome_date')?->value;
            $dmEnrollDate = $groupedByInstance->get(1)?->firstWhere('field_name', 'dm_enrollment_date')?->value;
            $gender = $groupedByInstance->get(1)?->firstWhere('field_name', 'gender_demo')?->value;
            $age = $groupedByInstance->get(1)?->firstWhere('field_name', 'age_demo')?->value;
            $status = $this->determineStatus($dmOutcome, $nextAppoDate);
            $daysAfterEnroll = $this->calcDaysAfterEnroll($dmEnrollDate);
            $daysLTFU = $this->calcNewlyLTFU($nextAppoDate);

            // dd($dmEnrollDate, $daysAfterEnroll);

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
                'status' => $status,
                'initial_enrollment' => $initenrollment,
                'latest_instance' => $latestInstanceNum,
                'latest_outcome_date' => $latestOutcomeDate,
                'next_appo_date' => $nextAppoDate,
                'dm_enrollment_date' => $dmEnrollDate,
                'dm_outcome' => $dmOutcome,
                'days_after_enroll' => $daysAfterEnroll,
                'days_after_ltfu' => $daysLTFU,
            ];
        })->values();
    }

   private function safeParseDate(?string $dateString): ?Carbon
{
    if (empty($dateString)) {
        return null;
    }

    try {
        return Carbon::parse($dateString);
    } catch (\Exception $e) {
        return null;
    }
}

private function determineStatus($dmOutcome, ?string $nextAppoDate): string
{
    if (in_array((int) $dmOutcome, [1, 3], true)) {
        $nextAppoDateCarbon = $this->safeParseDate($nextAppoDate);
        
        if (!$nextAppoDateCarbon) {
            return 'Needs Follow-up';
        }

        $daysDifference = $nextAppoDateCarbon->diffInDays(now(), false);

        return $daysDifference >= 0
            ? ($daysDifference >= 60 ? 'Lost to Followup' : 'Active')
            : 'Active';
    }

    return 'Not Active';
}

private function calcNewlyLTFU(?string $nextAppoDate): ?int
{
    $nextAppoDateCarbon = $this->safeParseDate($nextAppoDate);
    
    if (!$nextAppoDateCarbon) {
        return null;
    }

    return $nextAppoDateCarbon->diffInDays(now(), false);
}
    // last 3 months
    private function calcDaysAfterEnroll(string $dmEnrollDate)
    {
        $dmEnrollDateCarbon = Carbon::parse($dmEnrollDate);

        $daysDifference = $dmEnrollDateCarbon->diffInDays(now(), false);

        return $daysDifference;

    }

  

    private function getDiagnosisValue(string $condition): ?string
    {
        return match ($condition) {
            'diabetes_1' => '1',
            'diabetes_2' => '2',
            default => null,
        };
    }
}
