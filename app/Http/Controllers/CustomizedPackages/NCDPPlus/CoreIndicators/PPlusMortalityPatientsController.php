<?php

namespace App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators;

use App\Http\Controllers\Controller;
use App\Services\NCDPPlus\MortalityPatientsService;
use Inertia\Inertia;

class PPlusMortalityPatientsController extends Controller
{
    private MortalityPatientsService $mortalityPatientsService;

    public function __construct(MortalityPatientsService $mortalityPatientsService)
    {
        $this->mortalityPatientsService = $mortalityPatientsService;
    }

  public function index()
{
    $project = $this->mortalityPatientsService->getProject();
    $patients = $this->mortalityPatientsService->getAllMortalityPatients();
    
    // Compute summary and quarterly data
    $summary = $this->mortalityPatientsService->computeSummaryFromPatients($patients);
    $quarterlyData = $this->computeQuarterlyData($patients);

    return Inertia::render('Customizations/NCDPPlus/CoreIndicators/PPlusMortalityPatients', [
        'project' => $project,
        'patients' => $patients,
        'summary' => $summary,
        'quarterlyData' => $quarterlyData,
    ]);
}
    /** All Mortality Patients */
    public function allMortalityPatients()
    {
        $project = $this->mortalityPatientsService->getProject();
        $patients = $this->mortalityPatientsService->getAllMortalityPatients();

        return Inertia::render('Customizations/NCDPPlus/CoreIndicators/Reports/Mortality/AllMortalityPatients', [
            'project' => $project,
            'patients' => $patients,
        ]);
    }

    /** Individual disease methods with mortality dates */
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
            'patients'
        );
    }

    /** Rheumatic Heart Disease */
    public function rheumatic()
    {
        return $this->renderReport(
            'card_diagnosis',
            'card_outcome',
            ['2'],
            ['card_death_date', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
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
            ['card_death_date', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
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
            ['card_death_date', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
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
            ['card_death_date', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
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
            ['scd_outcome_date', 'scd_appo_date', 'scd_outcome', 'scd_diagnosis'],
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
            ['when', 'nxt_appo_date', 'outcome', 'main_diagnosis'],
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
            ['liver_outcome_date', 'liver_next_appo_date', 'liver_outcome', 'liver_main_diagnosis'],
            'ChronicLiverDisease'
        );
    }

    
    /** Epilepsy */
    public function epilepsy()
    {
        return $this->renderReport(
            'pat_main_diagnosis',
            'ep_outcome',
            ['3'],
            ['ep_visit_date', 'ep_next_appo_date', 'ep_outcome', 'pat_main_diagnosis'],
            'Epilepsy'
        );
    }


    /**
     * Render report using service - UPDATED to include mortality dates
     */
    private function renderReport(
        string $diagnosisField,
        string $outcomeField,
        array $diagnosisValues,
        array $visitFields,
        string $viewName,
        string $patientsVariable = 'patients'
    ) {
        $project = $this->mortalityPatientsService->getProject();
        $patients = $this->mortalityPatientsService->getMortalityPatients(
            $diagnosisField,
            $outcomeField,
            $diagnosisValues,
            $visitFields
        );

        return Inertia::render("Customizations/NCDPPlus/CoreIndicators/Reports/Mortality/{$viewName}", [
            'project' => $project,
            $patientsVariable => $patients,
        ]);
    }

    /**
     * Get summary statistics for all Mortality patients
     */
    public function getAllSummary()
    {
        $summary = $this->mortalityPatientsService->getAllMortalitySummary();
        return response()->json($summary);
    }

    /**
     * Get summary for specific disease
     */
    public function getSummary(string $diseaseType)
    {
        $config = $this->getDiseaseConfig($diseaseType);
        
        if (!$config) {
            return response()->json(['error' => 'Disease type not found'], 404);
        }

        $summary = $this->mortalityPatientsService->getMortalityPatientsSummary(
            $config['diagnosis_field'],
            $config['outcome_field'],
            $config['diagnosis_values'],
            $config['visit_fields']
        );

        return response()->json($summary);
    }

    /**
     * Get quarterly mortality data
     */
    public function getQuarterlyData()
    {
        $patients = $this->mortalityPatientsService->getAllMortalityPatients();
        $quarterlyData = $this->computeQuarterlyData($patients);
        
        return response()->json($quarterlyData);
    }

    /**
     * Compute quarterly data from patients
     */
    private function computeQuarterlyData($patients)
    {
        $quarters = [];
        
        foreach ($patients as $patient) {
            if (empty($patient['mortality_date'])) {
                continue;
            }
            
            $date = \Carbon\Carbon::parse($patient['mortality_date']);
            $year = $date->year;
            $quarter = ceil($date->month / 3);
            $quarterKey = "Q{$quarter} {$year}";
            
            if (!isset($quarters[$quarterKey])) {
                $quarters[$quarterKey] = [
                    'quarter' => $quarterKey,
                    'total' => 0,
                    'by_disease' => []
                ];
            }
            
            $quarters[$quarterKey]['total']++;
            
            $diseaseName = $patient['disease_name'] ?? 'Unknown';
            if (!isset($quarters[$quarterKey]['by_disease'][$diseaseName])) {
                $quarters[$quarterKey]['by_disease'][$diseaseName] = 0;
            }
            $quarters[$quarterKey]['by_disease'][$diseaseName]++;
        }
        
        // Sort quarters chronologically
        ksort($quarters);
        
        return array_values($quarters);
    }

    /**
     * Configuration for different disease types - FIXED typo in 'unspecified_diabetes'
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
            'unspecified_diabetes' => [ // Fixed typo from 'uspecified_diabetes'
                'diagnosis_field' => 'db_diagnosis',
                'outcome_field' => 'dm_outcome',
                'diagnosis_values' => ['3', '5'],
                'visit_fields' => ['db_visit_date', 'next_appo_date', 'dm_outcome', 'db_diagnosis'],
            ],
            'rheumatic' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['2'],
                'visit_fields' => ['card_death_date', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'congenital' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['5'],
                'visit_fields' => ['card_death_date', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'heart_failure' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['1', '6'],
                'visit_fields' => ['card_death_date', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'other_cardiac' => [
                'diagnosis_field' => 'card_diagnosis',
                'outcome_field' => 'card_outcome',
                'diagnosis_values' => ['2', '3', '4', '7', '8', '9', '10', '11'],
                'visit_fields' => ['card_death_date', 'card_next_appo_date', 'card_outcome', 'card_diagnosis'],
            ],
            'sickle_cell' => [
                'diagnosis_field' => 'scd_diagnosis',
                'outcome_field' => 'scd_outcome',
                'diagnosis_values' => ['1'],
                'visit_fields' => ['scd_outcome_date', 'scd_appo_date', 'scd_outcome', 'scd_diagnosis'],
            ],
            'chronic_respiratory' => [
                'diagnosis_field' => 'main_diagnosis',
                'outcome_field' => 'outcome',
                'diagnosis_values' => ['1', '2', '3'],
                'visit_fields' => ['when', 'nxt_appo_date', 'outcome', 'main_diagnosis'],
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
                'visit_fields' => ['liver_outcome_date', 'liver_next_appo_date', 'liver_outcome', 'liver_main_diagnosis'],
            ],
             'epilepsy' => [
                'diagnosis_field' => 'pat_main_diagnosis',
                'outcome_field' => 'ep_outcome',
                'diagnosis_values' => ['3'],
                'visit_fields' => ['ep_visit_date', 'ep_next_appo_date', 'ep_outcome', 'pat_main_diagnosis'],
            ]
        ];

        return $configs[$diseaseType] ?? null;
    }
}