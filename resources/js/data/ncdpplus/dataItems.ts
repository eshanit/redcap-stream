import { markRaw } from "vue";
import { type IProjectData } from '@/types/IProject'
import Download from '@/components/insights/shared/Download.vue'
import Gender from "@/components/insights/shared/Gender.vue";
import Age from "@/components/insights/shared/Age.vue";
import HouseHold from "@/components/insights/ncdpplus/HouseHold.vue";
import YesNo from "@/components/insights/shared/YesNo.vue";
import SingleSelectAnswer from "@/components/insights/shared/SingleSelectAnswer.vue";
import MainDiagnosisDate from "@/components/insights/ncdpplus/MainDiagnosisDate.vue";
import HIVTestDate from "@/components/insights/ncdpplus/HIVTestDate.vue";
import ARTStartDate from "@/components/insights/ncdpplus/ARTStartDate.vue";
import EmploymentStatus from "@/components/insights/ncdpplus/EmploymentStatus.vue";
import DemographicsComplete from "@/components/insights/ncdpplus/DemographicsComplete.vue";
import Faciility from "@/components/insights/ncdpplus/Faciility.vue";
import MainDiagnosis from "@/components/insights/ncdpplus/MainDiagnosis.vue";
import SubDiagnosis from "@/components/insights/ncdpplus/SubDiagnosis.vue";
import SubCategory from "@/components/insights/ncdpplus/SubCategory.vue";
import ReferredFrom from "@/components/insights/ncdpplus/ReferredFrom.vue";
import Biomass from "@/components/insights/ncdpplus/Biomass.vue";
import OtherExposure from "@/components/insights/ncdpplus/OtherExposure.vue";
import HistoryComplications from "@/components/insights/ncdpplus/HistoryComplications.vue";
import HIV from "@/components/insights/ncdpplus/HIV.vue";
import TB from "@/components/insights/ncdpplus/TB.vue";
import TBYear from "@/components/insights/ncdpplus/TBYear.vue";
import CardioDate from "@/components/insights/ncdpplus/CardioDate.vue";
import AllergyDate from "@/components/insights/ncdpplus/AllergyDate.vue";
import DepressionDate from "@/components/insights/ncdpplus/DepressionDate.vue";
import GERDDate from "@/components/insights/ncdpplus/GERDDate.vue";
import AnxietyDate from "@/components/insights/ncdpplus/AnxietyDate.vue";
import RhinoDate from "@/components/insights/ncdpplus/RhinoDate.vue";
import OsteoporosisDate from "@/components/insights/ncdpplus/OsteoporosisDate.vue";
import Triggers from "@/components/insights/ncdpplus/Triggers.vue";

export default [
    {
        name: 'record_id',
        description: 'Download record ids',
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
        name: 'name_demo',
        description: 'Download respondent names',
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
        name: 'gender_demo',
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
        name: 'date_birth_demo',
        description: 'List of respondents date of birth',
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
        name: 'enrol_date_demo',
        description: 'Respondents age account',
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
        name: 'physical_address',
        description: 'List of respondents physical addresses',
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
        name: 'near_place',
        description: 'List of respondents place of living',
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
        name: 'res_phone_no_demo',
        description: 'Download List of respondents phone numbers',
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
        name: 'res_guard_name_demo',
        description: 'Download List of respondents guardians',
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
        name: 'res_patient_relation_demo',
        description: 'Download List of respondents relations',
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
        name: 'resp_hh_size_demo',
        description: 'Respondents house hold sizes counts',
        textInside: 'A look at the number of respondents in relationship to household size',
        component: markRaw(HouseHold),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'res_home_visits_demo',
        description: 'Respondents responses to house visits',
        textInside: 'A look at the number of respondents who agrees to home visits and those who dont',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'res_chw_name_demo',
        description: 'Download List of chw name',
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
        name: 'employment_status',
        description: 'Analysis of employment status',
        textInside: 'An analysis of the employment status of the respondents',
        component: markRaw(EmploymentStatus),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'demographics_complete',
        description: 'Completed Demographics information',
        textInside: 'An analysis of the demographics data of the respondents',
        component: markRaw(DemographicsComplete),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'facility_demo',
        description: 'Respondent Facility Analysis',
        textInside: 'A count of the respondents facility',
        component: markRaw(Faciility),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'main_diagnosis',
        description: 'Respondent Main Diagnosis',
        textInside: 'A count of the respondents main diagnosis',
        component: markRaw(MainDiagnosis),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'diagnosis_date',
        description: 'Respondent Main Diagnosis dates',
        textInside: 'A count of the respondents main diagnosis per year',
        component: markRaw(MainDiagnosisDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'sub_diagnosis',
        description: 'Respondent Sub Diagnosis',
        textInside: 'A count of the respondents sub diagnosis',
        component: markRaw(SubDiagnosis),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'sub_categ',
        description: 'Respondent Sub Category Diagnosis',
        textInside: 'Respondent Sub Category Diagnosis.',
        component: markRaw(SubCategory),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'spec_diagnosis',
        description: 'Respondent Specific Diagnosis',
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
        name: 'referral_from',
        description: 'Referred from',
        textInside: 'A count of places where clients where referred from ',
        component: markRaw(ReferredFrom),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'spec_referal',
        description: 'Download other specified referals',
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
        name: 'biomass',
        description: 'Exposure to biomass smoke',
        textInside: 'A count of the respondents exposed to biomass smoke',
        component: markRaw(Biomass),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'other_exposure',
        description: 'Exposure to other elements',
        textInside: 'A count of the respondents specified referals',
        component: markRaw(OtherExposure),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'spec_exposure',
        description: 'Download Other specified exposure',
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
        name: 'compl_history',
        description: 'History of complications',
        textInside: 'An analysis on the respondents history of complications',
        component: markRaw(HistoryComplications),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'hiv',
        description: 'HIV analysis',
        textInside: 'HIV',
        component: markRaw(HIV),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'test_date',
        description: 'HIV test date',
        textInside: 'HIV',
        component: markRaw(HIVTestDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'art_date',
        description: 'ART start date',
        textInside: 'ART start date',
        component: markRaw(ARTStartDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'tb',
        description: 'TB analysis',
        textInside: 'TB analysis',
        component: markRaw(TB),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'year',
        description: 'TB Year',
        textInside: 'TB Year',
        component: markRaw(TBYear),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'cardio_date',
        description: 'Cardiovascular disease (Date)',
        textInside: 'Cardiovascular disease (year analysis)',
        component: markRaw(CardioDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'allergy',
        description: 'Allergy Analysis',
        textInside: 'Count of number of respondents who have (and dont) have allergies',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'depression',
        description: 'Depression Analysis',
        textInside: 'Count of number of respondents who have (and dont) have depression',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'allergy_date',
        description: 'Allergy Dates',
        textInside: 'Food allergy time analysis (year)',
        component: markRaw(AllergyDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'depression_date',
        description: 'Depression Dates',
        textInside: 'Respondent Depression time analysis (year)',
        component: markRaw(DepressionDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'gerd_date',
        description: 'GERD Dates',
        textInside: 'Respondent GERD time analysis (year)',
        component: markRaw(GERDDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'anxiety',
        description: 'Anxiety',
        textInside: 'Respondent anxiety counts (yes or no)',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'anxiety_date',
        description: 'Anxiety',
        textInside: 'Time analysis for anxiety dates',
        component: markRaw(AnxietyDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'rhinosinu_date',
        description: 'Chronic Rhinosinusitis',
        textInside: 'Time analysis for Chronic Rhinosinusitis dates',
        component: markRaw(RhinoDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
     {
        name: 'osteoporosis',
        description: 'osteoporosis',
        textInside: 'Respondent osteoporosis counts (yes or no)',
        component: markRaw(YesNo),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
      {
        name: 'osteo_date',
        description: 'Osteoporosis Time Analysis',
        textInside: 'Osteoporosis Time Analysis',
        component: markRaw(OsteoporosisDate),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
     {
        name: 'triggers',
        description: 'Trigger Analysis',
        textInside: 'Trigger Analysis',
        component: markRaw(Triggers),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },
    {
        name: 'spec_triggers',
        description: 'Download Other specified triggers',
        textInside: 'The type of data for this field cannot be analysed by this engine at the moment, however you can click the button below to use other specialized software for further analysis.',
        component: markRaw(Download),
        props: {
            description: String,
            textInside: String,
            itemMetadata: Object,
            projectData: Array<IProjectData>
        }
    },

]