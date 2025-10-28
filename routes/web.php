<?php

use App\Http\Controllers\CustomizedPackages\AHP\ServiceBasedIndicators as ServiceBasedIndicatorsControllers;
use App\Http\Controllers\CustomizedPackages\DashboardController as CustomPackagesDashboard;
use App\Http\Controllers\CustomizedPackages\NCD\Hba1c as Hba1cControllers;
use App\Http\Controllers\CustomizedPackages\NCDPPlus\CoreIndicators as CoreIndicatorsControllers;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Projects\AHP as AHPControllers;
use App\Http\Controllers\Projects\NCD as NCDControllers;
use App\Http\Controllers\Projects\NCDPPlus as NCDPPlusControllers;
use App\Http\Controllers\Projects\QuestionnaireItemController as GetFieldNameData;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('project/{project_id}/questionnaire/{field_name}/data', GetFieldNameData::class)->name('item.data');

    // Project NCD
    Route::group(['as' => 'ncd_booklet_20249fea.', 'prefix' => 'ncd'], function () {
        Route::get('project/{project_id}', NCDControllers\ProjectDashboardController::class)->name('project.dashboard');
        Route::get('project/{project_id}/questionnaire/dashboard', NCDControllers\QuestionnaireController::class)->name('questionnaire.dashboard');
    });

    // Project NCD PenPlus
    Route::group(['as' => 'ncd_patient_card.', 'prefix' => 'ncd_pplus'], function () {
        Route::get('project/{project_id}', NCDPPlusControllers\ProjectDashboardController::class)->name('project.dashboard');
        Route::get('project/{project_id}/questionnaire/dashboard', NCDPPlusControllers\QuestionnaireController::class)->name('questionnaire.dashboard');
    });

    // Project NCD PenPlus
    Route::group(['as' => 'ahp_register_2025_v2.', 'prefix' => 'ahp'], function () {
        Route::get('project/{project_id}', AHPControllers\ProjectDashboardController::class)->name('project.dashboard');
        Route::get('project/{project_id}/questionnaire/dashboard', AHPControllers\QuestionnaireController::class)->name('questionnaire.dashboard');
    });

    // // Custom Packages
    Route::group(['as' => 'packages.', 'prefix' => 'packages'], function () {
        Route::get('project/{project_id}/dashboard', CustomPackagesDashboard::class)->name('dashboard');
        Route::get('project/{project_id}/hba1c_analytics', Hba1cControllers\DashboardController::class)->name('hba1c');
        Route::get('project/{project_id}/hba1c_analytics/alldata', Hba1cControllers\AllHba1cController::class)->name('hba1c.alldata');
        Route::get('project/{project_id}/hba1c_analytics/averagedrespondents', Hba1cControllers\AveragedRespondentController::class)->name('hba1c.avgresp');
        Route::get('project/{project_id}/hba1c_analytics/genderanalysis', Hba1cControllers\GenderHBa1cDataController::class)->name('hba1c.gender');
        Route::get('project/{project_id}/hba1c_analytics/facilityanalysis', Hba1cControllers\FacilityHBa1cDataController::class)->name('hba1c.facility');
        Route::get('project/{project_id}/hba1c_analytics/respondent/{respondent}/data', Hba1cControllers\RespondentHBa1cDataController::class)->name('hba1c.resp.data');

        // core indicators
        Route::get('project/{project_id}/core_indicators', CoreIndicatorsControllers\DashboardController::class)->name('core');
        Route::get('project/{project_id}/core_indicators/enrollment', CoreIndicatorsControllers\EnrollmentAnalysisController::class)->name('core.enrollment');
        Route::get('project/{project_id}/core_indicators/activepatients', CoreIndicatorsControllers\ActivePatientsController::class)->name('core.active.patients');
        Route::get('project/{project_id}/core_indicators/deceasedpatients', CoreIndicatorsControllers\DeathAnalysisController::class)->name('core.deaths.patients');
        Route::get('project/{project_id}/core_indicators/enrolledlastthreemonths', CoreIndicatorsControllers\EnrolledLastThreeMonthsController::class)->name('core.enrolled.3months');
        Route::get('project/{project_id}/core_indicators/ltfulastthreemonths', CoreIndicatorsControllers\NewlyLTFUController::class)->name('core.enrolled.ltfu3months');
        Route::get('project/{project_id}/core_indicators/mortalitythreemonths', CoreIndicatorsControllers\DeathLastThreeMonthsController::class)->name('core.enrolled.death3months');

        /** NPP new */

        // Active Patients
        Route::get('project/{project_id}/core_indicators/nppactivepatients', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'index'])->name('core.pplusactive.patients');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/diabetes_1', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'type1Diabetes'])->name('core.pplusactive.type1Diabetes');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/diabetes_2', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'type2Diabetes'])->name('core.pplusactive.type2Diabetes');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/gestational_diabetes', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'unspecifiedDiabetes'])->name('core.pplusactive.unspecifiedDiabetes');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/rheumatic', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'rheumatic'])->name('core.pplusactive.rheumatic');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/congenital', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'congenital'])->name('core.pplusactive.congenital');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/heart_failure', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'heartFailure'])->name('core.pplusactive.heart_failure');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/other_cardiac', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'otherCardiac'])->name('core.pplusactive.other_cardiac');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/sickle_cell', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'sickleCell'])->name('core.pplusactive.sickle_cell');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/chronic_respiratory', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'chronicRespiratoryDisease'])->name('core.pplusactive.chronic_respiratory');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/chronic_kidney', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'chronicKidneyDisease'])->name('core.pplusactive.chronic_kidney');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/chronic_liver', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'chronicLiverDisease'])->name('core.pplusactive.chronic_liver');
        Route::get('project/{project_id}/core_indicators/nppactivepatients/all', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'allActivePatients']);
        Route::get('project/{project_id}/core_indicators/nppactivepatients/all/summary', [CoreIndicatorsControllers\PPlusActivePatientsController::class, 'getAllSummary']);

        // LTFU Patients
        Route::get('project/{project_id}/core_indicators/nppltfupatients', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'index'])->name('core.pplusltfu.patients');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/diabetes_1', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'type1Diabetes'])->name('core.pplusltfu.type1Diabetes');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/diabetes_2', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'type2Diabetes'])->name('core.pplusltfu.type2Diabetes');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/gestational_diabetes', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'unspecifiedDiabetes'])->name('core.pplusltfu.unspecifiedDiabetes');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/rheumatic', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'rheumatic'])->name('core.pplusltfu.rheumatic');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/congenital', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'congenital'])->name('core.pplusltfu.congenital');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/heart_failure', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'heartFailure'])->name('core.pplusltfu.heart_failure');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/other_cardiac', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'otherCardiac'])->name('core.pplusltfu.other_cardiac');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/sickle_cell', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'sickleCell'])->name('core.pplusltfu.sickle_cell');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/chronic_respiratory', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'chronicRespiratoryDisease'])->name('core.pplusltfu.chronic_respiratory');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/chronic_kidney', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'chronicKidneyDisease'])->name('core.pplusltfu.chronic_kidney');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/chronic_liver', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'chronicLiverDisease'])->name('core.pplusltfu.chronic_liver');
        Route::get('project/{project_id}/core_indicators/nppltfupatients/all', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'allLTFUPatients']);
        Route::get('project/{project_id}/core_indicators/nppltfupatients/all/summary', [CoreIndicatorsControllers\PPlusLTFUPatientsController::class, 'getAllSummary']);

        // Mortality Patients
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'index'])->name('core.pplusmortality.patients');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/diabetes_1', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'type1Diabetes'])->name('core.pplusmortality.type1Diabetes');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/diabetes_2', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'type2Diabetes'])->name('core.pplusmortality.type2Diabetes');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/gestational_diabetes', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'unspecifiedDiabetes'])->name('core.pplusmortality.unspecifiedDiabetes');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/rheumatic', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'rheumatic'])->name('core.pplusmortality.rheumatic');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/congenital', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'congenital'])->name('core.pplusmortality.congenital');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/heart_failure', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'heartFailure'])->name('core.pplusmortality.heart_failure');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/other_cardiac', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'otherCardiac'])->name('core.pplusmortality.other_cardiac');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/sickle_cell', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'sickleCell'])->name('core.pplusmortality.sickle_cell');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/chronic_respiratory', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'chronicRespiratoryDisease'])->name('core.pplusmortality.chronic_respiratory');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/chronic_kidney', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'chronicKidneyDisease'])->name('core.pplusmortality.chronic_kidney');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/chronic_liver', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'chronicLiverDisease'])->name('core.pplusmortality.chronic_liver');
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/all', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'allMortalityPatients']);
        Route::get('project/{project_id}/core_indicators/nppmortalitypatients/all/summary', [CoreIndicatorsControllers\PPlusMortalityPatientsController::class, 'getAllSummary']);



        // Active Patients
        Route::get('project/{project_id}/core_indicators/{status}/{condition}/all/data', CoreIndicatorsControllers\GetAllDataController::class)->name('core.all.data');
        Route::get('project/{project_id}/core_indicators/{status}/{condition}/diabetes/data', CoreIndicatorsControllers\GetDiabetesDataController::class)->name('core.diabetes.data');
        Route::get('project/{project_id}/core_indicators/{status}/{condition}/cardiac/data', CoreIndicatorsControllers\GetCardiacDataController::class)->name('core.cardiac.data');
        Route::get('project/{project_id}/core_indicators/{status}/{condition}/scd/data', CoreIndicatorsControllers\GetSickleCellDataController::class)->name('core.scd.data');
        Route::get('project/{project_id}/core_indicators/{status}/{condition}/crd/data', CoreIndicatorsControllers\GetRespiratoryDataController::class)->name('core.resp.data');
        Route::get('project/{project_id}/core_indicators/{status}/{condition}/ckd/data', CoreIndicatorsControllers\GetCKDDataController::class)->name('core.ckd.data');
        Route::get('project/{project_id}/core_indicators/{status}/{condition}/cld/data', CoreIndicatorsControllers\GetCLDDataController::class)->name('core.cld.data');

        // AHP Service Based Indicators
        Route::get('project/{project_id}/delivery-indicators-1', ServiceBasedIndicatorsControllers\DashboardController::class)->name('service_based_indicators.dashboard');

        Route::get('project/{project_id}/delivery-indicators-1/maternal-health', ServiceBasedIndicatorsControllers\MaternalHealthController::class)->name('service_based_indicators.maternal_health');
        // AHP Sexual and Reproductive Health
        Route::get('project/{project_id}/delivery-indicators-1/sexual-and-reproductive-health', ServiceBasedIndicatorsControllers\SexAndReproHealthController::class)->name('service_based_indicators.sexual_and_repro_health');
        // AHP Mental Health
        Route::get('project/{project_id}/delivery-indicators-1/mental-health', ServiceBasedIndicatorsControllers\MentalHealthController::class)->name('service_based_indicators.mental_health');
        // AHP HIV Health
        Route::get('project/{project_id}/delivery-indicators-1/hiv-health', ServiceBasedIndicatorsControllers\HIVHealthController::class)->name('service_based_indicators.hiv_health');
        // AHP PreP Health
        Route::get('project/{project_id}/delivery-indicators-1/prep-health', ServiceBasedIndicatorsControllers\PrEPHealthController::class)->name('service_based_indicators.prep_health');


        // AHP Data Summary Report
        Route::get('project/{project_id}/general_data_overview', \App\Http\Controllers\CustomizedPackages\AHP\DataSummary\DataReportController::class)->name('data_summary.report');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
