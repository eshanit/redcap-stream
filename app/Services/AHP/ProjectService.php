<?php

namespace App\Services\AHP;

use App\Enums\AHPFacility as AHPFacilityEnum;
use App\Enums\AHPFamilyPlanning as AHPFamilyPlanningEnum;
use App\Enums\ANCFirstBooking as ANCFirstBookingEnum;
use App\Enums\ClientProfile as ClientProfileEnum;
use App\Enums\EducationLevel as EducationLevelEnum;
use App\Enums\Gender as GenderEnum;
use App\Enums\MaritalStatus as MaritalStatusEnum;
use App\Enums\PNCMVisit as PNCMVisitEnum;
use App\Models\ProjectData3;
use Illuminate\Support\Collection;

class ProjectService
{
    /**
     * Get all ANC registrants.
     */
    public function getANCRegistrants(int $projectId): Collection
    {
        $data = ProjectData3::query()
            ->where('project_id', $projectId)
            ->whereIn('field_name', [
                'record',
                'demog_facility',
                'demog_dateofbirth',
                'demog_gender',
                'demog_marital_status',
                'demog_education',
                'demog_client_profile',
                'ancr_date',
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {

                $allInstances = $group->groupBy('instance')->map(function ($instanceGroup, $instance) {
                    return [
                        'health_facility' => $instanceGroup->where('field_name', 'demog_facility')->first()?->value,
                        'date_of_birth' => $instanceGroup->where('field_name', 'demog_dateofbirth')->first()?->value,
                        'gender' => $instanceGroup->where('field_name', 'demog_gender')->first()?->value,
                        'marital_status' => $instanceGroup->where('field_name', 'demog_marital_status')->first()?->value,
                        'education' => $instanceGroup->where('field_name', 'demog_education')->first()?->value,
                        'client_profile' => $instanceGroup->where('field_name', 'demog_client_profile')->first()?->value,
                        'ancr_date' => $instanceGroup->where('field_name', 'ancr_date')->first()?->value,
                    ];
                });

                // Find the first non-null demographic values from any instance
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                           ! is_null($demo['gender']) ||
                           ! is_null($demo['marital_status']) ||
                           ! is_null($demo['education']) ||
                           ! is_null($demo['client_profile']);
                });

                // Now filter to only instances with anc_date values
                $instancesWithANCDate = $allInstances->filter(function ($demo) {
                    return ! is_null($demo['ancr_date']);
                });

                return $instancesWithANCDate->map(function ($demo) use ($record, $firstValidDemographics) {
                    $genderCode = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    $gender = GenderEnum::fromValue($genderCode);

                    $facilityCode = $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null;
                    $facility = AHPFacilityEnum::fromValue($facilityCode);

                    $maritalStatusCode = $demo['marital_status'] ?? $firstValidDemographics['marital_status'] ?? null;
                    $maritalStatus = MaritalStatusEnum::fromValue($maritalStatusCode);

                    $clientProfileCode = $demo['client_profile'] ?? $firstValidDemographics['client_profile'] ?? null;
                    $clientProfile = ClientProfileEnum::fromValue($clientProfileCode);

                    $educationCode = $demo['education'] ?? $firstValidDemographics['education'] ?? null;
                    $education = EducationLevelEnum::fromValue($educationCode);

                    return [
                        'record' => $record,
                        'health_facility' => $facility,
                        'date_of_birth' => $demo['date_of_birth'] ?? $firstValidDemographics['date_of_birth'],
                        'gender' => $gender,
                        'marital_status' => $maritalStatus,
                        'education' => $education,
                        'client_profile' => $clientProfile,
                        'ancr_date' => $demo['ancr_date'],
                    ];
                });
            })->filter()
            ->flatten(1)
            ->values();

        return $data;
    }

    /**
     * Get ANC first bookings for ANC project.
     */
    public function getANCFirstBookings(int $projectId): Collection
    {
        $data = ProjectData3::query()
            ->where('project_id', $projectId)
            ->whereIn('field_name', [
                'record',
                'demog_facility',
                'demog_dateofbirth',
                'demog_gender',
                'demog_marital_status',
                'demog_education',
                'demog_client_profile',
                'ancr_first_booking',
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {

                $allInstances = $group->groupBy('instance')->map(function ($instanceGroup, $instance) {
                    return [
                        'instance' => $instance ?: 1,
                        'health_facility' => $instanceGroup->where('field_name', 'demog_facility')->first()?->value,
                        'gender' => $instanceGroup->where('field_name', 'demog_gender')->first()?->value,
                        'marital_status' => $instanceGroup->where('field_name', 'demog_marital_status')->first()?->value,
                        'education' => $instanceGroup->where('field_name', 'demog_education')->first()?->value,
                        'client_profile' => $instanceGroup->where('field_name', 'demog_client_profile')->first()?->value,
                        'ancr_first_booking' => $instanceGroup->where('field_name', 'ancr_first_booking')->first()?->value,
                    ];
                });

                // Find the first non-null demographic values from any instance
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                           ! is_null($demo['gender']) ||
                           ! is_null($demo['marital_status']) ||
                           ! is_null($demo['education']) ||
                           ! is_null($demo['client_profile']);
                });

                return $allInstances->map(function ($demo) use ($record, $firstValidDemographics) {
                    $genderCode = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    $gender = GenderEnum::fromValue($genderCode);

                    $facilityCode = $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null;
                    $facility = AHPFacilityEnum::fromValue($facilityCode);

                    $maritalStatusCode = $demo['marital_status'] ?? $firstValidDemographics['marital_status'] ?? null;
                    $maritalStatus = MaritalStatusEnum::fromValue($maritalStatusCode);

                    $clientProfileCode = $demo['client_profile'] ?? $firstValidDemographics['client_profile'] ?? null;
                    $clientProfile = ClientProfileEnum::fromValue($clientProfileCode);

                    $educationCode = $demo['education'] ?? $firstValidDemographics['education'] ?? null;
                    $education = EducationLevelEnum::fromValue($educationCode);

                    $ancrFirstBookingCode = $demo['ancr_first_booking'] ?? null;
                    $ancrFirstBooking = ANCFirstBookingEnum::fromValue($ancrFirstBookingCode);

                    return [
                        'record' => $record,
                        'health_facility' => $facility,
                        'gender' => $gender,
                        'marital_status' => $maritalStatus,
                        'education' => $education,
                        'client_profile' => $clientProfile,
                        'anc_first_booking' => $ancrFirstBooking,
                    ];
                });
            })->filter()
            ->flatten(1)
            ->values();

        return $data;
    }

    /**
     *  Get ANC visits
     */
    public function getANCVisits(int $projectId): Collection
    {
        $data = ProjectData3::query()
            ->where('project_id', $projectId)
            ->whereIn('field_name', [
                'record',
                'demog_facility',
                'demog_dateofbirth',
                'demog_gender',
                'demog_marital_status',
                'demog_education',
                'demog_client_profile',
                'anc_date',
                'instance',
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {

                $allInstances = $group->groupBy('instance')->map(function ($instanceGroup, $instance) {
                    if ($instance != '') {
                        $newInst = $instance;
                    } else {
                        $newInst = 1;
                    }

                    return [
                        'instance' => $newInst,
                        'health_facility' => $instanceGroup->where('field_name', 'demog_facility')->first()?->value,
                        'date_of_birth' => $instanceGroup->where('field_name', 'demog_dateofbirth')->first()?->value,
                        'gender' => $instanceGroup->where('field_name', 'demog_gender')->first()?->value,
                        'marital_status' => $instanceGroup->where('field_name', 'demog_marital_status')->first()?->value,
                        'education' => $instanceGroup->where('field_name', 'demog_education')->first()?->value,
                        'client_profile' => $instanceGroup->where('field_name', 'demog_client_profile')->first()?->value,
                        'anc_date' => $instanceGroup->where('field_name', 'anc_date')->first()?->value,

                    ];
                });

                // Find the first non-null demographic values from any instance
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                           ! is_null($demo['date_of_birth']) ||
                           ! is_null($demo['gender']) ||
                           ! is_null($demo['marital_status']) ||
                           ! is_null($demo['education']) ||
                           ! is_null($demo['client_profile']);
                });

                // Now filter to only instances with anc_date values
                $instancesWithANCDate = $allInstances->filter(function ($demo) {
                    return ! is_null($demo['anc_date']);
                });

                // If no instances have anc_date values, skip this record
                if ($instancesWithANCDate->isEmpty()) {
                    return null;
                }

                return $instancesWithANCDate->map(function ($demo) use ($record, $firstValidDemographics) {

                    $genderCode = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    $gender = GenderEnum::fromValue($genderCode);

                    $facilityCode = $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null;
                    $facility = AHPFacilityEnum::fromValue($facilityCode);

                    $maritalStatusCode = $demo['marital_status'] ?? $firstValidDemographics['marital_status'] ?? null;
                    $maritalStatus = MaritalStatusEnum::fromValue($maritalStatusCode);

                    $clientProfileCode = $demo['client_profile'] ?? $firstValidDemographics['client_profile'] ?? null;
                    $clientProfile = ClientProfileEnum::fromValue($clientProfileCode);

                    $educationCode = $demo['education'] ?? $firstValidDemographics['education'] ?? null;
                    $education = EducationLevelEnum::fromValue($educationCode);

                    return [
                        'record' => $record,
                        'visit' => $demo['instance'],
                        'health_facility' => $facility,
                        'date_of_birth' => $demo['date_of_birth'] ?? $firstValidDemographics['date_of_birth'],
                        'gender' => $gender,
                        'marital_status' => $maritalStatus,
                        'education' => $education,
                        'client_profile' => $clientProfile,
                        'anc_date' => $demo['anc_date'],
                    ];

                });
            })->filter()
            ->flatten(1)
            ->values();

        return $data;
    }

    /**
     * Number of PNC Visits
     */
    public function getPNCVisits(int $projectId): Collection
    {
        $data = ProjectData3::query()
            ->where('project_id', $projectId)
            ->whereIn('field_name', [
                'record',
                'demog_facility',
                'demog_dateofbirth',
                'demog_gender',
                'demog_marital_status',
                'demog_education',
                'demog_client_profile',
                'pncm_visit',
                'pncm_visit_date',
                'instance',
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {

                $allInstances = $group->groupBy('instance')->map(function ($instanceGroup, $instance) {
                    if ($instance != '') {
                        $newInst = $instance;
                    } else {
                        $newInst = 1;
                    }

                    return [
                        'instance' => $newInst,
                        'health_facility' => $instanceGroup->where('field_name', 'demog_facility')->first()?->value,
                        'date_of_birth' => $instanceGroup->where('field_name', 'demog_dateofbirth')->first()?->value,
                        'gender' => $instanceGroup->where('field_name', 'demog_gender')->first()?->value,
                        'marital_status' => $instanceGroup->where('field_name', 'demog_marital_status')->first()?->value,
                        'education' => $instanceGroup->where('field_name', 'demog_education')->first()?->value,
                        'client_profile' => $instanceGroup->where('field_name', 'demog_client_profile')->first()?->value,
                        'pncm_visit' => $instanceGroup->where('field_name', 'pncm_visit')->first()?->value,
                        'pncm_visit_date' => $instanceGroup->where('field_name', 'pncm_visit_date')->first()?->value,

                    ];
                });

                // Find the first non-null demographic values from any instance
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                           ! is_null($demo['date_of_birth']) ||
                           ! is_null($demo['gender']) ||
                           ! is_null($demo['marital_status']) ||
                           ! is_null($demo['education']) ||
                           ! is_null($demo['client_profile']);
                });

                // Now filter to only instances with pncm_visit values
                $instancesWithPNCVisit = $allInstances->filter(function ($demo) {
                    return ! is_null($demo['pncm_visit']);
                });

                // If no instances have pncm_visit values, skip this record
                if ($instancesWithPNCVisit->isEmpty()) {
                    return null;
                }

                return $instancesWithPNCVisit->map(function ($demo) use ($record, $firstValidDemographics) {

                    $genderCode = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    $gender = GenderEnum::fromValue($genderCode);

                    $facilityCode = $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null;
                    $facility = AHPFacilityEnum::fromValue($facilityCode);

                    $maritalStatusCode = $demo['marital_status'] ?? $firstValidDemographics['marital_status'] ?? null;
                    $maritalStatus = MaritalStatusEnum::fromValue($maritalStatusCode);

                    $clientProfileCode = $demo['client_profile'] ?? $firstValidDemographics['client_profile'] ?? null;
                    $clientProfile = ClientProfileEnum::fromValue($clientProfileCode);

                    $educationCode = $demo['education'] ?? $firstValidDemographics['education'] ?? null;
                    $education = EducationLevelEnum::fromValue($educationCode);

                    $pncmVisitCode = $demo['pncm_visit'] ?? null;
                    $pncmVisit = PNCMVisitEnum::fromValue($pncmVisitCode);

                    return [
                        'record' => $record,
                        'event' => $demo['instance'],
                        'health_facility' => $facility,
                        'date_of_birth' => $demo['date_of_birth'] ?? $firstValidDemographics['date_of_birth'],
                        'gender' => $gender,
                        'marital_status' => $maritalStatus,
                        'education' => $education,
                        'client_profile' => $clientProfile,
                        'pncm_visit' => $pncmVisit,
                        'pncm_visit_date' => $demo['pncm_visit_date'] ?? null,
                    ];

                });
            })->filter()
            ->flatten(1)
            ->values();

        return $data;
    }

    /**
     * Get all ANC registrants.
     */
    public function getSTIScreened(int $projectId): Collection
    {
        $data = ProjectData3::query()
            ->where('project_id', $projectId)
            ->whereIn('field_name', [
                'record',
                'instance',
                'demog_facility',
                'demog_dateofbirth',
                'demog_gender',
                'demog_marital_status',
                'demog_education',
                'demog_client_profile',
                'anc_syphilis_done',
                'anc_hepb_screen',
                'art_hbv_status',
                'anc_date',
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {

                // Group data by instance and map to structured array
                $allInstances = $group->groupBy('instance')->map(function ($instanceGroup, $instance) {

                    if ($instance != '') {
                        $newInst = $instance;
                    } else {
                        $newInst = 1;
                    }

                    return [
                        'instance' => $newInst,
                        'health_facility' => $instanceGroup->where('field_name', 'demog_facility')->first()?->value,
                        'date_of_birth' => $instanceGroup->where('field_name', 'demog_dateofbirth')->first()?->value,
                        'gender' => $instanceGroup->where('field_name', 'demog_gender')->first()?->value,
                        'marital_status' => $instanceGroup->where('field_name', 'demog_marital_status')->first()?->value,
                        'education' => $instanceGroup->where('field_name', 'demog_education')->first()?->value,
                        'client_profile' => $instanceGroup->where('field_name', 'demog_client_profile')->first()?->value,
                        'sti_date' => $instanceGroup->where('field_name', 'sti_date')->first()?->value,
                        'sti_syphilis_test' => $instanceGroup->where('field_name', 'sti_syphilis_test')->first()?->value,
                        'sti_gonorrhea_test' => $instanceGroup->where('field_name', 'sti_gonorrhea_test')->first()?->value,
                        'anc_date' => $instanceGroup->where('field_name', 'anc_date')->first()?->value,
                        'art_review_date' => $instanceGroup->where('field_name', 'art_review_date')->first()?->value,
                        'anc_syphilis_done' => $instanceGroup->where('field_name', 'anc_syphilis_done')->first()?->value,
                        'anc_hepb_screen' => $instanceGroup->where('field_name', 'anc_hepb_screen')->first()?->value,
                        'art_hbv_status' => $instanceGroup->where('field_name', 'art_hbv_status')->first()?->value,
                    ];
                });

                // Find first instance with valid demographics for fallback
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                        ! is_null($demo['gender']) ||
                        ! is_null($demo['marital_status']) ||
                        ! is_null($demo['education']) ||
                        ! is_null($demo['client_profile']);
                });

                // Filter instances with at least one STI screening field present
                $instancesWithSTIScreen = $allInstances->filter(function ($demo) {
                    return ! is_null($demo['anc_syphilis_done']) ||
                        ! is_null($demo['anc_hepb_screen']) ||
                        ! is_null($demo['art_hbv_status']) ||
                        ! is_null($demo['sti_syphilis_test']) ||
                        ! is_null($demo['sti_gonorrhea_test']);
                });

                // Process screened instances
                return $instancesWithSTIScreen->map(function ($demo) use ($record, $firstValidDemographics) {
                    // Determine STI screening status
                    $isScreened = ($demo['anc_syphilis_done'] === '1') ||
                                  ($demo['anc_hepb_screen'] === '1') ||
                                  ($demo['sti_syphilis_test'] === '1') ||
                                  ($demo['sti_gonorrhea_test'] === '1') ||
                                  ($demo['art_hbv_status'] !== null);

                    // Merge current instance data with fallback demographics
                    $genderCode = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    $facilityCode = $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null;
                    $maritalStatusCode = $demo['marital_status'] ?? $firstValidDemographics['marital_status'] ?? null;
                    $clientProfileCode = $demo['client_profile'] ?? $firstValidDemographics['client_profile'] ?? null;
                    $educationCode = $demo['education'] ?? $firstValidDemographics['education'] ?? null;

                    return [
                        'record' => $record,
                        'visit' => $demo['instance'],
                        'health_facility' => AHPFacilityEnum::fromValue($facilityCode),
                        'date_of_birth' => $demo['date_of_birth'] ?? $firstValidDemographics['date_of_birth'] ?? null,
                        'gender' => GenderEnum::fromValue($genderCode),
                        'marital_status' => MaritalStatusEnum::fromValue($maritalStatusCode),
                        'education' => EducationLevelEnum::fromValue($educationCode),
                        'client_profile' => ClientProfileEnum::fromValue($clientProfileCode),
                        'sti_screened' => $isScreened,
                        'sti_date' => $demo['sti_date'] ?? null,
                        'art_review_date' => $demo['art_review_date'] ?? null,
                        'anc_date' => $demo['anc_date'] ?? null,
                    ];
                });
            })
            ->flatten(1)
            ->values();

        return $data;
    }

    /**
     * Get STI Repeats
     */
    public function getSTIRepeats(int $projectId): Collection
    {
        $data = ProjectData3::query()
            ->where('project_id', $projectId)
            ->whereIn('field_name', [
                'record',
                'demog_facility',
                'demog_dateofbirth',
                'demog_gender',
                'demog_marital_status',
                'demog_education',
                'demog_client_profile',
                'sti_repeat',
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {

                $allInstances = $group->groupBy('instance')->map(function ($instanceGroup, $instance) {
                    return [
                        'health_facility' => $instanceGroup->where('field_name', 'demog_facility')->first()?->value,
                        'date_of_birth' => $instanceGroup->where('field_name', 'demog_dateofbirth')->first()?->value,
                        'gender' => $instanceGroup->where('field_name', 'demog_gender')->first()?->value,
                        'marital_status' => $instanceGroup->where('field_name', 'demog_marital_status')->first()?->value,
                        'education' => $instanceGroup->where('field_name', 'demog_education')->first()?->value,
                        'client_profile' => $instanceGroup->where('field_name', 'demog_client_profile')->first()?->value,
                        'sti_repeat' => $instanceGroup->where('field_name', 'sti_repeat')->first()?->value,
                    ];
                });

                // Find the first non-null demographic values from any instance
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                           ! is_null($demo['gender']) ||
                           ! is_null($demo['marital_status']) ||
                           ! is_null($demo['education']) ||
                           ! is_null($demo['client_profile']);
                });

                // Now filter to only instances with anc_date values
                $instancesWithSTI = $allInstances->filter(function ($demo) {
                    return ! is_null($demo['sti_repeat']);
                });

                return $instancesWithSTI->map(function ($demo) use ($record, $firstValidDemographics) {
                    $genderCode = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    $gender = GenderEnum::fromValue($genderCode);

                    $facilityCode = $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null;
                    $facility = AHPFacilityEnum::fromValue($facilityCode);

                    $maritalStatusCode = $demo['marital_status'] ?? $firstValidDemographics['marital_status'] ?? null;
                    $maritalStatus = MaritalStatusEnum::fromValue($maritalStatusCode);

                    $clientProfileCode = $demo['client_profile'] ?? $firstValidDemographics['client_profile'] ?? null;
                    $clientProfile = ClientProfileEnum::fromValue($clientProfileCode);

                    $educationCode = $demo['education'] ?? $firstValidDemographics['education'] ?? null;
                    $education = EducationLevelEnum::fromValue($educationCode);

                    return [
                        'record' => $record,
                        'health_facility' => $facility,
                        'date_of_birth' => $demo['date_of_birth'] ?? $firstValidDemographics['date_of_birth'],
                        'gender' => $gender,
                        'marital_status' => $maritalStatus,
                        'education' => $education,
                        'client_profile' => $clientProfile,
                        'sti_repeat' => $demo['sti_repeat'],
                    ];
                });
            })->filter()
            ->flatten(1)
            ->values();

        return $data;
    }

    /**
     *  get STI Cases Treated
     */
    public function getSTICasesTreated(int $projectId): Collection
    {
        $data = ProjectData3::query()
            ->where('project_id', $projectId)
            ->whereIn('field_name', [
                'record',
                'instance',
                'demog_facility',
                'demog_dateofbirth',
                'demog_gender',
                'demog_marital_status',
                'demog_education',
                'demog_client_profile',
                'anc_date',
                'anc_syphilis_treat_dose', // should be 1, 2 or 3
                'anc_hep_b_treatment', // shoulf be I or R
                'art_review_date', // should be date
                'art_hbv_status', // should be 3
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {

                // Group data by instance and map to structured array
                $allInstances = $group->groupBy('instance')->map(function ($instanceGroup, $instance) {

                    if ($instance != '') {
                        $newInst = $instance;
                    } else {
                        $newInst = 1;
                    }

                    return [
                        'instance' => $newInst,
                        'health_facility' => $instanceGroup->where('field_name', 'demog_facility')->first()?->value,
                        'date_of_birth' => $instanceGroup->where('field_name', 'demog_dateofbirth')->first()?->value,
                        'gender' => $instanceGroup->where('field_name', 'demog_gender')->first()?->value,
                        'marital_status' => $instanceGroup->where('field_name', 'demog_marital_status')->first()?->value,
                        'education' => $instanceGroup->where('field_name', 'demog_education')->first()?->value,
                        'client_profile' => $instanceGroup->where('field_name', 'demog_client_profile')->first()?->value,
                        // 'sti_date' => $instanceGroup->where('field_name', 'sti_date')->first()?->value,
                        // 'sti_syphilis_test' => $instanceGroup->where('field_name', 'sti_syphilis_test')->first()?->value,
                        // 'sti_gonorrhea_test' => $instanceGroup->where('field_name', 'sti_gonorrhea_test')->first()?->value,
                        'anc_date' => $instanceGroup->where('field_name', 'anc_date')->first()?->value,
                        'art_review_date' => $instanceGroup->where('field_name', 'art_review_date')->first()?->value,
                        'anc_syphilis_treat_dose' => $instanceGroup->where('field_name', 'anc_syphilis_treat_dose')->first()?->value,
                        'anc_hep_b_treatment' => $instanceGroup->where('field_name', 'anc_hep_b_treatment')->first()?->value,
                        'art_hbv_status' => $instanceGroup->where('field_name', 'art_hbv_status')->first()?->value,
                    ];
                });

                // Find first instance with valid demographics for fallback
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                        ! is_null($demo['gender']) ||
                        ! is_null($demo['marital_status']) ||
                        ! is_null($demo['education']) ||
                        ! is_null($demo['client_profile']);
                });

                // Filter instances with at least one STI screening field present
                $instancesWithSTITreatment = $allInstances->filter(function ($demo) {
                    return ! is_null($demo['anc_syphilis_treat_dose']) ||
                        ! is_null($demo['anc_hep_b_treatment']) ||
                        ! is_null($demo['art_hbv_status']);
                });

                // Process treated instances
                return $instancesWithSTITreatment->map(function ($demo) use ($record, $firstValidDemographics) {
                    // Determine STI screening status
                    $isTreated = ($demo['anc_syphilis_treat_dose'] === '1') ||
                                  ($demo['anc_syphilis_treat_dose'] === '2') ||
                                  ($demo['anc_syphilis_treat_dose'] === '3') ||
                                  ($demo['anc_hep_b_treatment'] === 'I') ||
                                  ($demo['anc_hep_b_treatment'] === 'R') ||
                                  ($demo['art_hbv_status'] === '3');

                    // Merge current instance data with fallback demographics
                    $genderCode = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    $facilityCode = $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null;
                    $maritalStatusCode = $demo['marital_status'] ?? $firstValidDemographics['marital_status'] ?? null;
                    $clientProfileCode = $demo['client_profile'] ?? $firstValidDemographics['client_profile'] ?? null;
                    $educationCode = $demo['education'] ?? $firstValidDemographics['education'] ?? null;

                    return [
                        'record' => $record,
                        'visit' => $demo['instance'],
                        'health_facility' => AHPFacilityEnum::fromValue($facilityCode),
                        'date_of_birth' => $demo['date_of_birth'] ?? $firstValidDemographics['date_of_birth'] ?? null,
                        'gender' => GenderEnum::fromValue($genderCode),
                        'marital_status' => MaritalStatusEnum::fromValue($maritalStatusCode),
                        'education' => EducationLevelEnum::fromValue($educationCode),
                        'client_profile' => ClientProfileEnum::fromValue($clientProfileCode),
                        'sti_treated' => $isTreated,
                        'art_review_date' => $demo['art_review_date'] ?? null,
                        'anc_date' => $demo['anc_date'] ?? null,
                    ];
                });
            })
            ->flatten(1)
            ->values();

        return $data;
    }

    /**
     *  get Contraception Methods
     */
    public function getContraceptionMethods(int $projectId): Collection
    {
        $data = ProjectData3::query()
            ->where('project_id', $projectId)
            ->whereIn('field_name', [
                'record',
                'instance',
                'demog_facility',
                'demog_dateofbirth',
                'demog_gender',
                'demog_marital_status',
                'demog_education',
                'demog_client_profile',
                'fp_date',
                'fp_want_contra', // should be 1
                'fp_method', // shouuld be not null
            ])
            ->get()
            ->groupBy('record')
            ->map(function ($group, $record) {

                // Group data by instance and map to structured array
                $allInstances = $group->groupBy('instance')->map(function ($instanceGroup, $instance) {

                    if ($instance != '') {
                        $newInst = $instance;
                    } else {
                        $newInst = 1;
                    }

                    $fpMethods = $instanceGroup->where('field_name', 'fp_method')
                        ->pluck('value')
                        ->filter()
                        ->toArray();

                    return [
                        'instance' => $newInst,
                        'health_facility' => $instanceGroup->where('field_name', 'demog_facility')->first()?->value,
                        'date_of_birth' => $instanceGroup->where('field_name', 'demog_dateofbirth')->first()?->value,
                        'gender' => $instanceGroup->where('field_name', 'demog_gender')->first()?->value,
                        'marital_status' => $instanceGroup->where('field_name', 'demog_marital_status')->first()?->value,
                        'education' => $instanceGroup->where('field_name', 'demog_education')->first()?->value,
                        'client_profile' => $instanceGroup->where('field_name', 'demog_client_profile')->first()?->value,
                        // 'sti_date' => $instanceGroup->where('field_name', 'sti_date')->first()?->value,
                        // 'sti_syphilis_test' => $instanceGroup->where('field_name', 'sti_syphilis_test')->first()?->value,
                        // 'sti_gonorrhea_test' => $instanceGroup->where('field_name', 'sti_gonorrhea_test')->first()?->value,
                        'fb_date' => $instanceGroup->where('field_name', 'fb_date')->first()?->value,
                        'fp_want_contra' => $instanceGroup->where('field_name', 'fp_want_contra')->first()?->value,
                        'fp_method' => $fpMethods, // Collect all fp_method values
                    ];
                });

                // Find first instance with valid demographics for fallback
                $firstValidDemographics = $allInstances->first(function ($demo) {
                    return ! is_null($demo['health_facility']) ||
                        ! is_null($demo['gender']) ||
                        ! is_null($demo['marital_status']) ||
                        ! is_null($demo['education']) ||
                        ! is_null($demo['client_profile']);
                });

                // Filter instances with at least one STI screening field present
                $instancesContraceptionCons = $allInstances->filter(function ($demo) {
                    return ! is_null($demo['fp_want_contra']) ||
                        ! is_null($demo['fp_method']);
                });

                // Process treated instances
                return $instancesContraceptionCons->map(function ($demo) use ($record, $firstValidDemographics) {
                    // Determine STI screening status

                    $contraceptionCodes = [];
                if (!empty($demo['fp_method'])) {
                    if (is_array($demo['fp_method'])) {
                        $contraceptionCodes = $demo['fp_method'];
                    } elseif (str_contains($demo['fp_method'], ',')) {
                        $contraceptionCodes = explode(',', $demo['fp_method']);
                    } else {
                        $contraceptionCodes = [$demo['fp_method']];
                    }
                }
                
                $contraceptionMethods = AHPFamilyPlanningEnum::fromValues($contraceptionCodes);

                    // Merge current instance data with fallback demographics
                    $genderCode = $demo['gender'] ?? $firstValidDemographics['gender'] ?? null;
                    $facilityCode = $demo['health_facility'] ?? $firstValidDemographics['health_facility'] ?? null;
                    $maritalStatusCode = $demo['marital_status'] ?? $firstValidDemographics['marital_status'] ?? null;
                    $clientProfileCode = $demo['client_profile'] ?? $firstValidDemographics['client_profile'] ?? null;
                    $educationCode = $demo['education'] ?? $firstValidDemographics['education'] ?? null;
                    $contraceptionCode = $demo['fp_method'] ?? null;

                    return [
                        'record' => $record,
                        'health_facility' => AHPFacilityEnum::fromValue($facilityCode),
                        'date_of_birth' => $demo['date_of_birth'] ?? $firstValidDemographics['date_of_birth'] ?? null,
                        'gender' => GenderEnum::fromValue($genderCode),
                        'marital_status' => MaritalStatusEnum::fromValue($maritalStatusCode),
                        'education' => EducationLevelEnum::fromValue($educationCode),
                        'client_profile' => ClientProfileEnum::fromValue($clientProfileCode),
                        'fp_date' => $demo['fp_date'] ?? null,
                        'contraceptions' =>  $contraceptionMethods,
                        'fp_want_contra' => $demo['fp_want_contra'], // Convert to boolean
                    ];
                });
            })
            ->flatten(1)
            ->values();

        return $data;
    }
}
