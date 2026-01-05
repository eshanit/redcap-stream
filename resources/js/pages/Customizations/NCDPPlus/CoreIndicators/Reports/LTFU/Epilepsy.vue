<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import EpilepsyPPLusChart from '@/components/charts/apexcharts/ncdpplus/Epilepsy/PPlusChart.vue';
import { computed } from 'vue';

const props = defineProps<{
  project: IProject
  patients: any
}>();

const packageRoot = computed(() => {
  return props.project.project_id == 39 ? 'ncd_pplus' : 'ncd';
});

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
    title: 'LTFU Patients',
    href: '/packages/project/' + props.project.project_id + '/core_indicators/nppltfupatients',
  },
  {
    title: 'Epilepsy',
    href: ''
  }
];

const handleBackClick = () => {
  router.get(`/packages/project/${props.project.project_id}/core_indicators/nppltfupatients`);
};
</script>

<template>
  <Head :title="`${project.app_title} - Epilepsy LTFU Patients`" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-slate-900 dark:to-slate-800 p-6">
      <div class="max-w-7xl mx-auto">
        <!-- Header with Back Button -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
          <div>
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-sm font-medium mb-4">
              Neurological Condition
            </div>
            
            <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white leading-tight">
              Epilepsy Lost To Follow Up Patients
              <span class="block text-xl text-slate-600 dark:text-slate-300 font-normal mt-2">
                {{ project.app_title }}
              </span>
            </h1>
            <p class="text-lg text-slate-600 dark:text-slate-300 mt-3 max-w-3xl">
              Comprehensive overview of Epilepsy LTFU patients aggregated per quarter and facility.
            </p>
          </div>
          
          <div class="flex items-center space-x-4">
            <button 
              @click="handleBackClick"
              class="px-6 py-2.5 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
              </svg>
              Back to Conditions
            </button>
            
            <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-xl flex items-center justify-center">
                <span class="text-2xl">🧠</span>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                  {{ patients?.total || '0' }}
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Epilepsy LTFU</div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                  {{ patients?.facilities?.length || '0' }}
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Facilities</div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">Q{{ patients?.quarter || '4' }}</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Current Quarter</div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-yellow-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">Epilepsy</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Neurological Condition</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart Container -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-2xl border border-slate-200 dark:border-slate-700 mb-8">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
            <div>
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Epilepsy LTFU Patient Analysis</h2>
              <p class="text-slate-600 dark:text-slate-300 mt-1">
                Quarterly distribution of Epilepsy LTFU patients across all facilities
              </p>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                <span class="w-3 h-3 bg-indigo-500 rounded-full"></span>
                <span class="text-sm text-slate-700 dark:text-slate-300">Epilepsy</span>
              </div>
              <div class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                <span class="w-3 h-3 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full"></span>
                <span class="text-sm text-slate-700 dark:text-slate-300">Quarterly Trend</span>
              </div>
            </div>
          </div>
          
          <EpilepsyPPLusChart :active-patients-data="patients" />
        </div>

        <!-- Data Info Panel -->
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl p-8 shadow-lg border border-indigo-100 dark:border-slate-700">
          <div class="flex flex-col lg:flex-row items-start lg:items-center gap-6">
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
              </div>
            </div>
            <div class="flex-grow">
              <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Epilepsy LTFU Information</h3>
              <p class="text-slate-600 dark:text-slate-300">
                This chart shows the distribution of Epilepsy patients lost to follow-up across different facilities for the current quarter. 
                Epilepsy is a neurological disorder characterized by recurrent seizures that require consistent medication management. 
                LTFU in epilepsy patients can lead to uncontrolled seizures, increased risk of injury, status epilepticus, and reduced quality of life. 
                Regular follow-up is essential for medication adjustment, seizure control monitoring, and managing potential side effects of antiepileptic drugs.
              </p>
            </div>
            <div class="flex-shrink-0">
              <div class="bg-white dark:bg-slate-700 rounded-xl p-4 border border-slate-200 dark:border-slate-600">
                <div class="text-sm text-slate-500 dark:text-slate-400">Retention Priority</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white">Critical</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-slate-500 dark:text-slate-400 text-sm">
          <p>© {{ new Date().getFullYear() }} Redcap Stream • Epilepsy LTFU Patients Dashboard • {{ project.app_title }}</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>