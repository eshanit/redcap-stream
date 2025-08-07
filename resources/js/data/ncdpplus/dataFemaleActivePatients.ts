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
            description: 'All Female Patients active at PEN Plus clinic',
            textInside: 'The number of all female patients enrolled per quarter (active female patients, i.e, All patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
            component: markRaw(AllCondtionsDisease),
            props: {
                description: String,
                textInside: String,
                patientData: Array<IEnrollment>
            }
        },
    {
        name: 'diabetes_1',
        description: 'Type I Diabetes Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Type I Diabetes per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(TypeIDiabetes),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    },
    {
        name: 'diabetes_2',
        description: 'Type II Diabetes Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Type II Diabetes per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(TypeIIDiabetes),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    },
    {
        name: 'gestational_diabetes',
        description: 'Unspecified Diabetes Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Unspecified Diabetes per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(UnspecifiedDiabetes),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    },
    {
        name: 'rheumatic',
        description: 'Rheumatic Heart Disease Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Rheumatic Heart Disease per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(Rheumatic),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    },
    {
        name: 'congenital',
        description: 'Congenital Heart Disease Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Congenital Heart Disease per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(Congenital),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    },
    {
        name: 'heart_failure',
        description: 'Heart Failure Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Heart Failure per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(HeartFailure),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    },
    {
        name: 'other_cardiac',
        description: 'Other/ Unspecified Cardiac Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Other/ Unspecified Cardiac conditions per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(OtherHeartDisease),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    },
      {
        name: 'sickle_cell',
        description: 'Sickle Cell Disease Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Sickle Cell Disease per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(SickleCellDisease),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    },
    {
        name: 'chronic_respiratory',
        description: 'Chronic Respiratory Disease Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Chronic Respiratory Disease per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(RespiratoryDisease),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    },
     {
        name: 'chronic_kidney',
        description: 'Chronic Kidney Disease Female Patients active at PEN Plus clinic',
        textInside: 'The number of female patients enrolled with Chronic Kidney Disease per quarter (active female patients, i.e, All female patients enrolled in care at the PEN Plus who have not been lost to follow up, transferred out, or died.',
        component: markRaw(ChronicKidneyDisease),
        props: {
            description: String,
            textInside: String,
            patientData: Array<IEnrollment>
        }
    }
]