<!-- resources/js/Pages/Customizations/NCDPPlus/CoreIndicators/Components/Mortality/AllPatientsTab.vue -->
<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-900">All Mortality Patients</h3>
      <div class="flex space-x-4">
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Search patients..."
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
        <select
          v-model="diseaseFilter"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">All Diseases</option>
          <option v-for="disease in uniqueDiseases" :key="disease" :value="disease">
            {{ disease }}
          </option>
        </select>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disease</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mortality Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facility</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="patient in filteredPatients" :key="`${patient.record}-${patient.instance}`">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ patient.record }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ patient.disease_name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ formatDate(patient.mortality_date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ patient.age_demo || 'N/A' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ getGenderLabel(patient.gender_demo) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ patient.facility_demo || 'N/A' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button
                @click="viewPatientDetails(patient)"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                View
              </button>
              <button
                @click="viewDiseaseReport(patient.disease_type)"
                class="text-green-600 hover:text-green-900"
              >
                Disease Report
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="filteredPatients.length === 0" class="text-center py-8 text-gray-500">
      No patients found
    </div>

    <!-- Patient Details Modal -->
    <div v-if="selectedPatient" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Patient Details</h3>
            <button @click="selectedPatient = null" class="text-gray-500 hover:text-gray-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div v-for="(value, key) in selectedPatient" :key="key" class="border-b pb-2">
              <span class="font-medium text-gray-600 capitalize">{{ formatKey(key) }}:</span>
              <span class="ml-2 text-gray-900">{{ value }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

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

interface Props {
  patients: Patient[]
  loading: boolean
}

const props = defineProps<Props>()

const searchTerm = ref('')
const diseaseFilter = ref('')
const selectedPatient = ref<Patient | null>(null)

// Computed properties
const uniqueDiseases = computed(() => {
  return [...new Set(props.patients.map(p => p.disease_name))].sort()
})

const filteredPatients = computed(() => {
  let filtered = props.patients

  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase()
    filtered = filtered.filter(patient => 
      patient.record.toLowerCase().includes(term) ||
      patient.disease_name.toLowerCase().includes(term) ||
      patient.facility_demo?.toLowerCase().includes(term)
    )
  }

  if (diseaseFilter.value) {
    filtered = filtered.filter(patient => patient.disease_name === diseaseFilter.value)
  }

  return filtered
})

// Methods
const getGenderLabel = (genderCode: string) => {
  const genders: Record<string, string> = {
    '1': 'Male',
    '2': 'Female',
    '': 'Unknown'
  }
  return genders[genderCode] || genderCode
}

const formatDate = (dateString?: string) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

const formatKey = (key: string) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const viewPatientDetails = (patient: Patient) => {
  selectedPatient.value = patient
}

const viewDiseaseReport = (diseaseType: string) => {
  const diseaseRoutes: Record<string, string> = {
    'type1': '/ncdp-plus/mortality/type1-diabetes',
    'type2': '/ncdp-plus/mortality/type2-diabetes',
    'unspecified': '/ncdp-plus/mortality/unspecified-diabetes',
    'rheumatic': '/ncdp-plus/mortality/rheumatic',
    'congenital': '/ncdp-plus/mortality/congenital',
    'heart_failure': '/ncdp-plus/mortality/heart-failure',
    'other_cardiac': '/ncdp-plus/mortality/other-cardiac',
    'sickle_cell': '/ncdp-plus/mortality/sickle-cell',
    'chronic_respiratory': '/ncdp-plus/mortality/chronic-respiratory',
    'chronic_kidney': '/ncdp-plus/mortality/chronic-kidney',
    'chronic_liver': '/ncdp-plus/mortality/chronic-liver',
  }

  const route = diseaseRoutes[diseaseType]
  if (route) {
    router.visit(route)
  }
}
</script>