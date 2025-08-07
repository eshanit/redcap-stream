<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { TriangleAlert, FileSearch } from 'lucide-vue-next';
import useGetPatientsData from '@/composables/useGetPatientsData';
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue';
import dataNewMortalityPatients from '@/data/ncdpplus/dataNewMortalityPatients';

const props = defineProps<{
    project: IProject,
}>();


const packageRoot = computed(() => {
  if (props.project.project_id == 39) {
    return 'ncd_pplus'
  }
  return 'ncd'
})

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: props.project.app_title,
    href: '/' + packageRoot.value + '/project/' + props.project.project_id,
  },
  {
    title: 'Custom Packages',
    href: '/packages/project/' + props.project.project_id + '/dashboard',
  },
  {
    title: 'Core Indicators',
    href: '/packages/project/' + props.project.project_id + '/core_indicators',
  },
  {
    title: 'Deceased < 3 Months',
    href: ''
  }
];


const form = useForm({
    condition: ''
});

const projectId = ref(props.project.project_id);
const condition = ref(form.condition);
const type = ref('mortalitythreemonths')
const tag = ref()



const conditions = [
    { name: 'all', label: 'All Death (Past 3 Months)', tag: 'all' },
    { name: 'diabetes_1', label: 'Type 1 Diabetes', tag: 'diabetes' },
    { name: 'diabetes_2', label: 'Type 2 Diabetes', tag: 'diabetes' },
    { name: 'gestational_diabetes', label: 'Unspecified/Gestational Diabetes', tag: 'diabetes' },
    { name: 'rheumatic', label: 'Rheumatic Heart Disease', tag: 'cardiac' },
    { name: 'congenital', label: 'Congenital Heart Disease' , tag: 'cardiac'},
    { name: 'heart_failure', label: 'Heart Failure', tag: 'cardiac' },
    { name: 'other_cardiac', label: 'Other/ Unspecified Cardiac Condition', tag: 'cardiac'},
    { name: 'hypertension', label: 'Hypertension', tag: 'hypertension' },
    { name: 'sickle_cell', label: 'Sickle Cell Anemia',  tag:'scd'},
    { name: 'chronic_respiratory', label: 'Chronic Respiratory Disease',  tag: 'crd'},
    { name: 'chronic_kidney', label: 'Chronic Kidney Disease', tag: 'ckd' },
    { name: 'chronic_liver', label: 'Chronic Liver Disease', tag: 'cld' },
];

// Watch for condition changes if needed
watch(() => form.condition, (newVal) => {
    condition.value = newVal;
    tag.value = conditions.find(r => r.name == newVal)?.tag
});

const { itemData, isLoading, error } = useGetPatientsData(projectId,type ,condition, tag);

const selectedComponent = (condition: string) => {
    return dataNewMortalityPatients.find(d => d.name == condition)
}

</script>

<template>
    <Head title="Customized Packaged Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                        <Heading title="Patients Mortality Past 3 Months Indicators"
                description="Patients deceased in the last 3 months per disease/condition aggregated per quarter." class="pt-5" />

            <div>
                To view the report, please select a condition.
            </div>
            <form @submit.prevent="form.post('/login')">
                <Select v-model="form.condition">
                    <SelectTrigger class="sm:w-full lg:w-1/3 md:w-1/3">
                        <SelectValue placeholder="Select a condition" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="condition in conditions" :key="condition.name" :value="condition.name">
                            <div class="py-2.5 cursor-pointer" v-html="condition.label"></div>
                        </SelectItem>
                    </SelectContent>
                </Select>
            </form>

            <div v-if="form.condition">
                <div v-if="isLoading" class="flex items-center justify-center pb-2.5">
                    <LoadingSpinner />
                </div>

                <div v-else>
                    <div v-if="itemData[form.condition] && itemData[form.condition].length > 0">
                        <!-- <pre>
                            {{ form.condition }}
                            {{ itemData }}
                        </pre> -->
                        <component :is="selectedComponent(form.condition)?.component"
                            v-bind="selectedComponent(form.condition)?.props"
                            :description="selectedComponent(form.condition)?.description || '-'"
                            :text-inside="selectedComponent(form.condition)?.textInside || '-'"
                            :patient-data="itemData[form.condition]"
                            indicator="threeMonthsDeath"
                            />
                    </div>

                    <!-- No Data Section -->
                   <div v-else class="flex flex-col items-center justify-center border rounded-lg bg-gray-50 h-full min-h-[300px]">
    <svg class="w-16 h-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m-6 3h6a2 2 0 002-2v-4a2 2 0 00-2-2h-6a2 2 0 00-2 2v4a2 2 0 002 2z" />
    </svg>
    <div class="text-center mt-4">
        <p class="text-lg font-semibold">No Data Found</p>
        <p>There was no data matching your query/request.</p>
    </div>
</div>
                </div>
            </div>

            <div v-if="!form.condition" class="border rounded-lg bg-gray-50 h-full flex items-center justify-center">
                <div>
                    <div class="flex items-center justify-center pb-2.5">
                        <TriangleAlert class="size-18" color="gray" />
                    </div>
                    <div class="text-center">
                        Nothing to show. Please select a field/Questionnaire Item to view insights on it.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>