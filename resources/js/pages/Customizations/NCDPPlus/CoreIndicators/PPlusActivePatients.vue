<script setup lang="ts">
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';

const props = defineProps<{
  project: IProject
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
    href: ''
  }
];

const conditions = [
  { 
    id: 'all', 
    name: 'All Active Patients', 
    icon: '👥',
    description: 'View all active patients across all conditions',
    tag: 'All Conditions',
    gradient: 'from-blue-500 to-indigo-500'
  },
  { 
    id: 'diabetes_1', 
    name: 'Type 1 Diabetes', 
    icon: '🩺',
    description: 'Patients with Type 1 Diabetes mellitus',
    tag: 'Diabetes',
    gradient: 'from-green-500 to-emerald-500'
  },
  { 
    id: 'diabetes_2', 
    name: 'Type 2 Diabetes', 
    icon: '🩺',
    description: 'Patients with Type 2 Diabetes mellitus',
    tag: 'Diabetes',
    gradient: 'from-green-500 to-emerald-500'
  },
  { 
    id: 'epilepsy', 
    name: 'Epilepsy', 
    icon: '🧠',
    description: 'Patients with epilepsy and seizure disorders',
    tag: 'Neurological',
    gradient: 'from-purple-500 to-violet-500'
  },
  { 
    id: 'gestational_diabetes', 
    name: 'Unspecified/Gestational Diabetes', 
    icon: '🤰',
    description: 'Patients with gestational or unspecified diabetes',
    tag: 'Diabetes',
    gradient: 'from-pink-500 to-rose-500'
  },
  { 
    id: 'rheumatic', 
    name: 'Rheumatic Heart Disease', 
    icon: '❤️',
    description: 'Patients with rheumatic heart disease',
    tag: 'Cardiac',
    gradient: 'from-red-500 to-orange-500'
  },
  { 
    id: 'congenital', 
    name: 'Congenital Heart Disease', 
    icon: '❤️',
    description: 'Patients with congenital heart defects',
    tag: 'Cardiac',
    gradient: 'from-red-500 to-orange-500'
  },
  { 
    id: 'heart_failure', 
    name: 'Heart Failure', 
    icon: '❤️',
    description: 'Patients diagnosed with heart failure',
    tag: 'Cardiac',
    gradient: 'from-red-500 to-orange-500'
  },
  { 
    id: 'other_cardiac', 
    name: 'Other/Unspecified Cardiac Condition', 
    icon: '❤️',
    description: 'Patients with other cardiac conditions',
    tag: 'Cardiac',
    gradient: 'from-red-500 to-orange-500'
  },
  { 
    id: 'hypertension', 
    name: 'Hypertension', 
    icon: '🩸',
    description: 'Patients with hypertension',
    tag: 'Hypertension',
    gradient: 'from-amber-500 to-yellow-500'
  },
  { 
    id: 'sickle_cell', 
    name: 'Sickle Cell Anemia', 
    icon: '🔴',
    description: 'Patients with sickle cell disease',
    tag: 'Hematological',
    gradient: 'from-rose-500 to-pink-500'
  },
  { 
    id: 'chronic_respiratory', 
    name: 'Chronic Respiratory Disease', 
    icon: '🫁',
    description: 'Patients with chronic respiratory conditions',
    tag: 'Respiratory',
    gradient: 'from-teal-500 to-cyan-500'
  },
  { 
    id: 'chronic_kidney', 
    name: 'Chronic Kidney Disease', 
    icon: '🥄',
    description: 'Patients with chronic kidney disease',
    tag: 'Renal',
    gradient: 'from-indigo-500 to-blue-500'
  },
  { 
    id: 'chronic_liver', 
    name: 'Chronic Liver Disease', 
    icon: '🟤',
    description: 'Patients with chronic liver disease',
    tag: 'Hepatic',
    gradient: 'from-orange-500 to-amber-500'
  },
];

const handleConditionClick = (conditionId: string) => {
  router.get(`/packages/project/${props.project.project_id}/core_indicators/nppactivepatients/${conditionId}`);
};
</script>

<template>
  <Head :title="`${project.app_title} - Active Patients`" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-slate-900 dark:to-slate-800 p-6">
      <!-- Header with Project Title -->
      <div class="max-w-7xl mx-auto mb-8">
        <div class="inline-flex items-center px-4 py-2 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-medium mb-4">
          Core Indicators
        </div>
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <div>
            <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white leading-tight">
              Active Patients Dashboard
              <span class="block text-xl text-slate-600 dark:text-slate-300 font-normal mt-2">
                {{ project.app_title }}
              </span>
            </h1>
            <p class="text-lg text-slate-600 dark:text-slate-300 mt-3 max-w-3xl">
              Select a condition to view detailed analytics and patient statistics. 
              Data is aggregated quarterly and updated daily.
            </p>
          </div>
          
          <div class="flex items-center space-x-2">
            <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <span class="font-semibold text-slate-900 dark:text-white">Analytics Hub</span>
          </div>
        </div>
      </div>

      <!-- Conditions Grid -->
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div 
            v-for="condition in conditions" 
            :key="condition.id"
            @click="handleConditionClick(condition.id)"
            class="group cursor-pointer transition-all duration-300 hover:scale-[1.02] hover:shadow-xl"
          >
            <div class="h-full bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 flex flex-col transition-all duration-300 group-hover:border-red-200 dark:group-hover:border-red-800/50">
              <!-- Icon and Gradient Background -->
              <div class="relative mb-6">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl" 
                     :class="`bg-gradient-to-r ${condition.gradient}`">
                  {{ condition.icon }}
                </div>
                <div class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-white dark:bg-slate-700 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-4 h-4 text-slate-400 group-hover:text-red-500 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                  </svg>
                </div>
              </div>
              
              <!-- Condition Name -->
              <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2 group-hover:text-red-500 dark:group-hover:text-red-400 transition-colors duration-300">
                {{ condition.name }}
              </h3>
              
              <!-- Description -->
              <p class="text-slate-600 dark:text-slate-300 mb-4 flex-grow">
                {{ condition.description }}
              </p>
              
              <!-- Tag -->
              <div class="flex justify-between items-center mt-auto">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                      :class="`bg-gradient-to-r ${condition.gradient} text-white`">
                  {{ condition.tag }}
                </span>
                <span class="text-xs text-slate-400 group-hover:text-red-500 transition-colors duration-300">
                  View Details →
                </span>
              </div>
              
              <!-- Hover Indicator -->
              <div class="absolute bottom-0 left-0 w-0 h-1 rounded-b-2xl transition-all duration-300 group-hover:w-full" 
                   :class="`bg-gradient-to-r ${condition.gradient}`">
              </div>
            </div>
          </div>
        </div>

        <!-- Info Panel -->
        <div class="mt-12 bg-gradient-to-r from-red-50 to-orange-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl p-8 shadow-lg border border-red-100 dark:border-slate-700">
          <div class="flex flex-col lg:flex-row items-start lg:items-center gap-6">
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-orange-500 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="flex-grow">
              <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">About Active Patients Data</h3>
              <p class="text-slate-600 dark:text-slate-300">
                Active patients are defined as patients who have had at least one visit in the last 12 months. 
                This dashboard provides quarterly aggregated data for trend analysis and monitoring. 
                Each condition includes detailed analytics including demographic breakdowns, treatment adherence, and outcome metrics.
              </p>
            </div>
            <div class="flex-shrink-0">
              <div class="bg-white dark:bg-slate-700 rounded-xl p-4 border border-slate-200 dark:border-slate-600">
                <div class="text-sm text-slate-500 dark:text-slate-400">Data Updated</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white">Daily</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Section -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-6A8.5 8.5 0 0012 3v10m8.5-5.5a8.5 8.5 0 01-17 0"></path>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">14</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Conditions Tracked</div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">Quarterly</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Aggregation Period</div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">Real-time</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Data Updates</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Note -->
        <div class="mt-12 text-center text-slate-500 dark:text-slate-400 text-sm">
          <p>© {{ new Date().getFullYear() }} Redcap Stream • Advanced analytics for health research</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>