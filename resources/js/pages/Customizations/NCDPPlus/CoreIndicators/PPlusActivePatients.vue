<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import AllPatients from '@/components/packages/ncdpplus/tabs/active/AllPatients.vue';
import FemalePatients from '@/components/packages/ncdpplus/tabs/active/FemalePatients.vue';
import { computed } from 'vue';

const props = defineProps<{
  project: IProject
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

// Condition icons mapping for visual appeal
const conditionIcons = {
  all: '👥',
  diabetes: '🩺',
  epilepsy: '🧠',
  cardiac: '❤️',
  hypertension: '🩸',
  scd: '🔴',
  crd: '🫁',
  ckd: '🥄',
  cld: '🟤'
};

const conditions = [
  { name: 'all', label: 'All Active Patients', tag: 'all' },
  { name: 'diabetes_1', label: 'Type 1 Diabetes', tag: 'diabetes' },
  { name: 'diabetes_2', label: 'Type 2 Diabetes', tag: 'diabetes' },
  { name: 'epilepsy', label: 'Epilepsy', tag: 'epilepsy' },
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

// Tag colors for consistent styling
const tagColors = {
  all: 'bg-blue-100 text-blue-800 border-blue-200',
  diabetes: 'bg-green-100 text-green-800 border-green-200',
  epilepsy: 'bg-purple-100 text-purple-800 border-purple-200',
  cardiac: 'bg-red-100 text-red-800 border-red-200',
  hypertension: 'bg-orange-100 text-orange-800 border-orange-200',
  scd: 'bg-pink-100 text-pink-800 border-pink-200',
  crd: 'bg-teal-100 text-teal-800 border-teal-200',
  ckd: 'bg-indigo-100 text-indigo-800 border-indigo-200',
  cld: 'bg-amber-100 text-amber-800 border-amber-200'
};

// Handle condition click
const handleConditionClick = (condition: any) => {
  // Navigate to the condition-specific page
  router.get(`/packages/project/${props.project.project_id}/core_indicators/nppactivepatients/${condition.name}`);
};
</script>

<template>
  <Head title="Customized Packaged Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
      <Heading title="Active Patients Indicators"
        description="Registered Active Patients per disease/condition aggregated per quarter. Select a condition to view detailed analytics."
        class="pt-5" />

      <!-- Conditions Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mt-6">
        <div 
          v-for="condition in conditions" 
          :key="condition.name"
          @click="handleConditionClick(condition)"
          class="group cursor-pointer transition-all duration-300 hover:scale-105 hover:shadow-lg"
        >
          <div class="bg-white rounded-xl border-2 border-gray-100 p-5 h-full flex flex-col transition-all duration-300 group-hover:border-blue-200 group-hover:shadow-md">
            <!-- Icon and Title -->
            <div class="flex items-start space-x-3 mb-3">
              <div class="text-2xl flex-shrink-0 mt-1">
                {{ conditionIcons[condition.tag] }}
              </div>
              <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                {{ condition.label }}
              </h3>
            </div>
            
            <!-- Tag -->
            <div class="mt-auto">
              <span 
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                :class="tagColors[condition.tag]"
              >
                {{ condition.tag.replace('_', ' ').toUpperCase() }}
              </span>
            </div>
            
            <!-- Hover Indicator -->
            <div class="absolute bottom-0 left-0 w-0 h-1 bg-blue-500 transition-all duration-300 group-hover:w-full rounded-b-xl"></div>
          </div>
        </div>
      </div>

      <!-- Quick Stats Section (Optional) -->
      <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
        <div class="flex items-center space-x-3">
          <div class="bg-blue-100 p-2 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800">Condition Analytics</h3>
            <p class="text-sm text-gray-600">Click on any condition card to view detailed patient analytics, trends, and insights for that specific condition.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>