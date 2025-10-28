<!-- resources/js/Pages/Customizations/NCDPPlus/CoreIndicators/Components/Mortality/OverviewTab.vue -->
<template>
  <div class="p-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="flex items-center">
          <div class="p-3 bg-red-100 rounded-lg">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Mortality</p>
            <p class="text-2xl font-bold text-gray-900">{{ summary?.total_patients || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Average Age</p>
            <p class="text-2xl font-bold text-gray-900">{{ Math.round(summary?.average_age || 0) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Facilities</p>
            <p class="text-2xl font-bold text-gray-900">{{ Object.keys(summary?.by_facility || {}).length }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="flex items-center">
          <div class="p-3 bg-purple-100 rounded-lg">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Disease Types</p>
            <p class="text-2xl font-bold text-gray-900">{{ Object.keys(summary?.by_disease || {}).length }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quarterly Breakdown -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Quarterly Mortality Breakdown</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quarter</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Deaths</th>
              <th 
                v-for="disease in Object.keys(quarterlyData[0]?.by_disease || {})" 
                :key="disease"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                {{ disease }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="quarter in quarterlyData" :key="quarter.quarter">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ quarter.quarter }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ quarter.total }}
              </td>
              <td 
                v-for="disease in Object.keys(quarterlyData[0]?.by_disease || {})" 
                :key="disease"
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
              >
                {{ quarter.by_disease[disease] || 0 }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="quarterlyData.length === 0" class="text-center py-8 text-gray-500">
        No quarterly data available
      </div>
    </div>

    <!-- Gender & Facility Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Gender Distribution -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Gender Distribution</h3>
        <div class="space-y-3">
          <div 
            v-for="(count, gender) in summary?.by_gender || {}" 
            :key="gender"
            class="flex items-center justify-between"
          >
            <span class="text-sm text-gray-600 capitalize">{{ getGenderLabel(gender) }}</span>
            <span class="text-sm font-medium text-gray-900">{{ count }}</span>
          </div>
        </div>
      </div>

      <!-- Facility Distribution -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Facility Distribution</h3>
        <div class="space-y-3 max-h-60 overflow-y-auto">
          <div 
            v-for="(count, facility) in summary?.by_facility || {}" 
            :key="facility"
            class="flex items-center justify-between"
          >
            <span class="text-sm text-gray-600">{{ facility }}</span>
            <span class="text-sm font-medium text-gray-900">{{ count }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  summary: any
  quarterlyData: any[]
  loading: boolean
}

defineProps<Props>()

const getGenderLabel = (genderCode: string) => {
  const genders: Record<string, string> = {
    '1': 'Male',
    '2': 'Female',
    '': 'Unknown'
  }
  return genders[genderCode] || genderCode
}
</script>