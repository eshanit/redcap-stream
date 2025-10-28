<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import CRDPPlusChart from '@/components/charts/apexcharts/ncdpplus/Respiratory/PPlusChart.vue';
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
    title: 'Chronic Respiratory Disease',
    href: ''
  }
];

</script>

<template>
  <Head title="Customized Packaged Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <Heading title="Mortality Patients Indicators"
        description="Registered Mortality Patients per disease/condition aggregated per quarter. Chronic Respiratory Disease"
        class="pt-5" />
      <CRDPPlusChart :active-patients-data="patients" />
    </div>
  </AppLayout>
</template>