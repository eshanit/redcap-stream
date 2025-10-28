<?php

namespace App\Services\NCDPPlus;

use App\Models\Project;
use App\Models\ProjectData3;
use Illuminate\Support\Collection;

class ActivePatientsService
{
    const PROJECT_ID = 39;
    const DEMOGRAPHIC_FIELDS = ['gender_demo', 'facility_demo', 'age_demo'];

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
     * Get active patients data for a specific disease type
     */
    public function getActivePatients(
        string $diagnosisField,
        string $outcomeField,
        array $diagnosisValues,
        array $visitFields
    ): Collection {
        // Get qualifying records
        $qualifyingRecords = $this->getQualifyingRecords($diagnosisField, $outcomeField, $diagnosisValues);

        if ($qualifyingRecords->isEmpty()) {
            return collect([]);
        }

        // Process patient data
        return $this->processPatientData($qualifyingRecords, $diagnosisField, $outcomeField, $diagnosisValues, $visitFields);
    }

    /**
     * Get records that qualify based on diagnosis and outcome
     */
    private function getQualifyingRecords(string $diagnosisField, string $outcomeField, array $diagnosisValues): Collection
    {
        return ProjectData3::query()
            ->from('redcap_data3 as pd1')
            ->join('redcap_data3 as pd2', function ($join) use ($outcomeField) {
                $join->on('pd1.record', '=', 'pd2.record')
                    ->where('pd2.field_name', $outcomeField)
                    ->where('pd2.value', '1');
            })
            ->where('pd1.project_id', self::PROJECT_ID)
            ->where('pd1.field_name', $diagnosisField)
            ->whereIn('pd1.value', $diagnosisValues)
            ->select('pd1.record')
            ->distinct()
            ->pluck('record');
    }

    /**
     * Process patient data for qualifying records
     */
    private function processPatientData(
        Collection $qualifyingRecords,
        string $diagnosisField,
        string $outcomeField,
        array $diagnosisValues,
        array $visitFields
    ): Collection {
        // Get all data for qualifying records
        $allPatientData = ProjectData3::query()
            ->where('project_id', self::PROJECT_ID)
            ->whereIn('record', $qualifyingRecords)
            ->whereIn('field_name', array_merge(self::DEMOGRAPHIC_FIELDS, $visitFields))
            ->get();

        // Separate demographics from visit data
        $demographics = $allPatientData
            ->whereIn('field_name', self::DEMOGRAPHIC_FIELDS)
            ->where(function ($item) {
                return $item->instance === '' || $item->instance === '1' || $item->instance === null;
            })
            ->groupBy('record');

        $visitData = $allPatientData
            ->whereIn('field_name', $visitFields)
            ->groupBy(['record', 'instance']);

        // Find qualifying instances
        $qualifyingInstances = $this->findQualifyingInstances(
            $allPatientData, 
            $diagnosisField, 
            $diagnosisValues, 
            $outcomeField
        );

        // Combine data
        return $qualifyingInstances->map(function ($instance) use ($demographics, $visitData) {
            return $this->buildPatientData($instance, $demographics, $visitData);
        });
    }

    /**
     * Find instances that qualify based on diagnosis and outcome
     */
    private function findQualifyingInstances(
        Collection $allPatientData, 
        string $diagnosisField, 
        array $diagnosisValues, 
        string $outcomeField
    ): Collection {
        return $allPatientData
            ->where('field_name', $diagnosisField)
            ->whereIn('value', $diagnosisValues)
            ->filter(function ($item) use ($allPatientData, $outcomeField) {
                return $allPatientData
                    ->where('record', $item->record)
                    ->where('instance', $item->instance)
                    ->where('field_name', $outcomeField)
                    ->where('value', '1')
                    ->isNotEmpty();
            })
            ->map(function ($item) {
                return (object) [
                    'record' => $item->record,
                    'instance' => $item->instance,
                ];
            })
            ->unique();
    }

    /**
     * Build patient data array for a specific instance
     */
    private function buildPatientData(object $instance, Collection $demographics, Collection $visitData): array
    {
        $patientData = [
            'record' => $instance->record,
            'instance' => $instance->instance === '' || $instance->instance === null ? '1' : $instance->instance,
        ];

        // Add demographics
        $recordDemos = $demographics->get($instance->record);
        if ($recordDemos) {
            foreach ($recordDemos as $demo) {
                $patientData[$demo->field_name] = $demo->value;
            }
        }

        // Add visit data
        $instanceVisits = $visitData->get($instance->record, collect())->get($instance->instance, collect());
        foreach ($instanceVisits as $visit) {
            $patientData[$visit->field_name] = $visit->value;
        }

        return $patientData;
    }

    /**
     * Get summary statistics for active patients
     */
    public function getActivePatientsSummary(
        string $diagnosisField,
        string $outcomeField,
        array $diagnosisValues,
        array $visitFields
    ): array {
        $patients = $this->getActivePatients($diagnosisField, $outcomeField, $diagnosisValues, $visitFields);

        return [
            'total_patients' => $patients->count(),
            'by_gender' => $patients->groupBy('gender_demo')->map->count(),
            'by_facility' => $patients->groupBy('facility_demo')->map->count(),
            'average_age' => $patients->where('age_demo', '!=', '')->average('age_demo'),
        ];
    }
}