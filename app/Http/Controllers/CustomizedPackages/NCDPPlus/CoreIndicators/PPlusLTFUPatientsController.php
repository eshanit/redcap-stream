<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Services\NCDPPlus\LTFUPatientsService;
use Inertia\Inertia;

class PPlusLTFUPatientsController extends Controller
{
    private LTFUPatientsService $LTFUPatientsService;

    public function __construct(LTFUPatientsService $LTFUPatientsService)
    {
        $this->LTFUPatientsService = $LTFUPatientsService;
    }

    public function index()
    {
        $project = $this->LTFUPatientsService->getProject();
        return Inertia::render('Customizations/NCDPPlus/CoreIndicators/PPlusLTFUPatients', [
            'project' => $project,
        ]);
    }

    /** Type I Diabetes */
    public function type1Diabetes()
    {
        return $this->renderReport(
            'db_diagnosis',
            'dm_outcome',
            ['1'],
            ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            'Type1Diabetes'
        );
    }

    /** Type II Diabetes */
    public function type2Diabetes()
    {
        return $this->renderReport(
            'db_diagnosis',
            'dm_outcome',
            ['2'],
            ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            'Type2Diabetes'
        );
    }

    /** Unspecified Diabetes */
    public function unspecifiedDiabetes()
    {
        return $this->renderReport(
            'db_diagnosis',
            'dm_outcome',
            ['3', '5'],
            ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            'UnspecifiedDiabetes',
            'LTFUPatientsData'
        );
    }

    /** Rheumatic Heart Disease */
    public function rheumatic()
    {
        return $this->renderReport(
            'card_diagnosis',
            'card_outcome',
            ['2'],
            ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            'Rheumatic'
        );
    }

    /** Congenital Heart Disease */
    public function congenital()
    {
        return $this->renderReport(
            'card_diagnosis',
            'card_outcome',
            ['5'],
            ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            'Congenital'
        );
    }

    /** Heart Failure */
    public function heartFailure()
    {
        return $this->renderReport(
            'card_diagnosis',
            'card_outcome',
            ['1', '6'],
            ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            'HeartFailure'
        );
    }

    /** Other Cardiac */
    public function otherCardiac()
    {
        return $this->renderReport(
            'card_diagnosis',
            'card_outcome',
            ['2', '3', '4', '7', '8', '9', '10', '11'],
            ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            'OtherCardiac'
        );
    }

    /** Sickle Cell */
    public function sickleCell()
    {
        return $this->renderReport(
            'scd_diagnosis',
            'scd_outcome',
            ['1'],
            ['scd_visit_date', 'scd_appo_date', 'scd_outcome', 'scd_diagnosis'],
            'SickleCell'
        );
    }

    /** Chronic Respiratory Disease */
    public function chronicRespiratoryDisease()
    {
        return $this->renderReport(
            'main_diagnosis',
            'outcome',
            ['1', '2', '3'],
            ['res_visit_date', 'nxt_appo_date', 'outcome', 'main_diagnosis'],
            'ChronicRespDisease'
        );
    }

    /** Chronic Kidney Disease */
    public function chronicKidneyDisease()
    {
        return $this->renderReport(
            'pat_main_diagnosis',
            'ckd_outcome',
            ['10'],
            ['ckd_visit_date', 'meds_next_appo_date', 'ckd_outcome', 'pat_main_diagnosis'],
            'ChronicKidneyDisease'
        );
    }

    /** Chronic Liver Disease */
    public function chronicLiverDisease()
    {
        return $this->renderReport(
            'liver_main_diagnosis',
            'liver_outcome',
            ['1', '2'],
            ['liver_date', 'liver_next_appo_date', 'liver_outcome', 'liver_main_diagnosis'],
            'ChronicLiverDisease'
        );
    }

    /**
     * Render report using service
     */
    private function renderReport(
        string $diagnosisField,
        string $outcomeField,
        array $diagnosisValues,
        array $visitFields,
        string $viewName,
        string $patientsVariable = 'patients'
    ) {
        $project = $this->LTFUPatientsService->getProject();
        $patients = $this->LTFUPatientsService->getLTFUPatients(
            $diagnosisField,
            $outcomeField,
            $diagnosisValues,
            $visitFields
        );

        return Inertia::render("Customizations/NCDPPlus/CoreIndicators/Reports/LTFU/{$viewName}", [
            'project' => $project,
            $patientsVariable => $patients,
        ]);
    }

      /** All LTFU Patients */
  // Add this method to your PPlusLTFUPatientsController
    public function allLTFUPatients()
    {
        $project = $this->LTFUPatientsService->getProject();
        
        // Get all disease configurations
        $diseaseConfigs = $this->getAllDiseaseConfigs();
        
        $allPatients = collect();
        
        foreach ($diseaseConfigs as $diseaseType => $config) {
            $patients = $this->LTFUPatientsService->getLTFUPatients(
                $config['diagnosis_field'],
                $config['outcome_field'],
                $config['diagnosis_values'],
                $config['visit_fields']
            );
            
            // Add disease type to each patient record for identification
            $patients = $patients->map(function ($patient) use ($diseaseType) {
                $patient['disease_type'] = $diseaseType;
                $patient['disease_name'] = $this->getDiseaseDisplayName($diseaseType);
                return $patient;
            });
            
            $allPatients = $allPatients->merge($patients);
        }

        // Remove duplicates based on record and instance
        $allPatients = $allPatients->unique(function ($patient) {
            return $patient['record'] . '_' . $patient['instance'];
        });

        return Inertia::render('Customizations/NCDPPlus/CoreIndicators/Reports/LTFU/AllLTFUPatients', [
            'project' => $project,
            'patients' => $allPatients,
        ]);
    }
    /**
     * Get summary statistics for all LTFU patients
     */
    public function getAllSummary()
    {
        $diseaseConfigs = $this->getAllDiseaseConfigs();
        
        $totalSummary = [
            'total_patients' => 0,
            'by_gender' => collect(),
            'by_facility' => collect(),
            'by_disease' => collect(),
            'average_age' => 0,
        ];

        $allAges = collect();
        $allGenders = collect();
        $allFacilities = collect();
        $diseaseCounts = collect();

        foreach ($diseaseConfigs as $diseaseType => $config) {
            $summary = $this->LTFUPatientsService->getLTFUPatientsSummary(
                $config['diagnosis_field'],
                $config['outcome_field'],
                $config['diagnosis_values'],
                $config['visit_fields']
            );

            $totalSummary['total_patients'] += $summary['total_patients'];
            
            // Merge gender counts
            foreach ($summary['by_gender'] as $gender => $count) {
                $allGenders[$gender] = ($allGenders[$gender] ?? 0) + $count;
            }
            
            // Merge facility counts
            foreach ($summary['by_facility'] as $facility => $count) {
                $allFacilities[$facility] = ($allFacilities[$facility] ?? 0) + $count;
            }
            
            // Collect disease-specific counts
            $diseaseCounts[$this->getDiseaseDisplayName($diseaseType)] = $summary['total_patients'];
            
            // Collect ages for average calculation
            if ($summary['average_age']) {
                $allAges->push($summary['average_age']);
            }
        }

        $totalSummary['by_gender'] = $allGenders;
        $totalSummary['by_facility'] = $allFacilities;
        $totalSummary['by_disease'] = $diseaseCounts;
        $totalSummary['average_age'] = $allAges->isNotEmpty() ? $allAges->avg() : 0;

        return response()->json($totalSummary);
    }

    /**
     * Get all disease configurations
     */
    private function getAllDiseaseConfigs(): array
    {
        return [
            'type1_diabetes' => [
                'diagnosis_field' => 'db_diagnosis',
                'outcome_field' => 'dm_outcome',
                'diagnosis_values' => ['1'],
                'visit_fields' => ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            ],
            'type2_diabetes' => [
                'diagnosis_field' => 'db_diagnosis',
                'outcome_field' => 'dm_outcome',
                'diagnosis_values' => ['2'],
                'visit_fields' => ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            ],
            'unspecified_diabetes' => [
                'diagnosis_field' => 'db_diagnosis',
                'outcome_field' => 'dm_outcome',
                'diagnosis_values' => ['3', '5'],
                'visit_fields' => ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            ],
            'rheumatic' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['2'],
                'visit_fields' => ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'congenital' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['5'],
                'visit_fields' => ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'heart_failure' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['1', '6'],
                'visit_fields' => ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'other_cardiac' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['2', '3', '4', '7', '8', '9', '10', '11'],
                'visit_fields' => ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'sickle_cell' => [
                'diagnosis_field' => 'scd_diagnosis',
                'outcome_field' => 'scd_outcome',
                'diagnosis_values' => ['1'],
                'visit_fields' => ['scd_visit_date', 'scd_appo_date', 'scd_outcome', 'scd_diagnosis'],
            ],
            'chronic_respiratory' => [
                'diagnosis_field' => 'main_diagnosis',
                'outcome_field' => 'outcome',
                'diagnosis_values' => ['1', '2', '3'],
                'visit_fields' => ['res_visit_date', 'nxt_appo_date', 'outcome', 'main_diagnosis'],
            ],
            'chronic_kidney' => [
                'diagnosis_field' => 'pat_main_diagnosis',
                'outcome_field' => 'ckd_outcome',
                'diagnosis_values' => ['10'],
                'visit_fields' => ['ckd_visit_date', 'meds_next_appo_date', 'ckd_outcome', 'pat_main_diagnosis'],
            ],
            'chronic_liver' => [
                'diagnosis_field' => 'liver_main_diagnosis',
                'outcome_field' => 'liver_outcome',
                'diagnosis_values' => ['1', '2'],
                'visit_fields' => ['liver_date', 'liver_next_appo_date', 'liver_outcome', 'liver_main_diagnosis'],
            ],
        ];
    }

    /**
     * Get display name for disease type
     */
    private function getDiseaseDisplayName(string $diseaseType): string
    {
        $names = [
            'type1_diabetes' => 'Type I Diabetes',
            'type2_diabetes' => 'Type II Diabetes',
            'unspecified_diabetes' => 'Unspecified Diabetes',
            'rheumatic' => 'Rheumatic Heart Disease',
            'congenital' => 'Congenital Heart Disease',
            'heart_failure' => 'Heart Failure',
            'other_cardiac' => 'Other Cardiac Diseases',
            'sickle_cell' => 'Sickle Cell Disease',
            'chronic_respiratory' => 'Chronic Respiratory Disease',
            'chronic_kidney' => 'Chronic Kidney Disease',
            'chronic_liver' => 'Chronic Liver Disease',
        ];

        return $names[$diseaseType] ?? $diseaseType;
    }

    /**
     * Get summary statistics for any disease type
     */
    public function getSummary(string $diseaseType)
    {
        $config = $this->getDiseaseConfig($diseaseType);
        
        if (!$config) {
            return response()->json(['error' => 'Disease type not found'], 404);
        }

        $summary = $this->LTFUPatientsService->getLTFUPatientsSummary(
            $config['diagnosis_field'],
            $config['outcome_field'],
            $config['diagnosis_values'],
            $config['visit_fields']
        );

        return response()->json($summary);
    }

    /**
     * Configuration for different disease types
     */
    private function getDiseaseConfig(string $diseaseType): ?array
    {
        $configs = [
            'type1_diabetes' => [
                'diagnosis_field' => 'db_diagnosis',
                'outcome_field' => 'dm_outcome',
                'diagnosis_values' => ['1'],
                'visit_fields' => ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            ],
            'type2_diabetes' => [
                'diagnosis_field' => 'db_diagnosis',
                'outcome_field' => 'dm_outcome',
                'diagnosis_values' => ['2'],
                'visit_fields' => ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            ],
            'uspecified_diabetes' => [
                'diagnosis_field' => 'db_diagnosis',
                'outcome_field' => 'dm_outcome',
                'diagnosis_values' => ['3', '5'],
                'visit_fields' => ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            ],
            'rheumatic' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['2'],
                'visit_fields' => ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'congenital' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['5'],
                'visit_fields' => ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'heart_failure' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['1', '6'],
                'visit_fields' => ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'other_cardiac' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['2', '3', '4', '7', '8', '9', '10', '11'],
                'visit_fields' => ['dateov_visit', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'sickle_cell' => [
                'diagnosis_field' => 'scd_diagnosis',
                'outcome_field' => 'scd_outcome',
                'diagnosis_values' => ['1'],
                'visit_fields' => ['scd_visit_date', 'scd_appo_date', 'scd_outcome', 'scd_diagnosis'],
            ],
            'chronic_respiratory' => [
                'diagnosis_field' => 'main_diagnosis',
                'outcome_field' => 'outcome',
                'diagnosis_values' => ['1', '2', '3'],
                'visit_fields' => ['res_visit_date', 'nxt_appo_date', 'outcome', 'main_diagnosis'],
            ],
            'chronic_kidney' => [
                'diagnosis_field' => 'pat_main_diagnosis',
                'outcome_field' => 'ckd_outcome',
                'diagnosis_values' => ['10'],
                'visit_fields' => ['ckd_visit_date', 'meds_next_appo_date', 'ckd_outcome', 'pat_main_diagnosis'],
            ],
            'chronic_liver' => [
                'diagnosis_field' => 'liver_main_diagnosis',
                'outcome_field' => 'liver_outcome',
                'diagnosis_values' => ['1', '2'],
                'visit_fields' => ['liver_date', 'liver_next_appo_date', 'liver_outcome', 'liver_main_diagnosis'],
            ],
        ];

        return $configs[$diseaseType] ?? null;
    }

}
