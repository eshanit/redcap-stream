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
