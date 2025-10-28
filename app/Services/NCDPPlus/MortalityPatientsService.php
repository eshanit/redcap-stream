<?php

namespace App\Services\NCDPPlus;

use App\Models\Project;
use App\Models\ProjectData3;
use Illuminate\Support\Collection;

class MortalityPatientsService
{
    const PROJECT_ID = 39;
    const DEMOGRAPHIC_FIELDS = ['gender_demo', 'facility_demo', 'age_demo'];

    // Define outcome fields and their corresponding disease types
    const OUTCOME_FIELDS = [
        'dm_outcome' => 'diabetes',
        'card_outcome' => 'cardiac', 
        'scd_outcome' => 'sickle_cell',
        'outcome' => 'respiratory',
        'ckd_outcome' => 'kidney',
        'liver_outcome' => 'liver'
    ];

    // Define diagnosis fields for each disease type
    const DISEASE_DIAGNOSIS_FIELDS = [
        'diabetes' => [
            'field' => 'db_diagnosis',
            'values' => [
                'type1' => ['1'],
                'type2' => ['2'], 
                'unspecified' => ['3', '5']
            ]
        ],
        'cardiac' => [
            'field' => 'card_diagnosis',
            'values' => [
                'rheumatic' => ['2'],
                'congenital' => ['5'],
                'heart_failure' => ['1', '6'],
                'other_cardiac' => ['2', '3', '4', '7', '8', '9', '10', '11']
            ]
        ],
        'sickle_cell' => [
            'field' => 'scd_diagnosis',
            'values' => [
                'sickle_cell' => ['1']
            ]
        ],
        'respiratory' => [
            'field' => 'main_diagnosis', 
            'values' => [
                'chronic_respiratory' => ['1', '2', '3']
            ]
        ],
        'kidney' => [
            'field' => 'pat_main_diagnosis',
            'values' => [
                'chronic_kidney' => ['10']
            ]
        ],
        'liver' => [
            'field' => 'liver_main_diagnosis',
            'values' => [
                'chronic_liver' => ['1', '2']
            ]
        ]
    ];

    /**
     * Get base project query
     */
    public function getProject(): Project
    {
        return Project::query()
            ->select('project_id', 'app_title')
            ->findOrFail(self::PROJECT_ID);
    }

    /**
     * Get ALL mortality patients (all diseases)
     */
public function getAllMortalityPatients(): Collection
{
    // Define outcome date fields for each disease type
    $outcomeDateFields = [
        'db_visit_date' => 'diabetes',
        'card_death_date' => 'cardiac',
        'scd_outcome_date' => 'sickle_cell',
        'when' => 'respiratory',
        'ckd_visit_date' => 'kidney',
        'liver_outcome_date' => 'liver'
    ];

    // Step 1: Find all death outcomes (value = 5)
    $deathOutcomes = ProjectData3::query()
        ->where('project_id', self::PROJECT_ID)
        ->whereIn('field_name', array_keys(self::OUTCOME_FIELDS))
        ->where('value', '5')
        ->get();

    if ($deathOutcomes->isEmpty()) {
        return collect([]);
    }

    // Step 2: Get all records that have death outcomes
    $records = $deathOutcomes->pluck('record')->unique();
    $instances = $deathOutcomes->pluck('instance')->unique();

    // Step 3: Get ALL data for these records (demographics + visit data + outcome date fields)
    $allPatientData = ProjectData3::query()
        ->where('project_id', self::PROJECT_ID)
        ->whereIn('record', $records)
        ->whereIn('field_name', array_merge(
            self::DEMOGRAPHIC_FIELDS,
            array_keys(self::OUTCOME_FIELDS),
            array_keys($outcomeDateFields) // Include the outcome date fields
        ))
        ->get();

    // Step 4: Separate demographics from visit data like ActivePatientsService
    $demographics = $allPatientData
        ->whereIn('field_name', self::DEMOGRAPHIC_FIELDS)
        ->where(function ($item) {
            return $item->instance === '' || $item->instance === '1' || $item->instance === null;
        })
        ->groupBy('record');

    $visitData = $allPatientData
        ->whereIn('field_name', array_merge(
            array_keys(self::OUTCOME_FIELDS),
            array_keys($outcomeDateFields)
        ))
        ->groupBy(['record', 'instance']);

    $allPatients = collect();

    // Step 5: Build patient data for each death outcome
    foreach ($deathOutcomes as $outcomeRecord) {
        $record = $outcomeRecord->record;
        $instance = $outcomeRecord->instance ?: '1';

        $patientData = [
            'record' => $record,
            'instance' => $instance,
        ];

        // Add demographics
        $recordDemos = $demographics->get($record);
        if ($recordDemos) {
            foreach ($recordDemos as $demo) {
                $patientData[$demo->field_name] = $demo->value;
            }
        }

        // Add visit data for this specific instance
        $instanceVisits = $visitData->get($record, collect())->get($instance, collect());
        foreach ($instanceVisits as $visit) {
            $patientData[$visit->field_name] = $visit->value;
        }

        // Only include patients who have at least some data (not just record and instance)
        if (count($patientData) > 2) {
            // Determine disease type
            $patientData['disease_type'] = $this->determineDiseaseType($outcomeRecord, $patientData);
            $patientData['disease_name'] = $this->getDiseaseDisplayName($patientData['disease_type']);
            
            // Add mortality date based on disease type
            $patientData['mortality_date'] = $this->getMortalityDate($patientData, $outcomeDateFields);
            
            $allPatients->push($patientData);
        }
    }

   // dd( $allPatients );

    return $allPatients;
}

/**
 * Get mortality date based on disease type and outcome date fields
 */
private function getMortalityDate(array $patientData, array $outcomeDateFields): ?string
{
    $diseaseCategory = $patientData['disease_type'] ?? '';
    
    // Map specific disease types to general categories for date field lookup
    $categoryMapping = [
        'type1' => 'diabetes',
        'type2' => 'diabetes',
        'unspecified' => 'diabetes',
        'rheumatic' => 'cardiac',
        'congenital' => 'cardiac', 
        'heart_failure' => 'cardiac',
        'other_cardiac' => 'cardiac',
        'sickle_cell' => 'sickle_cell',
        'chronic_respiratory' => 'respiratory',
        'chronic_kidney' => 'kidney',
        'chronic_liver' => 'liver',
    ];
    
    $generalCategory = $categoryMapping[$diseaseCategory] ?? $diseaseCategory;
    
    // Find the date field for this disease category
    $dateField = array_search($generalCategory, $outcomeDateFields);
    
    if ($dateField && isset($patientData[$dateField])) {
        return $patientData[$dateField];
    }
    
    return null;
}
    /**
     * Get mortality patients for a specific disease type
     */
    public function getMortalityPatients(
        string $diagnosisField,
        string $outcomeField,
        array $diagnosisValues,
        array $visitFields
    ): Collection {
        // Find death outcomes for this specific outcome field
        $deathOutcomes = ProjectData3::query()
            ->where('project_id', self::PROJECT_ID)
            ->where('field_name', $outcomeField)
            ->where('value', '5')
            ->get();

        if ($deathOutcomes->isEmpty()) {
            return collect([]);
        }

        $patients = collect();

        foreach ($deathOutcomes as $outcomeRecord) {
            // Get the diagnosis for this record and instance
            $diagnosis = ProjectData3::query()
                ->where('project_id', self::PROJECT_ID)
                ->where('record', $outcomeRecord->record)
               // ->where('instance', $outcomeRecord->instance)
                ->where('field_name', $diagnosisField)
                ->whereIn('value', $diagnosisValues)
                ->first();

            if ($diagnosis) {
                $patientData = $this->buildPatientData($outcomeRecord, $visitFields);
                if ($patientData) {
                    $patients->push($patientData);
                }
            }
        }

        return $patients;
    }

    /**
     * Build patient data from outcome record - FIXED to match ActivePatients structure
     */
    private function buildPatientData($outcomeRecord, array $additionalFields = []): ?array
    {
        $record = $outcomeRecord->record;
        $instance = $outcomeRecord->instance ?: '1';

        // Get all data for this record (both demographics and visit data)
        $allPatientData = ProjectData3::query()
            ->where('project_id', self::PROJECT_ID)
            ->where('record', $record)
            ->whereIn('field_name', array_merge(
                self::DEMOGRAPHIC_FIELDS,
                array_keys(self::OUTCOME_FIELDS),
                $additionalFields
            ))
            ->get();

        // Separate demographics from visit data like ActivePatientsService does
        $demographics = $allPatientData
            ->whereIn('field_name', self::DEMOGRAPHIC_FIELDS)
            ->where(function ($item) {
                return $item->instance === '' || $item->instance === '1' || $item->instance === null;
            })
            ->groupBy('record');

        $visitData = $allPatientData
            ->whereIn('field_name', array_merge(array_keys(self::OUTCOME_FIELDS), $additionalFields))
            ->groupBy(['record', 'instance']);

        // Build the patient data structure like ActivePatientsService
        $patientData = [
            'record' => $record,
            'instance' => $instance === '' || $instance === null ? '1' : $instance,
        ];

        // Add demographics
        $recordDemos = $demographics->get($record);
        if ($recordDemos) {
            foreach ($recordDemos as $demo) {
                $patientData[$demo->field_name] = $demo->value;
            }
        }

        // Add visit data for this specific instance
        $instanceVisits = $visitData->get($record, collect())->get($instance, collect());
        foreach ($instanceVisits as $visit) {
            $patientData[$visit->field_name] = $visit->value;
        }

        // If we have no data at all, return null
        if (count($patientData) <= 2) { // Only has record and instance
            return null;
        }

        // Determine disease type
        $patientData['disease_type'] = $this->determineDiseaseType($outcomeRecord, $patientData);
        $patientData['disease_name'] = $this->getDiseaseDisplayName($patientData['disease_type']);

        return $patientData;
    }

    /**
     * Determine the specific disease type based on outcome field and diagnosis
     */
    private function determineDiseaseType($outcomeRecord, array $patientData): string
    {
        $outcomeField = $outcomeRecord->field_name;
        $diseaseCategory = self::OUTCOME_FIELDS[$outcomeField] ?? 'unknown';

        // If we can't determine the category from outcome field, return unknown
        if ($diseaseCategory === 'unknown') {
            return 'unknown';
        }

        // Get the diagnosis configuration for this disease category
        $diagnosisConfig = self::DISEASE_DIAGNOSIS_FIELDS[$diseaseCategory] ?? null;
        if (!$diagnosisConfig) {
            return $diseaseCategory;
        }

        $diagnosisField = $diagnosisConfig['field'];
        $diagnosisValue = $patientData[$diagnosisField] ?? null;

        // Find which specific disease type matches this diagnosis value
        foreach ($diagnosisConfig['values'] as $diseaseType => $values) {
            if ($diagnosisValue && in_array($diagnosisValue, $values)) {
                return $diseaseType;
            }
        }

        // If no specific type found, return the general category
        return $diseaseCategory;
    }

    /**
     * Get display name for disease type
     */
    private function getDiseaseDisplayName(string $diseaseType): string
    {
        $names = [
            'type1' => 'Type I Diabetes',
            'type2' => 'Type II Diabetes', 
            'unspecified' => 'Unspecified Diabetes',
            'rheumatic' => 'Rheumatic Heart Disease',
            'congenital' => 'Congenital Heart Disease',
            'heart_failure' => 'Heart Failure',
            'other_cardiac' => 'Other Cardiac Diseases',
            'sickle_cell' => 'Sickle Cell Disease',
            'chronic_respiratory' => 'Chronic Respiratory Disease',
            'chronic_kidney' => 'Chronic Kidney Disease',
            'chronic_liver' => 'Chronic Liver Disease',
            'diabetes' => 'Diabetes',
            'cardiac' => 'Cardiac Disease',
            'respiratory' => 'Respiratory Disease',
            'kidney' => 'Kidney Disease', 
            'liver' => 'Liver Disease',
            'unknown' => 'Unknown Disease'
        ];

        return $names[$diseaseType] ?? $diseaseType;
    }

    /**
     * Get summary statistics for all mortality patients
     */
    public function getAllMortalitySummary(): array
    {
        $patients = $this->getAllMortalityPatients();

        if ($patients->isEmpty()) {
            return [
                'total_patients' => 0,
                'by_gender' => collect(),
                'by_facility' => collect(),
                'by_disease' => collect(),
                'average_age' => 0,
            ];
        }

        return [
            'total_patients' => $patients->count(),
            'by_gender' => $patients->groupBy('gender_demo')->map->count(),
            'by_facility' => $patients->groupBy('facility_demo')->map->count(),
            'by_disease' => $patients->groupBy('disease_name')->map->count(),
            'average_age' => $patients->where('age_demo', '!=', '')->average('age_demo'),
        ];
    }

    /**
     * Get summary for specific disease type
     */
    public function getMortalityPatientsSummary(
        string $diagnosisField,
        string $outcomeField,
        array $diagnosisValues,
        array $visitFields
    ): array {
        $patients = $this->getMortalityPatients($diagnosisField, $outcomeField, $diagnosisValues, $visitFields);

        if ($patients->isEmpty()) {
            return [
                'total_patients' => 0,
                'by_gender' => collect(),
                'by_facility' => collect(),
                'average_age' => 0,
            ];
        }

        return [
            'total_patients' => $patients->count(),
            'by_gender' => $patients->groupBy('gender_demo')->map->count(),
            'by_facility' => $patients->groupBy('facility_demo')->map->count(),
            'average_age' => $patients->where('age_demo', '!=', '')->average('age_demo'),
        ];
    }
}