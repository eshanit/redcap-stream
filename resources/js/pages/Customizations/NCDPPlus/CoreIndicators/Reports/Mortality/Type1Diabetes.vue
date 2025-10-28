<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import LTFUType1PatientsChart from '@/components/charts/apexcharts/ncdpplus/Diabetes/TypeI/PPlusChart.vue';
import { computed } from 'vue';

const props = defineProps<{
  project: IProject
  patients: any
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
    title: 'Mortality Patients',
    href: '/packages/project/' + props.project.project_id + '/core_indicators/nppmortalitypatients',
  },
  {
    title: 'Type 1 Diabetes',
    href: ''
  }
];

const yearlyQuarters = ['Q1', 'Q2', 'Q3', 'Q4'];

// Handle condition click
const handleConditionClick = (condition: any) => {
  // Navigate to the condition-specific page
  router.get(`/packages/project/${props.project.project_id}/core_indicators/ltfu_patients/${condition.name}`);
};
</script>

<template>
  <Head title="Customized Packaged Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <Heading title="Mortality Patients Indicators"
        description="Registered Mortality Patients per disease/condition aggregated per quarter. Type 1 Diabetes"
        class="pt-5" />
      <LTFUType1PatientsChart :active-patients-data="patients" />
    </div>
  </AppLayout>
</template>