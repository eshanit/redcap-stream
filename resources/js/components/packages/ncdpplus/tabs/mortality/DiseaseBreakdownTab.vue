<!-- resources/js/Pages/Customizations/NCDPPlus/CoreIndicators/Components/Mortality/DiseaseBreakdownTab.vue -->
<template>
  <div class="p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Mortality by Disease Type</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="(count, disease) in summary?.by_disease || {}"
        :key="disease"
        class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
        @click="viewDiseaseReport(disease)"
      >
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ disease }}</h4>
            <p class="text-3xl font-bold text-red-600">{{ count }}</p>
            <p class="text-sm text-gray-600 mt-1">patients</p>
          </div>
          <div class="p-3 bg-red-100 rounded-lg">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200">
          <div class="flex justify-between text-sm text-gray-600">
            <span>Percentage:</span>
            <span class="font-medium">
              {{ ((count / (summary?.total_patients || 1)) * 100).toFixed(1) }}%
            </span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="Object.keys(summary?.by_disease || {}).length === 0" class="text-center py-12 text-gray-500">
      No disease data available
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'

interface Props {
  summary: any
  loading: boolean
}

defineProps<Props>()

const viewDiseaseReport = (diseaseName: string) => {
  const diseaseRoutes: Record<string, string> = {
    'Type I Diabetes': '/ncdp-plus/mortality/type1-diabetes',
    'Type II Diabetes': '/ncdp-plus/mortality/type2-diabetes',
    'Unspecified Diabetes': '/ncdp-plus/mortality/unspecified-diabetes',
    'Rheumatic Heart Disease': '/ncdp-plus/mortality/rheumatic',
    'Congenital Heart Disease': '/ncdp-plus/mortality/congenital',
    'Heart Failure': '/ncdp-plus/mortality/heart-failure',
    'Other Cardiac Diseases': '/ncdp-plus/mortality/other-cardiac',
    'Sickle Cell Disease': '/ncdp-plus/mortality/sickle-cell',
    'Chronic Respiratory Disease': '/ncdp-plus/mortality/chronic-respiratory',
    'Chronic Kidney Disease': '/ncdp-plus/mortality/chronic-kidney',
    'Chronic Liver Disease': '/ncdp-plus/mortality/chronic-liver',
  }

  const route = diseaseRoutes[diseaseName]
  if (route) {
    router.visit(route)
  }
}
</script>