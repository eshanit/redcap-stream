import { markRaw } from "vue";
import { type IProjectData } from '@/types/IProject'
import Download from '@/components/insights/shared/Download.vue'
import Registering from "@/components/insights/ncd/Registering.vue";
import Facility from "@/components/insights/ncd/Facility.vue";
import Age from "@/components/insights/shared/Age.vue";
import Gender from "@/components/insights/shared/Gender.vue";
import Maritial from "@/components/insights/ncd/Maritial.vue";
import YesNo from "@/components/insights/shared/YesNo.vue";
import DMType from "@/components/insights/ncd/DMType.vue";
import HtnType from "@/components/insights/ncd/HtnType.vue"
import HtnYearDiag from "@/components/insights/ncd/HtnYearDiag.vue";
import DMYearDiag from "@/components/insights/ncd/DMYearDiag.vue";
import CVD1Diag from "@/components/insights/ncd/CVD1Diag.vue";
import CVD2Diag from "@/components/insights/ncd/CVD2Diag.vue";
import CKDDiag from "@/components/insights/ncd/CKDDiag.vue";
import TBPresent from "@/components/insights/ncd/TBPresent.vue";
import TBYearDiag from "@/components/insights/ncd/TBYearDiag.vue";
import HIVCode from "@/components/insights/ncd/HIVCode.vue";
import HIVYearDiag from "@/components/insights/ncd/HIVYearDiag.vue";
import HIVYearTest from "@/components/insights/ncd/HIVYearTest.vue";
import CancerYearDiag from "@/components/insights/ncd/CancerYearDiag.vue";
import AsthmaYearDiag from "@/components/insights/ncd/AsthmaYearDiag.vue";
import MentalHealthYearDiag from "@/components/insights/ncd/MentalHealthYearDiag.vue";
import AllergyYearDiag from "@/components/insights/ncd/AllergyYearDiag.vue";
import VisionYearDiag from "@/components/insights/ncd/VisionYearDiag.vue";
import PatientInformationForm from "@/components/insights/ncd/PatientInformationForm.vue";
import NCDEntryMode from "@/components/insights/ncd/NCDEntryMode.vue";
import NewDiagnosisYearDiag from "@/components/insights/ncd/NewDiagnosisYearDiag.vue";
import TransferedFrom from "@/components/insights/ncd/TransferedFrom.vue";
import TransferInYear from "@/components/insights/ncd/TransferInYear.vue";
import BPValues from "@/components/insights/ncd/BPValues.vue";
import FirstBPyear from "@/components/insights/ncd/FirstBPyear.vue";
import SecondBPyear from "@/components/insights/ncd/SecondBPyear.vue";
import ThirdBPyear from "@/components/insights/ncd/ThirdBPyear.vue";
import DMValues from "@/components/insights/ncd/DMValues.vue";
import FBSValues from "@/components/insights/ncd/FBSValues.vue";
import FBSYearDiag from "@/components/insights/ncd/FBSYearDiag.vue";
import RBSValues from "@/components/insights/ncd/RBSValues.vue";
import RBSYearDiag from "@/components/insights/ncd/RBSYearDiag.vue";
import HBA1cValues from "@/components/insights/ncd/HBA1cValues.vue";
import HBA1cYearDiag from "@/components/insights/ncd/HBA1cYearDiag.vue";
import Complaints from "@/components/insights/ncd/Complaints.vue";
import CardioNonMod from "@/components/insights/ncd/CardioNonMod.vue";
import CardioMod from "@/components/insights/ncd/CardioMod.vue";
import MedsBeforeEnroll from "@/components/insights/ncd/MedsBeforeEnroll.vue";
import InsulinYearDiag from "@/components/insights/ncd/InsulinYearDiag.vue";
import OralAntiMedYearDiag from "@/components/insights/ncd/OralAntiMedYearDiag.vue";
import BPMedYearDiag from "@/components/insights/ncd/BPMedYearDiag.vue";
import ARVMedYearDiag from "@/components/insights/ncd/ARVMedYearDiag.vue";
import PhysicalRespiratory from "@/components/insights/ncd/PhysicalRespiratory.vue";
import PhysicalHeart from "@/components/insights/ncd/PhysicalHeart.vue";
import PregancyStatus from "@/components/insights/ncd/PregancyStatus.vue";
import InstanceStatusWhy from "@/components/insights/ncd/InstanceStatusWhy.vue";
import InstancePreviousFacility from "@/components/insights/ncd/InstancePreviousFacility.vue";
import InstanceCurrentFacility from "@/components/insights/ncd/InstanceCurrentFacility.vue";
import InstancePatientStatusComplete from "@/components/insights/ncd/InstancePatientStatusComplete.vue";
import InstanceVisitDate from "@/components/insights/ncd/InstanceVisitDate.vue";
import InstanceVisitType from "@/components/insights/ncd/InstanceVisitType.vue";
import InstanceAge from "@/components/insights/ncd/InstanceAge.vue";
import InstanceWeightGroups from "@/components/insights/shared/InstanceWeightGroups.vue";
import InstanceHeightGroups from "@/components/insights/shared/InstanceHeightGroups.vue";
import InstanceBMIGroups from "@/components/insights/shared/InstanceBMIGroups.vue";
import InstanceVisitSignsAndSymptoms from "@/components/insights/ncd/InstanceVisitSignsAndSymptoms.vue";
import InstanceEyeExam from "@/components/insights/ncd/InstanceEyeExam.vue";
import InstanceFundoscopyOutcome from "@/components/insights/ncd/InstanceFundoscopyOutcome.vue";
import Scraped from "@/components/insights/shared/Scraped.vue";
import InstanceYesNo from "@/components/insights/shared/InstanceYesNo.vue";
import InstanceInterruptionPeriod from "@/components/insights/ncd/InstanceInterruptionPeriod.vue";
import InstanceCouncilling from "@/components/insights/ncd/InstanceCouncilling.vue";
import InstanceCreatine from "@/components/insights/ncd/InstanceCreatine.vue";
import InstanceKidneyDiseaseStage from "@/components/insights/ncd/InstanceKidneyDiseaseStage.vue";
import InstanceFBS from "@/components/insights/ncd/InstanceFBS.vue";
import InstanceRBS from "@/components/insights/ncd/InstanceRBS.vue";
import InstanceHBA1c from "@/components/insights/ncd/InstanceHBA1c.vue";
import InstanceUrinalysis from "@/components/insights/ncd/InstanceUrinalysis.vue";
import InstanceHTN from "@/components/insights/ncd/InstanceHTN.vue";
import InstanceHTNHTCDosages from "@/components/insights/ncd/InstanceHTNHTCDosages.vue";
import InstanceHTNHTCDosagesFreq from "@/components/insights/ncd/InstanceHTNHTCDosagesFreq.vue";
import InstanceHTNHTCMedReasons from "@/components/insights/ncd/InstanceHTNHTCMedReasons.vue";
import InstanceHTNEnapDosages from "@/components/insights/ncd/InstanceHTNEnapDosages.vue";
import InstanceHTNEnapDosagesFreq from "@/components/insights/ncd/InstanceHTNEnapDosagesFreq.vue";
import InstanceHTNEnapMedReasons from "@/components/insights/ncd/InstanceHTNEnapMedReasons.vue";
import InstanceHTNAmlDosages from "@/components/insights/ncd/InstanceHTNAmlDosages.vue";
import InstanceHTNAmlDosagesFreq from "@/components/insights/ncd/InstanceHTNAmlDosagesFreq.vue";
import InstanceHTNAmlMedReasons from "@/components/insights/ncd/InstanceHTNAmlMedReasons.vue";
import InstanceHTNAteDosages from "@/components/insights/ncd/InstanceHTNAteDosages.vue";
import InstanceHTNAteDosagesFreq from "@/components/insights/ncd/InstanceHTNAteDosagesFreq.vue";
import InstanceHTNAteMedReasons from "@/components/insights/ncd/InstanceHTNAteMedReasons.vue";
import InstanceHTNSpirDosages from "@/components/insights/ncd/InstanceHTNSpirDosages.vue";
import InstanceHTNSpirDosagesFreq from "@/components/insights/ncd/InstanceHTNSpirDosagesFreq.vue";
import InstanceHTNSpirMedReasons from "@/components/insights/ncd/InstanceHTNSpirMedReasons.vue";
import InstanceHTNFuroDosages from "@/components/insights/ncd/InstanceHTNFuroDosages.vue";
import InstanceHTNFuroDosagesFreq from "@/components/insights/ncd/InstanceHTNFuroDosagesFreq.vue";
import InstanceHTNFuroMedReasons from "@/components/insights/ncd/InstanceHTNFuroMedReasons.vue";
import InstanceHTNLosDosages from "@/components/insights/ncd/InstanceHTNLosDosages.vue";
import InstanceHTNLosDosagesFreq from "@/components/insights/ncd/InstanceHTNLosDosagesFreq.vue";
import InstanceHTNLosMedReasons from "@/components/insights/ncd/InstanceHTNLosMedReasons.vue";
import InstanceHTNNifeDosages from "@/components/insights/ncd/InstanceHTNNifeDosages.vue";
import InstanceHTNNifeMedReasons from "@/components/insights/ncd/InstanceHTNNifeMedReasons.vue";
import InstanceDMMetDosages from "@/components/insights/ncd/InstanceDMMetDosages.vue";
import InstanceDMMetDosagesFreq from "@/components/insights/ncd/InstanceDMMetDosagesFreq.vue";
import InstanceDMProtDosagesFreq from "@/components/insights/ncd/InstanceDMProtDosagesFreq.vue";
import InstanceDMProtMedReasons from "@/components/insights/ncd/InstanceDMProtMedReasons.vue";
import InstanceDMActDosagesFreq from "@/components/insights/ncd/InstanceDMActDosagesFreq.vue";
import InstanceDMActMedReasons from "@/components/insights/ncd/InstanceDMActMedReasons.vue";
import InstanceDMActraDosagesFreq from "@/components/insights/ncd/InstanceDMActraDosagesFreq.vue";
import InstanceDMActraMedReasons from "@/components/insights/ncd/InstanceDMActraMedReasons.vue";
import InstanceDMInsDosagesFreq from "@/components/insights/ncd/InstanceDMInsDosagesFreq.vue";
import InstanceDMInsMedReasons from "@/components/insights/ncd/InstanceDMInsMedReasons.vue";
import InstanceDMAspDosages from "@/components/insights/ncd/InstanceDMAspDosages.vue";
import InstanceDMAspDosagesFreq from "@/components/insights/ncd/InstanceDMAspDosagesFreq.vue";
import InstanceDMAspMedReasons from "@/components/insights/ncd/InstanceDMAspMedReasons.vue";
import InstanceAsthma from "@/components/insights/ncd/InstanceAsthma.vue";
import InstanceReferredTo from "@/components/insights/ncd/InstanceReferredTo.vue";
import InstanceOtherTests from "@/components/insights/ncd/InstanceOtherTests.vue";
import InstanceTransferToFacility from "@/components/insights/ncd/InstanceTransferToFacility.vue";


export default [
    {
        name: 'record_id',
        description: 'List of record ids',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_number',
        description: 'List of NCD Numbers, identification numbers',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cohort',
        description: 'List of NCD Cohorts',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_study_num',
        description: 'List of NCD Study Numbers',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_reg_date',
        description: 'Insights on NCD reg date',
        textInside: 'A look at registration into NCD, dates is recorded for each respondent. Below is data for the number of respondents recorded each year.',
        component: markRaw(Registering),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_health_facility',
        description: 'Insights on NCD Health Facilities',
        textInside: 'A look at registration into NCD, dates is recorded for each respondent. Below is data for the number of respondents recorded each year.',
        component: markRaw(Facility),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_district',
        description: 'List of NCD Study Numbers',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_surname',
        description: 'List of respondents last names',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_name',
        description: 'List of respondents first names',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_id_num',
        description: 'List of id numbers',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dob',
        description: 'List of respondents date of birth',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis. For age analysis check the age field',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_age',
        description: 'List of respondents age',
        textInside: 'Age analysis and reporting',
        component: markRaw(Age),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_gender',
        description: 'Respondents gender account',
        textInside: 'Gender Summary',
        component: markRaw(Gender),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_maritial_status',
        description: 'An analysis of the marital status on respondents',
        textInside: 'Marital status ',
        component: markRaw(Maritial),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_address',
        description: 'List of respondent addresses',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_kin_firstname',
        description: 'List of next of kin names',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_kin_lastname',
        description: 'List of next of kin surnames',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_rel_oth',
        description: 'List of next of kin other relationships',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_tel_pat',
        description: 'List of respondent contact numbers',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_tel_kin',
        description: 'List of respondent next of kin contact numbers',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_exists',
        description: 'Report on HTN Conditions existence ',
        textInside: 'A count of respondents who have at least one HTN condition on first day of registration into the NCD program. Those who answered no where also counted',
        component: markRaw(YesNo),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_present',
        description: 'Report on HTN Conditions existence ',
        textInside: 'A count of respondents with HTM conditions on first day of registration into the NCD program.',
        component: markRaw(HtnType),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_diag',
        description: 'Year distribution of HTN Diagnosis',
        textInside: 'HTN diagnosis report',
        component: markRaw(HtnYearDiag),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_exists',
        description: 'Report on DM Conditions existence ',
        textInside: 'A count of respondents who have at least one DM condition on first day of registration into the NCD program. Those who answered no where also counted',
        component: markRaw(YesNo),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_present',
        description: 'Report on DM Conditions existence ',
        textInside: 'A count of respondents with DM conditions on first day of registration into the NCD program.',
        component: markRaw(DMType),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_diag',
        description: 'Year distribution of DM Diagnosis',
        textInside: 'DM diagnosis report',
        component: markRaw(DMYearDiag),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cvd_1_exists',
        description: 'Report on CVD Condition 1 existence ',
        textInside: 'A count of respondents who have at least one CVD Condition 1 on first day of registration into the NCD program. Those who answered no where also counted',
        component: markRaw(YesNo),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cvd_1_present',
        description: 'List of CVD Condition 1 present',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cvd_1_diag',
        description: 'Year distribution of CVD Condition 1 Diagnosis',
        textInside: 'CVD Condition 1 diagnosis report',
        component: markRaw(CVD1Diag),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cvd_2_exists',
        description: 'Report on CVD Condition 2 existence ',
        textInside: 'A count of respondents who have at least one CVD Condition 1 on first day of registration into the NCD program. Those who answered no where also counted',
        component: markRaw(YesNo),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cvd_2_present',
        description: 'List of CVD Condition 2 present',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cvd_2_diag',
        description: 'Year distribution of CVD Condition 2 Diagnosis',
        textInside: 'CVD Condition 2 diagnosis report',
        component: markRaw(CVD2Diag),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_ckd_exists',
        description: 'Report on CKD Condition ',
        textInside: 'A count of respondents who have a CVD Condition  on first day of registration into the NCD program. Those who answered no where also counted',
        component: markRaw(YesNo),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_ckd_diag',
        description: 'Year distribution of CKD Condition Diagnosis',
        textInside: 'CKD Condition diagnosis report',
        component: markRaw(CKDDiag),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_tb_exists',
        description: 'Report on TB/DRTB Condition ',
        textInside: 'A count of respondents who have a TB/DRTB Condition  on first day of registration into the NCD program. Those who answered no where also counted',
        component: markRaw(YesNo),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_tb_present',
        description: 'Report on TB/DRTB Condition ',
        textInside: 'A count of respondents who have a TB/DRTB Condition  on first day of registration into the NCD program. Also, included is analysis/reports on respondents who have multiple TB conditions.',
        component: markRaw(TBPresent),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_tb_diag',
        description: 'Report on TB/DRTB year of diagnosis ',
        textInside: 'A count of respondents who diagniased for TB/DRTB Condition by year.',
        component: markRaw(TBYearDiag),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_hiv_exists',
        description: 'Report on respondents HIV test (done or not done) ',
        textInside: 'A count of respondents who were tested for HIV.',
        component: markRaw(YesNo),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_hiv_present',
        description: 'Report on respondents HIV Code ',
        textInside: 'A count of respondents who fall into HIV Codes',
        component: markRaw(HIVCode),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_hiv_oi',
        description: 'List of HIV OIs ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_hiv_test',
        description: 'List of HIV Test years ',
        textInside: 'Shows years which respondents last had their HIV tests and gives counts on how many had tests in each year.',
        component: markRaw(HIVYearTest),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_hiv_diag',
        description: 'List of HIV diagnosis years ',
        textInside: 'Shows years which respondents last had their HIV diagnosis and gives counts on how many were diagnosed in each year.',
        component: markRaw(HIVYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cncr_exists',
        description: 'Report on Cancer ',
        textInside: 'A count of respondents who have reported to have Cancer on first day of registration into the NCD program. Those who answered no where also counted',
        component: markRaw(YesNo),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cncr_present',
        description: 'List of Cancers reported by respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cncr_diag',
        description: 'List of Cancer diagnosis years ',
        textInside: 'Shows years which respondents last had their Cancer diagnosis and gives counts on how many were diagnosed in each year.',
        component: markRaw(CancerYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_asthma_exists',
        description: 'List of respondents Asthma status',
        textInside: 'A count of respondents who have reported to have Asthma on first day of registration into the NCD program. Those who answered no where also counted.',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_asthma_diag',
        description: 'List of respondents Asthma Diagnosis years',
        textInside: 'Shows years which respondents last had their Asthma diagnosis and gives counts on how many were diagnosed in each year.',
        component: markRaw(AsthmaYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_epi_exists',
        description: 'List of respondents epilepsy',
        textInside: 'A count of respondents who have reported to have Epilepsy on first day of registration into the NCD program. Those who answered no where also counted.',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_epi_diag',
        description: 'List of respondents Epilepsy Diagnosis years',
        textInside: 'Shows years which respondents last had their Epilepsy diagnosis and gives counts on how many were diagnosed in each year.',
        component: markRaw(AsthmaYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_mh_exists',
        description: 'List of respondents Mental Health',
        textInside: 'A count of respondents who have reported to have Epilepsy on first day of registration into the NCD program. Those who answered no where also counted.',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_mh_present',
        description: 'List of Mental Health reported by respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_mh_present_2',
        description: 'List of Mental Health (2) reported by respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_mh_diag',
        description: 'List of respondents Mental Health years',
        textInside: 'Shows years which respondents last had their Mental Health diagnosis and gives counts on how many were diagnosed in each year.',
        component: markRaw(MentalHealthYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_alle_exists',
        description: 'List of respondents with Drug Allergies',
        textInside: 'A count of respondents who have reported to have Drug Allergies on first day of registration into the NCD program. Those who answered no where also counted.',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_alle_present',
        description: 'List of Drug Allergies reported by respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_alle_present_2',
        description: 'List of Drug Allergies (2) reported by respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_alle_diag',
        description: 'List of respondents Allergy years',
        textInside: 'Shows years which respondents discovered they had Allergy and gives counts on how many were diagnosed in each year.',
        component: markRaw(AllergyYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_vision_exists',
        description: 'List of respondents with Vision problems',
        textInside: 'A count of respondents who have reported to have vision problems on first day of registration into the NCD program. Those who answered no where also counted.',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_vision_present',
        description: 'List of vision problems reported by respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_vision_present_2',
        description: 'List of vision problems (2) reported by respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_vision_diag',
        description: 'List of respondents vision problems years',
        textInside: 'Shows years which respondents were diagonised with vision problems and gives counts on how many were diagnosed in each year.',
        component: markRaw(VisionYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_patient_information_complete',
        description: 'Report on Patient Information Status ',
        textInside: 'A count of respondents who has their Patient Information completed and the status of the process.',
        component: markRaw(PatientInformationForm),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_mode_entry',
        description: 'Report on Entry Mode into NCD Program',
        textInside: 'A count of respondents by way in which they entered the NCD program.',
        component: markRaw(NCDEntryMode),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_date_newdiag',
        description: 'List of respondents new diagnosis years',
        textInside: 'Shows years which respondents were newly diagonised and gives counts on how many were diagnosed in each year.',
        component: markRaw(NewDiagnosisYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_transfered_from',
        description: 'List of respondents transfered from another facility',
        textInside: 'Shows years which respondents were transfered from another facility and gives counts on how many were transfered in each year.',
        component: markRaw(TransferedFrom),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_transfered_from_oth',
        description: 'List of facilities where respondents transfered from ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_date_transin',
        description: 'List of respondents transfer dates (In years)',
        textInside: 'Shows years which respondents were Transfered In and gives counts on how many were transfered In for each year.',
        component: markRaw(TransferInYear),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_bp_values',
        description: 'Report on Pre-treatment/confirmatory BP values ',
        textInside: 'A count of respondents who have Pre-treatment/confirmatory BP values at hand. A respondent can have one or a combination of those.',
        component: markRaw(BPValues),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_1stbp_value',
        description: 'List of 1st Bp values for respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_1stbp_date',
        description: 'List of respondents 1st BP dates (In years)',
        textInside: 'Shows years which respondents had their first BP results and gives counts on how many had their 1st BP results for each year.',
        component: markRaw(FirstBPyear),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_2ndbp_value',
        description: 'List of 2nd Bp values for respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_2ndbp_date',
        description: 'List of respondents 2nd BP dates (In years)',
        textInside: 'Shows years which respondents had their second BP results and gives counts on how many had their 2nd BP results for each year.',
        component: markRaw(SecondBPyear),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_3rdbp_value',
        description: 'List of 3rd Bp values for respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_3rdbp_date',
        description: 'List of respondents 3rd BP dates (In years)',
        textInside: 'Shows years which respondents had their 3rd BP results and gives counts on how many had their 3rd BP results for each year.',
        component: markRaw(ThirdBPyear),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_values',
        description: 'Report on Pre-treatment/confirmatory DM values ',
        textInside: 'A count of respondents who have Pre-treatment/confirmatory DM values at hand. A respondent can have one or a combination of those.',
        component: markRaw(DMValues),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_fbs',
        description: 'Report on Pre-treatment/confirmatory DM LBS values ',
        textInside: 'A count of respondents who have Pre-treatment/confirmatory DM LBS values at hand. A respondent can have one or a combination of those.',
        component: markRaw(FBSValues),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_fbs_date',
        description: 'List of respondents Historical/Confirmatory FBS (In years)',
        textInside: 'Shows years which respondents had their FBS Confirmed and gives counts on how many were confirmed for each year.',
        component: markRaw(FBSYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_rbs',
        description: 'Report on Pre-treatment/confirmatory DM RBS values ',
        textInside: 'A count of respondents who have Pre-treatment/confirmatory DM RBS values at hand. A respondent can have one or a combination of those.',
        component: markRaw(RBSValues),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_rbs_date',
        description: 'List of respondents Historical/Confirmatory RBS (In years)',
        textInside: 'Shows years which respondents had their RBS Confirmed and gives counts on how many were confirmed for each year.',
        component: markRaw(RBSYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_hba1c',
        description: 'Report on Pre-treatment/confirmatory DM HBA1c values ',
        textInside: 'A count of respondents who have Pre-treatment/confirmatory DM HBA1c values at hand. A respondent can have one or a combination of those.',
        component: markRaw(HBA1cValues),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_hba1c_date',
        description: 'List of respondents Historical/Confirmatory HBA1c (In years)',
        textInside: 'Shows years which respondents had their HBA1c Confirmed and gives counts on how many were confirmed for each year.',
        component: markRaw(HBA1cYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_complaints',
        description: 'List of respondents with complaints',
        textInside: 'Shows counts of respondents for each complaint and also complaints combo.',
        component: markRaw(Complaints),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_complaints_oth',
        description: 'List of other complaints for respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_fam_history',
        description: 'List of respondents with Diabetes/Hypertension family history',
        textInside: 'Shows counts of respondents with Diabetes/Hypertension family history and also the combo.',
        component: markRaw(Complaints),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cardio_nonmod',
        description: 'List of respondents with Cardio Vascular Risk (non-mod)',
        textInside: 'Shows counts of respondents with Cardio Vascular Risk (non-mod) and also the combo.',
        component: markRaw(CardioNonMod),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_cardio_mod',
        description: 'List of respondents with Cardio Vascular Risk (modifiable)',
        textInside: 'Shows counts of respondents with Cardio Vascular Risk (mod) and also the combo.',
        component: markRaw(CardioMod),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_b4enrol',
        description: 'List of respondents (Medication before enrollment)',
        textInside: 'Shows counts of respondents on medication before enrollment and also the combo.',
        component: markRaw(MedsBeforeEnroll),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_insulin_med_date',
        description: 'List of respondents (Insulin start date)',
        textInside: 'Shows years which respondents started taking insulin and counts for each year.',
        component: markRaw(InsulinYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_oralanti_med_date',
        description: 'List of respondents (Oral Anti Diabetics Meds)',
        textInside: 'Shows years which respondents started taking Oral Anti Diabetics Meds and counts for each year.',
        component: markRaw(OralAntiMedYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_bp_med_date',
        description: 'List of respondents (BP Medication)',
        textInside: 'Shows years which respondents started taking BP Medication and counts for each year.',
        component: markRaw(BPMedYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_arvs_med_date',
        description: 'List of respondents (ARV Medication)',
        textInside: 'Shows years which respondents started taking ARV Medication and counts for each year.',
        component: markRaw(ARVMedYearDiag),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: ' ncd_med_b4enrol_oth',
        description: 'List of other medicines (before enrolling) for respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_respi',
        description: 'List of respondents (Physical Eaxmination: Respiratory)',
        textInside: 'Shows counts of respondents with respiratory ailments and also the combo.',
        component: markRaw(PhysicalRespiratory),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_heart',
        description: 'List of respondents (Physical Eaxmination: Heart)',
        textInside: 'Shows counts of respondents with heart problems and also the combo.',
        component: markRaw(PhysicalHeart),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_b4enrol_oth',
        description: 'List of other medicines (before enrolling) for respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_heart_specify',
        description: 'List of other heart problems for respondents ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_neuro',
        description: 'Respondents with Neuro Problems ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_stroke_specify',
        description: 'Respondents stroke (specified) ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_preg',
        description: 'Report on Pregnancy Status',
        textInside: 'A count of respondents with Pregnancy Status on first day of registration into the NCD program.',
        component: markRaw(PregancyStatus),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_preg_delivery',
        description: 'Respondents Expected date of delivery ',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_patient_initial_consultation_complete',
        description: 'Report on Patient Initial Consultion Status ',
        textInside: 'A count of respondents who has their Patient Information completed and the status of the process.',
        component: markRaw(PatientInformationForm),
        props: {
            yesDescription: String,
            noDescription: String,
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_status',
        description: 'List of Instances and count of Current Status',
        textInside: 'A count of respondents current status into the NCD program per visit.',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_status_why',
        description: 'List of Instances Current Status (Reason for no)',
        textInside: 'A count of respondents current status  for those who answered no into the NCD program per visit.',
        component: markRaw(InstanceStatusWhy),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_prev_health_facility',
        description: 'List of Instances and count of previous facilities respondents moved from',
        textInside: 'A count of respondents current of previous facilities moved from per visit.',
        component: markRaw(InstancePreviousFacility),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_prev_oth_health_facility',
        description: 'List of Instances and count of previous facilitiesnNot on the list)respondents moved from',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_curr_health_facility',
        description: 'List of Instances and count of current facilities respondents moved to',
        textInside: 'A count of respondents current of current facilities moved to per visit.',
        component: markRaw(InstanceCurrentFacility),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_curr_oth_health_facility',
        description: 'List of Instances and count of current facilities (not on the list) respondents moved from',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_tranfered',
        description: 'List of Instances and count of respondents who know when they were transferred per visit',
        textInside: 'A count of respondents who know their transfer date per visit.',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_transfer_date',
        description: 'List of Instances and the dates when respondents transfered',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_opted_out',
        description: 'List of Instances and count of respondents who gave reasons why they opted out',
        textInside: 'A count of respondents who know their opted out per visit.',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_opted_out_why',
        description: 'List of Instances and the reasons why respondents opted out?',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_status_other',
        description: 'List of Instances and further information on opting out',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_ltfu',
        description: 'List of Instances and comments on opting out',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_patient_status_complete',
        description: 'List of Instances Form Complete Status',
        textInside: 'A count of respondents patient status information per visit.',
        component: markRaw(InstancePatientStatusComplete),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_visit_date',
        description: 'List of Instances Form Complete Status',
        textInside: 'A count of respondents patient status information per visit.',
        component: markRaw(InstanceVisitDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_visit_type',
        description: 'List of Instances and count of visit types by respondents',
        textInside: 'A count of respondents visit types to per visit.',
        component: markRaw(InstanceVisitType),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_age_2',
        description: 'List of Instances and count of respondents age',
        textInside: 'A count of respondents age to per visit.',
        component: markRaw(InstanceAge),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_weight',
        description: 'List of Instances and count of respondents weight',
        textInside: 'A count of respondents weight to per visit.',
        component: markRaw(InstanceWeightGroups),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_height',
        description: 'List of Instances and count of respondents height',
        textInside: 'A count of respondents height to per visit.',
        component: markRaw(InstanceHeightGroups),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_bodymassindex',
        description: 'List of Instances and count of respondents BMI',
        textInside: 'A count of respondents BMI to per visit.',
        component: markRaw(InstanceBMIGroups),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_bp',
        description: 'Download BP values per Instance/Visit',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_bp_systolic',
        description: 'Download BP Systolic values per Instance/Visit',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_bp_diastolic',
        description: 'Download BP diastolic values per Instance/Visit',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_pulse',
        description: 'Download pulse values per Instance/Visit',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_signs_symp',
        description: 'Download symptoms values per Instance/Visit',
        textInside: 'A count of symptons which respondents experienced per visit.',
        component: markRaw(InstanceVisitSignsAndSymptoms),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_foot_exam',
        description: 'Download foot exam results per Instance/Visit',
        textInside: 'A count of foot exam results which respondents had per visit.',
        component: markRaw(InstanceVisitSignsAndSymptoms),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_eye_exam',
        description: 'Download eye exam results per Instance/Visit',
        textInside: 'A count of eye exam results which respondents had per visit.',
        component: markRaw(InstanceEyeExam),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_eye_fundoscopy',
        description: 'Download eye fundoscopy data per Instance/Visit',
        textInside: 'A count of eye fundoscopy data which respondents answered if they had taken it or not had per visit.',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_eye_fundoscopy_outcome',
        description: 'Download eye fundoscopy results per Instance/Visit',
        textInside: 'A count of eye fundoscopy results which respondents answered if they had taken it or not had per visit.',
        component: markRaw(InstanceFundoscopyOutcome),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_interrupt',
        description: 'This field has been scraped',
        textInside: 'This field is no longer being asked from respondents.',
        component: markRaw(Scraped),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_interruption',
        description: 'Data for treatment interruption per visit',
        textInside: 'A count of the number of respondents whose treatments were interrupted (for each visit).',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_interruption_period',
        description: 'Data for treatment interruption period per visit',
        textInside: 'A count of the number of respondents whose treatments were interrupted (for each visit).',
        component: markRaw(InstanceInterruptionPeriod),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_counc_reason',
        description: 'Data for counselling reasons period per visit',
        textInside: 'A count of the number of respondents who did counselling (for each visit).',
        component: markRaw(InstanceCouncilling),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_creatin_done',
        description: 'Data for respondents who had creatin test done per visit',
        textInside: 'A count of the number of respondents who had creatin test done (for each visit).',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_creatin',
        description: 'Data for respondents creatine levels per visit',
        textInside: 'A count of the number of respondents creatine levels (for each visit).',
        component: markRaw(InstanceCreatine),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_creatin_clear_male',
        description: 'Download Data for male respondents creatine levels per visit',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_creatin_clear_female',
        description: 'Download Data for female respondents creatine levels per visit',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_kidney_disease_stage',
        description: 'Summary Report for kidney disease Stage per visit',
        textInside: 'A count of respondents at each kidney disease stage per visit.',
        component: markRaw(InstanceKidneyDiseaseStage),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_fbs_done',
        description: 'Summary Report for those who had fbs done per visit',
        textInside: 'A count of respondents who did fbs per visit.',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_fbs',
        description: 'Summary Report for those who had fbs groups per visit',
        textInside: 'A count of respondents in FBS groups per visit.',
        component: markRaw(InstanceFBS),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_rbs_done',
        description: 'Summary Report for those who had rbs done per visit',
        textInside: 'A count of respondents who did rbs per visit.',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_rbs',
        description: 'Summary Report for those who had RBS groups per visit',
        textInside: 'A count of respondents in RBS groups per visit.',
        component: markRaw(InstanceRBS),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_hba1c_done',
        description: 'Summary Report for those who had hba1c done per visit',
        textInside: 'A count of respondents who did hba1c per visit.',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_hba1c',
        description: 'Summary Report for those who had HBA1c groups per visit',
        textInside: 'A count of respondents in HBA1c groups per visit.',
        component: markRaw(InstanceHBA1c),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_uria_done',
        description: 'Summary Report for those who had urinalysis done per visit',
        textInside: 'A count of respondents who did urinalysis per visit.',
        component: markRaw(InstanceYesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_uria',
        description: 'Summary Report urinalysis results per visit',
        textInside: 'A count of respondents who had urinalysis results per visit.',
        component: markRaw(InstanceUrinalysis),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn',
        description: 'Summary Report HTN responses per visit',
        textInside: 'A count of respondents HTN results per visit.',
        component: markRaw(InstanceHTN),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_htc_dosages',
        description: 'Summary Report HTC Dosages responses per visit',
        textInside: 'A count of respondents HTC Dosage per visit.',
        component: markRaw(InstanceHTNHTCDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_htc_dosages_freq',
        description: 'Summary Report HTC Dosages Frequencies responses per visit',
        textInside: 'A count of respondents HTC Dosage Frequencies per visit.',
        component: markRaw(InstanceHTNHTCDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_hct',
        description: 'Summary Report HTC Medical Reasons responses per visit',
        textInside: 'A count of respondents HTN HTC Medical Reasons per visit.',
        component: markRaw(InstanceHTNHTCMedReasons),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_enap_dosages',
        description: 'Summary Report on enapril dosages responses per visit',
        textInside: 'A count of respondents enapril dosages per visit.',
        component: markRaw(InstanceHTNEnapDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_enap_dosages_freq',
        description: 'Summary Report HTN enapril dosage frequencies responses per visit',
        textInside: 'A count of respondents HTN enapril dosage frequencies Reasons per visit.',
        component: markRaw(InstanceHTNEnapDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_enap',
        description: 'Summary Report HTN enapril medical reasons responses per visit',
        textInside: 'A count of respondents HTN enapril medical reasons per visit.',
        component: markRaw(InstanceHTNEnapMedReasons),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_aml_dosages',
        description: 'Summary Report HTC Amlodin dosages responses per visit',
        textInside: 'A count of respondents HTC Amlodin dosages per visit.',
        component: markRaw(InstanceHTNAmlDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_aml_dosages_freq',
        description: 'Summary Report HTC Amlodin dosages Frequences responses per visit',
        textInside: 'A count of respondents HTC Amlodin dosages frequences per visit.',
        component: markRaw(InstanceHTNAmlDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_aml',
        description: 'Summary Report HTC Amlodin medical reasons responses per visit',
        textInside: 'A count of respondents HTC Amlodin medical reasons per visit.',
        component: markRaw(InstanceHTNAmlMedReasons),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_ate_dosages',
        description: 'Summary Report HTC Atenolol dosages responses per visit',
        textInside: 'A count of respondents HTC Atenolol dosages per visit.',
        component: markRaw(InstanceHTNAteDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_ate_dosages_freq',
        description: 'Summary Report HTC Atenolol dosages Frequences responses per visit',
        textInside: 'A count of respondents HTC Atenolol dosages frequences per visit.',
        component: markRaw(InstanceHTNAteDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_ate',
        description: 'Summary Report HTC Atenolol medical reasons responses per visit',
        textInside: 'A count of respondents HTC Atenolol medical reasons per visit.',
        component: markRaw(InstanceHTNAteMedReasons),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    //
    {
        name: 'ncd_htn_spir_dosages',
        description: 'Summary Report HTC Spironolactone dosages responses per visit',
        textInside: 'A count of respondents HTC Spironolactone dosages per visit.',
        component: markRaw(InstanceHTNSpirDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_spir_dosages_freq',
        description: 'Summary Report HTC Spironolactone dosages Frequences responses per visit',
        textInside: 'A count of respondents HTC Spironolactone dosages frequences per visit.',
        component: markRaw(InstanceHTNSpirDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_spir',
        description: 'Summary Report HTC Spironolactone medical reasons responses per visit',
        textInside: 'A count of respondents HTC Spironolactone medical reasons per visit.',
        component: markRaw(InstanceHTNSpirMedReasons),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    //
    {
        name: 'ncd_htn_furo_dosages',
        description: 'Summary Report HTC Furosemide dosages responses per visit',
        textInside: 'A count of respondents HTC Furosemide dosages per visit.',
        component: markRaw(InstanceHTNFuroDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_furo_dosages_freq',
        description: 'Summary Report HTC Furosemide dosages Frequences responses per visit',
        textInside: 'A count of respondents HTC Furosemide dosages frequences per visit.',
        component: markRaw(InstanceHTNFuroDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_furo',
        description: 'Summary Report HTC Furosemide medical reasons responses per visit',
        textInside: 'A count of respondents HTC Furosemide medical reasons per visit.',
        component: markRaw(InstanceHTNFuroMedReasons),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    //
    {
        name: 'ncd_htn_los_dosages',
        description: 'Summary Report HTC Losartan dosages responses per visit',
        textInside: 'A count of respondents HTC Losartan dosages per visit.',
        component: markRaw(InstanceHTNLosDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_furo_dosages_freq',
        description: 'Summary Report HTC Losartan dosages Frequences responses per visit',
        textInside: 'A count of respondents HTC Losartan dosages frequences per visit.',
        component: markRaw(InstanceHTNLosDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_furo',
        description: 'Summary Report HTN Losartan medical reasons responses per visit',
        textInside: 'A count of respondents HTN Losartan medical reasons per visit.',
        component: markRaw(InstanceHTNLosMedReasons),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    //
    {
        name: 'ncd_htn_nife_dosages',
        description: 'Summary Report HTN Nifedipine dosages responses per visit',
        textInside: 'A count of respondents HTN Nifedipine dosages per visit.',
        component: markRaw(InstanceHTNNifeDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_htn_nife_dosages_freq',
        description: 'Summary Report HTN Nifedipine dosages Frequences responses per visit',
        textInside: 'A count of respondents HTN Nifedipine dosages frequences per visit.',
        component: markRaw(InstanceHTNNifeDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_nife',
        description: 'Summary Report HTN Nifedipine medical reasons responses per visit',
        textInside: 'A count of respondents HTN Nifedipine medical reasons per visit.',
        component: markRaw(InstanceHTNNifeMedReasons),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_other',
        description: 'Download Data for other DM Medicines per visit',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    //
    {
        name: 'ncd_dm_met_dosage',
        description: 'Summary Report DM Metformin dosages responses per visit',
        textInside: 'A count of respondents DM Metformin dosages per visit.',
        component: markRaw(InstanceDMMetDosages),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_met_dosages_freq',
        description: 'Summary Report DM Metformin dosages Frequences responses per visit',
        textInside: 'A count of respondents DM Metformin dosages frequences per visit.',
        component: markRaw(InstanceDMMetDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_met',
        description: 'Summary Report DM Metformin medical reasons responses per visit',
        textInside: 'A count of respondents DM Metformin medical reasons per visit.',
        component: markRaw(InstanceDMMetDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
     //
     {
        name: 'ncd_dm_potra_dosage',
        description: 'Download DM Protaphane dosages responses per visit',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_dm_potra_dosages_freq',
        description: 'Summary Report DM Protaphane dosages Frequences responses per visit',
        textInside: 'A count of respondents DM Protaphane dosages frequences per visit.',
        component: markRaw(InstanceDMProtDosagesFreq),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'ncd_med_reasons_potra',
        description: 'Summary Report DM Protaphane medical reasons responses per visit',
        textInside: 'A count of respondents DM Protaphane medical reasons per visit.',
        component: markRaw(InstanceDMProtMedReasons),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    //
    {
       name: 'ncd_dm_actra_dosage',
       description: 'Download DM Actraphane dosages responses per visit',
       textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
       component: markRaw(Download),
       props: {
           description: String,
           textInside: String,
           itemMetadata: Object,
           projectData: Array<IProjectData>
       }
   },
   {
       name: 'ncd_dm_actra_dosage_freq',
       description: 'Summary Report DM Actraphane dosages Frequences responses per visit',
       textInside: 'A count of respondents DM Actraphane dosages frequences per visit.',
       component: markRaw(InstanceDMActDosagesFreq),
       props: {
           description: String,
           textInside: String,
           itemMetadata: Object,
           projectData: Array<IProjectData>
       }
   },
   {
       name: 'ncd_med_reasons_actra',
       description: 'Summary Report DM Actraphane medical reasons responses per visit',
       textInside: 'A count of respondents DM Actraphane medical reasons per visit.',
       component: markRaw(InstanceDMActMedReasons),
       props: {
           description: String,
           textInside: String,
           itemMetadata: Object,
           projectData: Array<IProjectData>
       }
   },
   //
   {
    name: 'ncd_dm_actrapid_dosage',
    description: 'Download DM Actrapid dosages responses per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_dm_actrapi_dosage_freq',
    description: 'Summary Report DM Actrapid dosages Frequences responses per visit',
    textInside: 'A count of respondents DM Actrapid dosages frequences per visit.',
    component: markRaw(InstanceDMActraDosagesFreq),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_med_reasons_actrap',
    description: 'Summary Report DM Actrapidmedical reasons responses per visit',
    textInside: 'A count of respondents DM Actrapid medical reasons per visit.',
    component: markRaw(InstanceDMActraMedReasons),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
 //
 {
    name: 'ncd_dm_insu_dosage',
    description: 'Download DM Other Insulin dosages responses per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_dm_insu_dosage_freq',
    description: 'Summary Report DM Other Insuliin dosages Frequences responses per visit',
    textInside: 'A count of respondents DM Other Insulin dosages frequences per visit.',
    component: markRaw(InstanceDMInsDosagesFreq),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_med_insu_actrap',
    description: 'Summary Report DM Insulin reasons responses per visit',
    textInside: 'A count of respondents DM Insulin medical reasons per visit.',
    component: markRaw(InstanceDMInsMedReasons),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
 //
 {
    name: 'ncd_dm_asp_dosage',
    description: 'Summary Report DM Asprin dosages responses per visit',
    textInside: 'A count of respondents DM Asprin dosages per visit.',
    component: markRaw(InstanceDMAspDosages),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_dm_asp_dosages_freq',
    description: 'Summary Report DM Asprin dosages Frequences responses per visit',
    textInside: 'A count of respondents DM Asprin dosages frequences per visit.',
    component: markRaw(InstanceDMAspDosagesFreq),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_med_reasons_asp',
    description: 'Summary Report DM Asprin medical reasons responses per visit',
    textInside: 'A count of respondents DM Asprin medical reasons per visit.',
    component: markRaw(InstanceDMAspMedReasons),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_htn_oth_spec',
    description: 'Download HTN Other Medicines responses per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_asthma',
    description: 'Summary Report Asthma Treatment responses per visit',
    textInside: 'A count of respondents asthma treatment per visit',
    component: markRaw(InstanceAsthma),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_asthma_oth',
    description: 'Download Other Asthma Treatment responses per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_referred',
    description: 'Summary Report of where respondents where referred to per visit',
    textInside: 'A count of respondents referred to per visit',
    component: markRaw(InstanceReferredTo),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_referred_dh',
    description: 'Download District Hospital referred to per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_referred_oth',
    description: 'Download Other where respondent was referred to per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_referred_sp',
    description: 'Download Specialists where respondent was referred to per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_other_tests',
    description: 'Summary Report of other tests taken by respondents to per visit',
    textInside: 'A count of respondents tests per visit',
    component: markRaw(InstanceOtherTests),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_other_tests_spec',
    description: 'Download Other Tests on respondents per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_next_review',
    description: 'Download next review dates for respondents per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_transfer_facility',
    description: 'Download facility where respondents transferred to per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Scraped),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_transfer_to_facility',
    description: 'Report on facility where respondents transferred to per visit',
    textInside: 'Download and see facility where respondents transferred to per visit.',
    component: markRaw(InstanceTransferToFacility),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_followup_oth',
    description: 'Download other reasons for follow up per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'ncd_clinician',
    description: 'Download clinician names per visit',
    textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
    component: markRaw(Download),
    props: {
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
{
    name: 'chronic_diseases_visits_complete',
    description: 'Report on Patient visits Status ',
    textInside: 'A count of respondents who has their Patient visits completed and the status of the process.',
    component: markRaw(PatientInformationForm),
    props: {
        yesDescription: String,
        noDescription: String,
        description: String,
        textInside: String,
        itemMetadata: Object,
        projectData: Array<IProjectData>
    }
},
]