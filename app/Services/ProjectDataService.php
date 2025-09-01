<?php

namespace App\Services;

use App\Models\ProjectData3;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectDataService
{
    public function getHba1cData(int $projectId)
    {
        $data = ProjectData3::where('project_id', $projectId)
            ->whereIn('field_name', [
                'ncd_hba1c',
                'ncd_health_facility',
                'ncd_age_2',
                'ncd_gender',
                'ncd_visit_date',
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {
                // First collect ALL data from ALL instances, preserving instance numbers
                $allInstances = $group->groupBy('instance')->map(function ($eventGroup, $instance) {

                    if ($instance != '') {
                        $newInst = $instance;
                    } else {
                        $newInst = 1;
                    }

                    return [
                        'instance' => $newInst, // Handle null/undefined instances
                        'health_facility' => $eventGroup->where('field_name', 'ncd_health_facility')->first()?->value,
                        'age' => $eventGroup->where('field_name', 'ncd_age_2')->first()?->value,
                        'gender' => $eventGroup->where('field_name', 'ncd_gender')->first()?->value,
                        'hba1c' => $eventGroup->where('field_name', 'ncd_hba1c')->first()?->value,
                        'visit_date' => $eventGroup->where('field_name', 'ncd_visit_date')->first()?->value,
                    ];
                });

                // Find the first non-null demographic values from any instance
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                           ! is_null($demo['age']) ||
                           ! is_null($demo['gender']);
                });

                // Now filter to only instances with hba1c values
                $instancesWithHba1c = $allInstances->filter(function ($demo) {
                    return ! is_null($demo['hba1c']);
                });

                // If no instances have hba1c values, skip this record
                if ($instancesWithHba1c->isEmpty()) {
                    return null;
                }

                // Prepare the final data with demographics filled in
                return $instancesWithHba1c->map(function ($demo) use ($record, $firstValidDemographics) {
                    // Convert gender to Male/Female
                    $gender = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    if ($gender == 1) {
                        $gender = 'Male';
                    } elseif ($gender == 2) {
                        $gender = 'Female';
                    }

                    return [
                        'record' => $record,
                        'event' => $demo['instance'], // Will be 1 for null/undefined instances
                        'ncd_health_facility' => $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null,
                        'ncd_age' => $demo['age'] ?? $firstValidDemographics['age'] ?? null,
                        'ncd_gender' => $gender,
                        'ncd_hb1ac' => $demo['hba1c'],
                        'ncd_visit_date' => $demo['visit_date'] ?? null,
                    ];
                });
            })
            ->filter()
            ->flatten(1)
            ->values();

        return $data;
    }

    /**
     * get Cleaned data
     */
    public function getCleanedHba1cData(int $projectId)
    {
        $data = ProjectData3::where('project_id', $projectId)
            ->whereIn('field_name', [
                'ncd_hba1c',
                'ncd_health_facility',
                'ncd_age_2',
                'ncd_gender',
                'ncd_visit_date',
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {
                // First collect ALL data from ALL instances, preserving instance numbers
                $allInstances = $group->groupBy('instance')->map(function ($eventGroup, $instance) {
                    // Clean the HbA1c value
                    $hba1cValue = $eventGroup->where('field_name', 'ncd_hba1c')->first()?->value;
                    $cleanedHba1c = $this->cleanHba1cValue($hba1cValue);

                    if ($instance != '') {
                        $newInst = $instance;
                    } else {
                        $newInst = 1;
                    }

                    return [
                        'instance' => $newInst,
                        'health_facility' => $eventGroup->where('field_name', 'ncd_health_facility')->first()?->value,
                        'age' => $eventGroup->where('field_name', 'ncd_age_2')->first()?->value,
                        'gender' => $eventGroup->where('field_name', 'ncd_gender')->first()?->value,
                        'hba1c' => $cleanedHba1c,
                        'visit_date' => $eventGroup->where('field_name', 'ncd_visit_date')->first()?->value,
                    ];
                });

                // Find the first non-null demographic values from any instance
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                           ! is_null($demo['age']) ||
                           ! is_null($demo['gender']);
                });

                // Now filter to only instances with valid numeric hba1c values
                $instancesWithValidHba1c = $allInstances->filter(function ($demo) {
                    return is_numeric($demo['hba1c']);
                });

                // If no instances have valid hba1c values, skip this record
                if ($instancesWithValidHba1c->isEmpty()) {
                    return null;
                }

                // Prepare the final data with demographics filled in
                return $instancesWithValidHba1c->map(function ($demo) use ($record, $firstValidDemographics) {
                    // Convert gender to Male/Female
                    $gender = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    if ($gender == 1) {
                        $gender = 'Male';
                    } elseif ($gender == 2) {
                        $gender = 'Female';
                    }

                    return [
                        'record' => $record,
                        'event' => $demo['instance'],
                        'ncd_health_facility' => $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null,
                        'ncd_age' => $demo['age'] ?? $firstValidDemographics['age'] ?? null,
                        'ncd_gender' => $gender,
                        'ncd_hb1ac' => $demo['hba1c'],
                        'ncd_visit_date' => $demo['visit_date'] ?? null,
                    ];
                });
            })
            ->filter()
            ->flatten(1)
            ->values();

        return $data;
    }

    public function getDeathStatistics(int $projectId)
    {
        $deathRecords = ProjectData3::where('project_id', $projectId)
            ->where(function ($query) {
                $query->whereIn('field_name', [
                    'outcome',
                    'dm_outcome',
                    'ep_outcome',
                    'scd_outcome',
                    'card_outcome',
                    'liver_outcome',
                ])
                    ->where('value', 5)
                    ->orWhere(function ($q) {
                        $q->where('field_name', 'final_outcome')
                            ->where('value', 6);
                    });
            })
            ->get()
            ->groupBy(['record', 'instance']);

        // dd($deathRecords);

        $totalDeaths = 0;
        $deathsByQuarter = [];

        $dateFieldsMap = [
            'outcome' => 'when',
            'dm_outcome' => 'outcome_date',
            'ep_outcome' => 'ep_outcome_date',
            'scd_outcome' => 'scd_outcome_date',
            'card_outcome' => 'date_a',
            'final_outcome' => 'final_outcome_date',
            'liver_outcome' => 'liver_outcome_date',
        ];

        foreach ($deathRecords as $recordId => $instances) {
            foreach ($instances as $instance => $fields) {
                $normalizedInstance = $instance ?? 1;
                $deathDate = null;

                foreach ($fields as $field) {
                    $fieldName = $field->field_name;

                    if (($fieldName === 'final_outcome' && (int) $field->value === 6) ||
                        ($fieldName !== 'final_outcome' && (int) $field->value === 5)) {

                        $dateField = $dateFieldsMap[$fieldName];

                        $dateRecord = ProjectData3::where('project_id', $projectId)
                            ->where('record', $recordId)
                            ->where('instance', $instance)
                            ->where('field_name', $dateField)
                            ->first();

                        if ($dateRecord && $dateRecord->value) {
                            try {
                                $deathDate = Carbon::parse($dateRecord->value);
                            } catch (\Exception $e) {
                                // Handle invalid date format
                                continue;
                            }
                            break;
                        }
                    }
                }

                if ($deathDate instanceof Carbon) {
                    $totalDeaths++;

                    // Get year and quarter from Carbon
                    $year = $deathDate->year;
                    $quarter = 'Q'.$deathDate->quarter;

                    // Initialize year if not exists
                    if (! isset($deathsByQuarter[$year])) {
                        $deathsByQuarter[$year] = [
                            'Q1' => 0,
                            'Q2' => 0,
                            'Q3' => 0,
                            'Q4' => 0,
                        ];
                    }

                    // Increment the count for this year's quarter
                    if (isset($deathsByQuarter[$year][$quarter])) {
                        $deathsByQuarter[$year][$quarter]++;
                    }
                }
            }
        }

        return [
            'records' => $deathRecords->flatten(2),
            'total_deaths' => $totalDeaths,
            'deaths_by_quarter' => $deathsByQuarter,
        ];
    }

    //
    public function getCardiacDeathRecords(int $projectId): Collection // Use Illuminate's Collection
    {
        $deathRecords = ProjectData3::where('project_id', $projectId)
            ->where('field_name', 'card_outcome')
            ->where('value', 5)
            ->get()
            ->groupBy(['record', 'instance']);

        $cardiacDeaths = new Collection; // Use Illuminate's Collection

        foreach ($deathRecords as $recordId => $instances) {
            foreach ($instances as $instance => $fields) {
                $dateRecord = ProjectData3::where('project_id', $projectId)
                    ->where('record', $recordId)
                    // ->where('instance', $instance)
                    ->where('field_name', 'date_a')
                    ->first();
                // dd($dateRecord);

                if ($dateRecord && $dateRecord->value) {
                    try {
                        $deathDate = Carbon::parse($dateRecord->value);
                        $cardiacDeaths->push([
                            'record' => $recordId,
                            'death_date' => $deathDate,
                            'instance' => $instance ?? 1,
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $cardiacDeaths;
    }

    // chronic kidney
    public function getCKDDeathRecords(int $projectId): Collection // Use Illuminate's Collection
    {
        $deathRecords = ProjectData3::where('project_id', $projectId)
            ->where('field_name', 'final_outcome')
            ->where('value', 6)
            ->get()
            ->groupBy(['record', 'instance']);

        $ckdDeaths = new Collection; // Use Illuminate's Collection

        foreach ($deathRecords as $recordId => $instances) {
            foreach ($instances as $instance => $fields) {
                $dateRecord = ProjectData3::where('project_id', $projectId)
                    ->where('record', $recordId)
                    // ->where('instance', $instance)
                    ->where('field_name', 'final_outcome_date')
                    ->first();
                // dd($dateRecord);

                if ($dateRecord && $dateRecord->value) {
                    try {
                        $deathDate = Carbon::parse($dateRecord->value);
                        $ckdDeaths->push([
                            'record' => $recordId,
                            'death_date' => $deathDate,
                            'instance' => $instance ?? 1,
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $ckdDeaths;
    }

    // chronic kidney
    public function getCLDDeathRecords(int $projectId): Collection // Use Illuminate's Collection
    {
        $deathRecords = ProjectData3::where('project_id', $projectId)
            ->where('field_name', 'liver_outcome')
            ->where('value', 5)
            ->get()
            ->groupBy(['record', 'instance']);

        $cldDeaths = new Collection; // Use Illuminate's Collection

        foreach ($deathRecords as $recordId => $instances) {
            foreach ($instances as $instance => $fields) {
                $dateRecord = ProjectData3::where('project_id', $projectId)
                    ->where('record', $recordId)
                    // ->where('instance', $instance)
                    ->where('field_name', 'liver_outcome_date')
                    ->first();
                // dd($dateRecord);

                if ($dateRecord && $dateRecord->value) {
                    try {
                        $deathDate = Carbon::parse($dateRecord->value);
                        $cldDeaths->push([
                            'record' => $recordId,
                            'death_date' => $deathDate,
                            'instance' => $instance ?? 1,
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $cldDeaths;
    }

    // diabetes
    public function getDiabetesDeathRecords(int $projectId): Collection // Use Illuminate's Collection
    {
        $deathRecords = ProjectData3::where('project_id', $projectId)
            ->where('field_name', 'db_outcome')
            ->where('value', 5)
            ->get()
            ->groupBy(['record', 'instance']);

        $diabetesDeaths = new Collection; // Use Illuminate's Collection

        foreach ($deathRecords as $recordId => $instances) {
            foreach ($instances as $instance => $fields) {
                $dateRecord = ProjectData3::where('project_id', $projectId)
                    ->where('record', $recordId)
                    // ->where('instance', $instance)
                    ->where('field_name', 'outcome_date')
                    ->first();
                // dd($dateRecord);

                if ($dateRecord && $dateRecord->value) {
                    try {
                        $deathDate = Carbon::parse($dateRecord->value);
                        $diabetesDeaths->push([
                            'record' => $recordId,
                            'death_date' => $deathDate,
                            'instance' => $instance ?? 1,
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $diabetesDeaths;
    }

    // respiratory
    public function getRespiratoryDeathRecords(int $projectId): Collection // Use Illuminate's Collection
    {
        $deathRecords = ProjectData3::where('project_id', $projectId)
            ->where('field_name', 'outcome')
            ->where('value', 5)
            ->get()
            ->groupBy(['record', 'instance']);

        $respiratoryDeaths = new Collection; // Use Illuminate's Collection

        foreach ($deathRecords as $recordId => $instances) {
            foreach ($instances as $instance => $fields) {
                $dateRecord = ProjectData3::where('project_id', $projectId)
                    ->where('record', $recordId)
                    // ->where('instance', $instance)
                    ->where('field_name', 'when')
                    ->first();
                // dd($dateRecord);

                if ($dateRecord && $dateRecord->value) {
                    try {
                        $deathDate = Carbon::parse($dateRecord->value);
                        $respiratoryDeaths->push([
                            'record' => $recordId,
                            'death_date' => $deathDate,
                            'instance' => $instance ?? 1,
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $respiratoryDeaths;
    }

    // sicle cell
    public function getSickleCellDeathRecords(int $projectId): Collection // Use Illuminate's Collection
    {
        $deathRecords = ProjectData3::where('project_id', $projectId)
            ->where('field_name', 'scd_outcome')
            ->where('value', 5)
            ->get()
            ->groupBy(['record', 'instance']);

        $scdDeaths = new Collection; // Use Illuminate's Collection

        foreach ($deathRecords as $recordId => $instances) {
            foreach ($instances as $instance => $fields) {
                $dateRecord = ProjectData3::where('project_id', $projectId)
                    ->where('record', $recordId)
                    // ->where('instance', $instance)
                    ->where('field_name', 'scd_outcome_date')
                    ->first();
                // dd($dateRecord);

                if ($dateRecord && $dateRecord->value) {
                    try {
                        $deathDate = Carbon::parse($dateRecord->value);
                        $scdDeaths->push([
                            'record' => $recordId,
                            'death_date' => $deathDate,
                            'instance' => $instance ?? 1,
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $scdDeaths;
    }

    //
    public function getAllActive(int $projectId, string $condition): Collection
    {

        $cardiacActives = [
            app(ProjectCardiacDataService::class)->getActiveRespondents($projectId, 'rheumatic'),
            app(ProjectCardiacDataService::class)->getActiveRespondents($projectId, 'congenital'),
            app(ProjectCardiacDataService::class)->getActiveRespondents($projectId, 'heart_failure'),
        ];

        $ckdActives = [app(ProjectCKDDataService::class)->getActiveRespondents($projectId, $condition)];
        $cldActives = [app(ProjectCLDDataService::class)->getActiveRespondents($projectId, $condition)];

        $diabetesActives = [
            app(ProjectDiabetesDataService::class)->getActiveRespondents($projectId, 'diabetes_1'),
            app(ProjectDiabetesDataService::class)->getActiveRespondents($projectId, 'diabetes_2'),
        ];

        $respiratoryActives = [app(ProjectRespiratoryDataService::class)->getActiveRespondents($projectId, $condition)];

        $scdActives = [
            app(ProjectSickleCellDataService::class)->getActiveRespondents($projectId, 'sicle_cell'),
        ];

        return collect([
            $cardiacActives,
            $ckdActives,
            $cldActives,
            $diabetesActives,
            $respiratoryActives,
            $scdActives,
        ]);
    }

    //
    public function getAllFemaleActive(int $projectId, string $condition): Collection
    {

        $cardiacFemaleActives = [
            app(ProjectCardiacDataService::class)->getActiveFemales($projectId, 'rheumatic'),
            app(ProjectCardiacDataService::class)->getActiveFemales($projectId, 'congenital'),
            app(ProjectCardiacDataService::class)->getActiveFemales($projectId, 'heart_failure'),
        ];

        $ckdFemaleActives = [app(ProjectCKDDataService::class)->getActiveFemales($projectId, $condition)];
        $cldFemaleActives = [app(ProjectCLDDataService::class)->getActiveFemales($projectId, $condition)];

        $diabetesFemaleActives = [
            app(ProjectDiabetesDataService::class)->getActiveFemales($projectId, 'diabetes_1'),
            app(ProjectDiabetesDataService::class)->getActiveFemales($projectId, 'diabetes_2'),
        ];

        $respiratoryFemaleActives = [app(ProjectRespiratoryDataService::class)->getActiveFemales($projectId, $condition)];

        $scdFemaleActives = [
            app(ProjectSickleCellDataService::class)->getActiveFemales($projectId, 'sickle_cell'),
        ];

        return collect([
            $cardiacFemaleActives,
            $ckdFemaleActives,
            $cldFemaleActives,
            $diabetesFemaleActives,
            $respiratoryFemaleActives,
            $scdFemaleActives,
        ]);
    }

    public function getAllNewlyEnrolled(int $projectId, string $condition): Collection
    {

        $cardiacNewlyEnrolled = [
            app(ProjectCardiacDataService::class)->getEnrolledLastThreeMonths($projectId, 'rheumatic'),
            app(ProjectCardiacDataService::class)->getEnrolledLastThreeMonths($projectId, 'congenital'),
            app(ProjectCardiacDataService::class)->getEnrolledLastThreeMonths($projectId, 'heart_failure'),
        ];

        $ckdNewlyEnrolled = [app(ProjectCKDDataService::class)->getEnrolledLastThreeMonths($projectId, $condition)];
        $cldNewlyEnrolled = [app(ProjectCLDDataService::class)->getEnrolledLastThreeMonths($projectId, $condition)];

        $diabetesNewlyEnrolled = [
            app(ProjectDiabetesDataService::class)->getEnrolledLastThreeMonths($projectId, 'diabetes_1'),
            app(ProjectDiabetesDataService::class)->getEnrolledLastThreeMonths($projectId, 'diabetes_2'),
        ];

        $respiratoryNewlyEnrolled = [app(ProjectRespiratoryDataService::class)->getEnrolledLastThreeMonths($projectId, $condition)];

        $scdNewlyEnrolled = [
            app(ProjectSickleCellDataService::class)->getEnrolledLastThreeMonths($projectId, 'sicle_cell'),
        ];

        return collect([
            $cardiacNewlyEnrolled,
            $ckdNewlyEnrolled,
            $cldNewlyEnrolled,
            $diabetesNewlyEnrolled,
            $respiratoryNewlyEnrolled,
            $scdNewlyEnrolled,
        ]);
    }

    public function getAllNewlyLTFU(int $projectId, string $condition): Collection
    {

        $cardiacNewlyLTFU = [
            app(ProjectCardiacDataService::class)->getNewlyLTFU($projectId, 'rheumatic'),
            app(ProjectCardiacDataService::class)->getNewlyLTFU($projectId, 'congenital'),
            app(ProjectCardiacDataService::class)->getNewlyLTFU($projectId, 'heart_failure'),
        ];

        $ckdNewlyLTFU = [app(ProjectCKDDataService::class)->getNewlyLTFU($projectId, $condition)];
        $cldNewlyLTFU = [app(ProjectCLDDataService::class)->getNewlyLTFU($projectId, $condition)];

        $diabetesNewlyLTFU = [
            app(ProjectDiabetesDataService::class)->getNewlyLTFU($projectId, 'diabetes_1'),
            app(ProjectDiabetesDataService::class)->getNewlyLTFU($projectId, 'diabetes_2'),
        ];

        $respiratoryNewlyLTFU = [app(ProjectRespiratoryDataService::class)->getNewlyLTFU($projectId, $condition)];

        $scdNewlyLTFU = [
            app(ProjectSickleCellDataService::class)->getNewlyLTFU($projectId, 'sicle_cell'),
        ];

        return collect([
            $cardiacNewlyLTFU,
            $ckdNewlyLTFU,
            $cldNewlyLTFU,
            $diabetesNewlyLTFU,
            $respiratoryNewlyLTFU,
            $scdNewlyLTFU,
        ]);
    }

    public function getAllNewlyDeaths(int $projectId, string $condition): Collection
    {

        $cardiacNewlyDeaths = [
            app(ProjectCardiacDataService::class)->getRecentCardiacDeaths($projectId, 'rheumatic'),
            app(ProjectCardiacDataService::class)->getRecentCardiacDeaths($projectId, 'congenital'),
            app(ProjectCardiacDataService::class)->getRecentCardiacDeaths($projectId, 'heart_failure'),
        ];

        $ckdNewlyDeaths = [app(ProjectCKDDataService::class)->getRecentCKDDeaths($projectId, $condition)];
        $cldNewlyDeaths = [app(ProjectCLDDataService::class)->getRecentCLDDeaths($projectId, $condition)];

        $diabetesNewlyDeaths = [
            app(ProjectDiabetesDataService::class)->getRecentDiabetesDeaths($projectId, 'diabetes_1'),
            app(ProjectDiabetesDataService::class)->getRecentDiabetesDeaths($projectId, 'diabetes_2'),
        ];

        $respiratoryNewlyDeaths = [app(ProjectRespiratoryDataService::class)->getRecentRespiratoryDeaths($projectId, $condition)];

        $scdNewlyDeaths = [
            app(ProjectSickleCellDataService::class)->getRecentSickleCellDeaths($projectId, 'sicle_cell'),
        ];

        return collect([
            $cardiacNewlyDeaths,
            $ckdNewlyDeaths,
            $cldNewlyDeaths,
            $diabetesNewlyDeaths,
            $respiratoryNewlyDeaths,
            $scdNewlyDeaths,
        ]);
    }

    //

    public function countDistinctRecordsByField(Project $project, string $fieldName): int
    {
        return $project->project_data()
            ->where('field_name', $fieldName)
            ->whereNotNull('value')
            ->distinct('record')
            ->count('record');
    }

    public function getTotalRespondents(Project $project): int
    {
        return $project->project_data()->distinct('record')->count('record');
    }

    //
        public function countByField(Project $project, string $fieldName): int
    {
        return $project->project_data()
            ->where('field_name', $fieldName)
            ->whereNotNull('value')
            ->distinct('record')
            ->count('record');
    }

     public function getFacilityBreakdown(Project $project, string $fieldName = null): array
    {
        $query = $project->project_data();
        
        if ($fieldName) {
            $query->where('field_name', $fieldName)
                  ->whereNotNull('value');
        }
        
       // dd($query);

        return $query->where('field_name', 'demog_facility')
            ->groupBy('value')
            ->select('value', DB::raw('count(*) as count'))
            ->pluck('count', 'value')
            ->toArray();
    }

    public function getMetricsWithFacilityBreakdown(Project $project): array
    {
        $metrics = [
            'outPatients' => 'opd_date',
            'stiPatients' => 'sti_date',
            'fpPatients' => 'fp_date',
            'ancrPatients' => 'ancr_date',
            'pncrPatients' => 'pncr_date',
            'prepPatients' => 'prepr_date',
            'mentalHealthPatients' => 'mh_screening_tools',
            'healthEducationPatients' => 'he_receive',
            'counselingPatients' => 'couns_receive',
            'artPatients' => 'artr_registration_date',
        ];

        $results = [];
        
        foreach ($metrics as $key => $field) {
            $results[$key] = [
                'total' => $this->countByField($project, $field),
                'byFacility' => $this->getFacilityBreakdown($project, $field)
            ];
        }

        return $results;
    }

public function getAllMetricsWithFacilityBreakdown(int $projectId): array
{
    $metrics = [
        'outPatients' => 'opd_date',
        'stiPatients' => 'sti_date',
        'fpPatients' => 'fp_date',
        'ancrPatients' => 'ancr_date',
        'pncrPatients' => 'pncr_date',
        'prepPatients' => 'prepr_date',
        'mentalHealthPatients' => 'mh_screening_tools',
        'healthEducationPatients' => 'he_receive',
        'counselingPatients' => 'couns_receive',
        'artPatients' => 'artr_registration_date',
    ];

    $results = [];
    
    // Get all records that have facility information
    $facilities = DB::table('redcap_data3')
        ->where('project_id', $projectId)
        ->where('field_name', 'demog_facility')
        ->pluck('value', 'record')
        ->toArray();

        //dd($projectId,$facilities);
    
    // For each metric, count records and group by facility
    foreach ($metrics as $key => $field) {
        $records = DB::table('redcap_data3')
            ->where('project_id', $projectId)
            ->where('field_name', $field)
            ->whereNotNull('value')
            ->pluck('record')
            ->toArray();
        
        $byFacility = [];
        $total = 0;
        
        foreach ($records as $record) {
            if (isset($facilities[$record])) {
                $facility = $facilities[$record];
                $byFacility[$facility] = isset($byFacility[$facility]) ? $byFacility[$facility] + 1 : 1;
                $total++;
            }
        }
        
        $results[$key] = [
            'total' => $total,
            'byFacility' => $byFacility
        ];
    }

    return $results;
}

    /**
     * Clean and normalize HbA1c values
     */
    protected function cleanHba1cValue($value)
    {
        if (is_null($value)) {
            return null;
        }

        $value = trim($value);

        // Remove percentage signs
        $value = str_replace('%', '', $value);

        // Handle "Hi" or "High" values
        if (strtolower($value) === 'hi' || strtolower($value) === 'high') {
            return null;
        }

        // Convert to float if numeric
        return is_numeric($value) ? (float) $value : null;
    }
}
