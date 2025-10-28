<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import AllActivePatientsChart from '@/components/charts/apexcharts/ncdpplus/All/PPlusChart.vue';
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
    title: 'Active Patients',
    href: '/packages/project/' + props.project.project_id + '/core_indicators/nppactivepatients',
  },
  {
    title: 'All Active Patients',
    href: ''
  }
];


</script>

<template>
  <Head title="All Active Patients" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <Heading 
        title="All Active Patients" 
        description="Comprehensive overview of all active patients across all disease conditions aggregated per quarter and facility."
        class="pt-5" 
      />
      <AllActivePatientsChart :patients-data="patients" condition="Active" />
    </div>
  </AppLayout>
</template>