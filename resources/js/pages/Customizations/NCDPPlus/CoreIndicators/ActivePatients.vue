<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import AllPatients from '@/components/packages/ncdpplus/tabs/active/AllPatients.vue';
import FemalePatients from '@/components/packages/ncdpplus/tabs/active/FemalePatients.vue';
import { computed } from 'vue';

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
    title: 'Active Patients',
    href: ''
  }
];


// Add type state
const patientType = ref<'all' | 'female' | 'facility' | 'Less than 18' | 'Less than 30'>('all')

const conditions = [
  { name: 'all', label: 'All Active Patients', tag: 'all' },
  { name: 'diabetes_1', label: 'Type 1 Diabetes', tag: 'diabetes' },
  { name: 'diabetes_2', label: 'Type 2 Diabetes', tag: 'diabetes' },
  { name: 'gestational_diabetes', label: 'Unspecified/Gestational Diabetes', tag: 'diabetes' },
  { name: 'rheumatic', label: 'Rheumatic Heart Disease', tag: 'cardiac' },
  { name: 'congenital', label: 'Congenital Heart Disease', tag: 'cardiac' },
  { name: 'heart_failure', label: 'Heart Failure', tag: 'cardiac' },
  { name: 'other_cardiac', label: 'Other/ Unspecified Cardiac Condition', tag: 'cardiac' },
  { name: 'hypertension', label: 'Hypertension', tag: 'hypertension' },
  { name: 'sickle_cell', label: 'Sickle Cell Anemia', tag: 'scd' },
  { name: 'chronic_respiratory', label: 'Chronic Respiratory Disease', tag: 'crd' },
  { name: 'chronic_kidney', label: 'Chronic Kidney Disease', tag: 'ckd' },
  { name: 'chronic_liver', label: 'Chronic Liver Disease', tag: 'cld' },
];



</script>

<template>

  <Head title="Customized Packaged Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <Heading title="Active Patients Indicators"
        description="Registered Active Patients per disease/condition aggregated per quarter." class="pt-5" />

      <Tabs v-model="patientType" class="w-full">
        <TabsList class="grid w-full grid-cols-2">
          <TabsTrigger value="all">All Patients</TabsTrigger>
          <!-- <TabsTrigger value="facility">By Facility</TabsTrigger> -->
          <TabsTrigger value="female">Female Patients</TabsTrigger>

        </TabsList>

        <!-- All Patients Tab -->
        <TabsContent value="all">
          <div class="space-y-4">
            <AllPatients :project="props.project" :conditions="conditions" indicator="active"/>
          </div>
        </TabsContent>

        <!-- Facility Tab -->
        <!-- <TabsContent value="facility">
          <div class="space-y-4">
            <div>View patients by facility:</div>

          </div>
        </TabsContent> -->

        <!-- Female Patients Tab -->
        <TabsContent value="female">
          <div class="space-y-4">
            <FemalePatients :project="props.project" :conditions="conditions" indicator="active" />
          </div>
        </TabsContent>


      </Tabs>

    </div>
  </AppLayout>
</template>