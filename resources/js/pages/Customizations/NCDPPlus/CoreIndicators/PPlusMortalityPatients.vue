<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { type BreadcrumbItem } from '@/types';
import OverviewTab from '@/components/packages/ncdpplus/tabs/mortality/OverviewTab.vue'
import AllPatientsTab from '@/components/packages/ncdpplus/tabs/mortality/AllPatientsTab.vue'
import DiseaseBreakdownTab from '@/components/packages/ncdpplus/tabs/mortality/DiseaseBreakdownTab.vue'
// import OverviewTab from './Components/Mortality/OverviewTab.vue'
// import AllPatientsTab from './Components/Mortality/AllPatientsTab.vue'
// import DiseaseBreakdownTab from './Components/Mortality/DiseaseBreakdownTab.vue'

interface Patient {
  record: string
  instance: string
  disease_name: string
  disease_type: string
  mortality_date?: string
  gender_demo?: string
  age_demo?: string
  facility_demo?: string
  [key: string]: any
}

interface Summary {
  total_patients: number
  by_gender: Record<string, number>
  by_facility: Record<string, number>
  by_disease: Record<string, number>
  average_age: number
}

interface QuarterlyData {
  quarter: string
  total: number
  by_disease: Record<string, number>
}

// Props - FIXED: Add quarterlyData and summary as props
const props = defineProps<{
  project: any
  patients?: any
  quarterlyData?: QuarterlyData[]
  summary?: Summary
}>()

// Reactive data
const activeTab = ref('overview')
const loading = ref(false)

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
    title: 'Mortality Patients',
    href: ''
  }
];

// Use props data or fallback to empty values
const summaryData = computed(() => props.summary || {
  total_patients: 0,
  by_gender: {},
  by_facility: {},
  by_disease: {},
  average_age: 0
})

const quarterlyData = computed(() => props.quarterlyData || [])

// Tabs configuration
const tabs = [
  { key: 'overview', name: 'Overview' },
  { key: 'all', name: 'All Patients' },
  { key: 'disease', name: 'By Disease' },
]

// Debug: log the props to see what's being received
console.log('Props received:', {
  patients: props.patients?.length,
  quarterlyData: props.quarterlyData,
  summary: props.summary
})
</script>
<template>
  <AppLayout :breadcrumbs="breadcrumbs">
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Mortality Patients Dashboard</h1>
        <p class="text-gray-600 mt-2">Comprehensive overview of mortality statistics across all disease types</p>
      </div>

      <!-- Navigation Tabs -->
      <div class="mb-6">
        <nav class="flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.key
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="bg-white rounded-lg shadow">
        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'">
          <OverviewTab 
            :summary="summary"
            :quarterlyData="quarterlyData"
            :loading="loading"
          />
        </div>

        <!-- All Patients Tab -->
        <div v-if="activeTab === 'all'">
          <AllPatientsTab 
            :patients="patients"
            :loading="loading"
          />
        </div>

        <!-- By Disease Tab -->
        <div v-if="activeTab === 'disease'">
          <DiseaseBreakdownTab 
            :summary="summary"
            :loading="loading"
          />
        </div>
      </div>
    </div>
  </div>
  </AppLayout>
</template>

