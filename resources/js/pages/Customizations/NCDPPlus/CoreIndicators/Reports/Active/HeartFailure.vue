<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import HeartFailurePPlusChart from '@/components/charts/apexcharts/ncdpplus/Heart/Failure/PPlusChart.vue';
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
    title: 'Active Patients',
    href: '/packages/project/' + props.project.project_id + '/core_indicators/nppactivepatients',
  },
  {
    title: 'Heart Failure',
    href: ''
  }
];

const handleBackClick = () => {
  router.get(`/packages/project/${props.project.project_id}/core_indicators/nppactivepatients`);
};

const yearlyQuarters = ['Q1', 'Q2', 'Q3', 'Q4'];
</script>

<template>
  <Head :title="`${project.app_title} - Heart Failure Patients`" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-slate-900 dark:to-slate-800 p-6">
      <div class="max-w-7xl mx-auto">
        <!-- Header with Back Button and Condition Info -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
          <div>
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-medium mb-4">
              Cardiac Condition
            </div>
            
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 bg-gradient-to-r from-red-600 to-pink-600 rounded-2xl flex items-center justify-center text-3xl">
                ❤️
              </div>
              <div>
                <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white leading-tight">
                  Heart Failure Patients
                  <span class="block text-xl text-slate-600 dark:text-slate-300 font-normal mt-2">
                    {{ project.app_title }}
                  </span>
                </h1>
                <p class="text-lg text-slate-600 dark:text-slate-300 mt-3 max-w-3xl">
                  Registered active Heart Failure patients aggregated per quarter and facility.
                </p>
              </div>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <button 
              @click="handleBackClick"
              class="px-6 py-2.5 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
              </svg>
              All Conditions
            </button>
            
            <div class="w-10 h-10 bg-gradient-to-r from-red-600 to-pink-600 rounded-xl flex items-center justify-center">
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
              <div class="w-12 h-12 bg-gradient-to-r from-red-600 to-pink-600 rounded-xl flex items-center justify-center">
                <span class="text-2xl">❤️</span>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                  {{ patients?.total || '0' }}
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Heart Failure Patients</div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                  {{ patients?.facilities?.length || '0' }}
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Active Facilities</div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-violet-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                  <span v-if="patients?.quarter">Q{{ patients.quarter }}</span>
                  <span v-else>Q4</span>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Current Quarter</div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-amber-500 to-yellow-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ yearlyQuarters.length }}</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Quarters Tracked</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart Container -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-2xl border border-slate-200 dark:border-slate-700 mb-8">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
            <div>
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Heart Failure Analysis</h2>
              <p class="text-slate-600 dark:text-slate-300 mt-1">
                Quarterly distribution of active Heart Failure patients across facilities
              </p>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                <span class="w-3 h-3 bg-gradient-to-r from-red-600 to-pink-600 rounded-full"></span>
                <span class="text-sm text-slate-700 dark:text-slate-300">Heart Failure Patients</span>
              </div>
              <div class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                <span class="w-3 h-3 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"></span>
                <span class="text-sm text-slate-700 dark:text-slate-300">Quarterly Trend</span>
              </div>
            </div>
          </div>
          
          <HeartFailurePPlusChart :active-patients-data="patients" />
          
          <!-- Quarter Legend -->
          <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-600">
            <div class="flex flex-wrap gap-3">
              <div v-for="quarter in yearlyQuarters" :key="quarter"
                   class="flex items-center gap-2 px-3 py-2 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ quarter }}</span>
                <span class="text-xs text-slate-500">2024</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Condition Information Panel -->
        <div class="bg-gradient-to-r from-red-50 to-pink-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl p-8 shadow-lg border border-red-100 dark:border-slate-700 mb-8">
          <div class="flex flex-col lg:flex-row items-start lg:items-center gap-6">
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-r from-red-600 to-pink-600 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
              </div>
            </div>
            <div class="flex-grow">
              <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">About Heart Failure</h3>
              <p class="text-slate-600 dark:text-slate-300 mb-4">
                Heart Failure occurs when the heart muscle doesn't pump blood as well as it should. 
                This dashboard tracks active heart failure patients who have had at least one clinical encounter 
                in the last 12 months, with data aggregated quarterly by facility.
              </p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                  <span class="text-sm text-slate-600 dark:text-slate-300">Chronic condition where the heart doesn't pump effectively</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                  <span class="text-sm text-slate-600 dark:text-slate-300">Can result from various underlying heart conditions</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                  <span class="text-sm text-slate-600 dark:text-slate-300">Managed with medication, lifestyle changes, and monitoring</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                  <span class="text-sm text-slate-600 dark:text-slate-300">Requires regular follow-up and symptom management</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Cardiac Conditions -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-lg border border-slate-200 dark:border-slate-700">
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Related Cardiac Conditions</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div 
              @click="router.get(`/packages/project/${project.project_id}/core_indicators/nppactivepatients/rheumatic`)"
              class="group cursor-pointer bg-gradient-to-r from-red-50 to-orange-50 dark:from-slate-800 dark:to-slate-900 rounded-xl p-4 border border-red-100 dark:border-slate-600 transition-all duration-300 hover:scale-[1.02] hover:shadow-md"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                  <span class="text-xl">❤️</span>
                </div>
                <div>
                  <h4 class="font-semibold text-slate-900 dark:text-white group-hover:text-red-600 transition-colors">
                    Rheumatic Heart Disease
                  </h4>
                  <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">View patient analytics</p>
                </div>
              </div>
            </div>
            
            <div 
              @click="router.get(`/packages/project/${project.project_id}/core_indicators/nppactivepatients/congenital`)"
              class="group cursor-pointer bg-gradient-to-r from-rose-50 to-pink-50 dark:from-slate-800 dark:to-slate-900 rounded-xl p-4 border border-rose-100 dark:border-slate-600 transition-all duration-300 hover:scale-[1.02] hover:shadow-md"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-r from-rose-500 to-pink-500 rounded-lg flex items-center justify-center">
                  <span class="text-xl">❤️</span>
                </div>
                <div>
                  <h4 class="font-semibold text-slate-900 dark:text-white group-hover:text-rose-600 transition-colors">
                    Congenital Heart Disease
                  </h4>
                  <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">View patient analytics</p>
                </div>
              </div>
            </div>
            
            <div 
              @click="handleBackClick"
              class="group cursor-pointer bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 rounded-xl p-4 border border-blue-100 dark:border-slate-600 transition-all duration-300 hover:scale-[1.02] hover:shadow-md"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center">
                  <span class="text-xl">👥</span>
                </div>
                <div>
                  <h4 class="font-semibold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">
                    All Conditions
                  </h4>
                  <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Return to overview</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-slate-500 dark:text-slate-400 text-sm">
          <p>© {{ new Date().getFullYear() }} Redcap Stream • Heart Failure Dashboard • {{ project.app_title }}</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>