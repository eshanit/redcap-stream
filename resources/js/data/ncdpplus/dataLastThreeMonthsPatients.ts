import { markRaw } from "vue";
import { type IEnrollment } from "@/types/IProject";
import TypeIDiabetes from "@/components/packages/ncdpplus/TypeIDiabetes.vue";
import TypeIIDiabetes from "@/components/packages/ncdpplus/TypeIIDiabetes.vue";
import UnspecifiedDiabetes from "@/components/packages/ncdpplus/UnspecifiedDiabetes.vue";
import Rheumatic from "@/components/packages/ncdpplus/Rheumatic.vue";
import Congenital from "@/components/packages/ncdpplus/Congenital.vue";
import HeartFailure from "@/components/packages/ncdpplus/HeartFailure.vue";
import OtherHeartDisease from "@/components/packages/ncdpplus/OtherHeartDisease.vue";
import SickleCellDisease from "@/components/packages/ncdpplus/SickleCellDisease.vue";
import RespiratoryDisease from "@/components/packages/ncdpplus/RespiratoryDisease.vue";
import ChronicKidneyDisease from "@/components/packages/ncdpplus/ChronicKidneyDisease.vue";
import AllCondtionsDisease from "@/components/packages/ncdpplus/AllCondtionsDisease.vue";

export default [
    {
        name: 'all',
        description: 'All Patients  enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of all patients enrolled per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus)',
        component: markRaw(AllCondtionsDisease),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'diabetes_1',
        description: 'Type I Diabetes Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Type I Diabetes per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus).',
        component: markRaw(TypeIDiabetes),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'diabetes_2',
        description: 'Type II Diabetes Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Type II Diabetes per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus clinic).',
        component: markRaw(TypeIIDiabetes),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'gestational_diabetes',
        description: 'Unspecified Diabetes Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Unspecified Diabetes per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus clinic).',
        component: markRaw(UnspecifiedDiabetes),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'rheumatic',
        description: 'Rheumatic Heart Disease Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Rheumatic Heart Disease per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus clinic).',
        component: markRaw(Rheumatic),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'congenital',
        description: 'Congenital Heart Disease Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Congenital Heart Disease per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus clinic).',
        component: markRaw(Congenital),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'heart_failure',
        description: 'Heart Failure Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Heart Failure per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus clinic).',
        component: markRaw(HeartFailure),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'other_cardiac',
        description: 'Other/ Unspecified Cardiac Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Other/ Unspecified Cardiac conditions per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus clinic).',
        component: markRaw(OtherHeartDisease),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'sickle_cell',
        description: 'Sickle Cell Disease Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Sickle Cell Disease per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus clinic).',
        component: markRaw(SickleCellDisease),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'chronic_respiratory',
        description: 'Chronic Respiratory Disease Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Chronic Respiratory Disease per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus clinic).',
        component: markRaw(RespiratoryDisease),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    },
    {
        name: 'chronic_kidney',
        description: 'Chronic Kidney Disease Patients enrolled past 3 months at PEN Plus clinic',
        textInside: 'The number of patients enrolled with Chronic Kidney Disease per quarter (enrolled past 3 months patients, i.e, All patients enrolled in care at the PEN Plus clinic).',
        component: markRaw(ChronicKidneyDisease),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>,
            indicator: 'threeMonthsDeath'
        }
    }
]