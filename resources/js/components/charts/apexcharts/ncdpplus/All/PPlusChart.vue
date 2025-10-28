<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

interface ActivePatient {
  record: string;
  instance: string;
  gender_demo: string | null;
  facility_demo: string | null;
  age_demo: string | null;
  disease_type: string;
  disease_name: string;
  // Various visit date fields from different diseases
  db_visit_date?: string | null;
  dateov_visit?: string | null;
  scd_visit_date?: string | null;
  res_visit_date?: string | null;
  ckd_visit_date?: string | null;
  liver_date?: string | null;
  // Various next appointment fields
  next_appo_date?: string | null;
  card_next_appo_date?: string | null;
  scd_appo_date?: string | null;
  nxt_appo_date?: string | null;
  meds_next_appo_date?: string | null;
  liver_next_appo_date?: string | null;
}

const props = defineProps<{
  patientsData: ActivePatient[] | any;
  condition: string;
}>();

// Create a computed property to ensure we always have an array
const patientsData = computed(() => {
  if (Array.isArray(props.patientsData)) {
    return props.patientsData;
  } else if (props.patientsData && typeof props.patientsData === 'object') {
    return Object.values(props.patientsData);
  } else {
    return [];
  }
});

// Chart options for facility/quarter breakdown
const facilityChartOptions = ref({
  chart: {
    type: 'bar',
    height: 350,
    toolbar: {
      show: true,
    },
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '55%',
      endingShape: 'rounded',
    },
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    show: true,
    width: 2,
    colors: ['transparent'],
  },
  xaxis: {
    title: {
      text: 'Quarter',
    },
    categories: [] as string[],
  },
  yaxis: {
    title: {
      text: `Number of ${props.condition} Patients`,
    },
  },
  fill: {
    opacity: 1,
  },
  tooltip: {
    y: {
      formatter: (val: number) => `${val} active patients`,
    },
  },
  title: {
    text: `All ${props.condition} Patients by Quarter and Facility`,
    align: 'center',
    style: {
      fontSize: '16px',
    },
  },
  legend: {
    position: 'top',
  },
});

// Chart options for disease breakdown
const diseaseChartOptions = ref({
  chart: {
    type: 'bar',
    height: 350,
    toolbar: {
      show: true,
    },
  },
  plotOptions: {
    bar: {
      horizontal: true,
    },
  },
  dataLabels: {
    enabled: true,
  },
  xaxis: {
    title: {
      text: 'Number of Patients',
    },
    categories: [] as string[],
  },
  yaxis: {
    title: {
      text: 'Disease Type',
    },
  },
  title: {
    text: `${props.condition} Patients by Disease Type`,
    align: 'center',
    style: {
      fontSize: '16px',
    },
  },
});

const facilitySeries = ref<{ name: string; data: number[] }[]>([]);
const diseaseSeries = ref<{ name: string; data: number[] }[]>([]);

const getQuarter = (dateStr: string | null): number | null => {
  if (!dateStr) return null;
  
  try {
    const date = new Date(dateStr);
    const month = date.getMonth() + 1;
    return Math.ceil(month / 3);
  } catch {
    return null;
  }
};

// Helper function to get visit date from any disease type
const getVisitDate = (patient: ActivePatient): string | null => {
  const visitFields = [
    'db_visit_date', 
    'dateov_visit', 
    'scd_visit_date', 
    'res_visit_date', 
    'ckd_visit_date', 
    'liver_date'
  ];
  
  for (const field of visitFields) {
    if (patient[field as keyof ActivePatient]) {
      return patient[field as keyof ActivePatient] as string;
    }
  }
  return null;
};

const processFacilityData = () => {
  if (!patientsData.value.length) {
    return { categories: [], series: [] };
  }

  // Get all unique facilities
  const facilities = Array.from(new Set(
    patientsData.value
      .map((item: ActivePatient) => item.facility_demo)
      .filter(Boolean)
  )).sort() as string[];

  // Get all unique quarters from visit dates
  const quarterMap: Record<string, { year: number; quarter: number }> = {};
  
  patientsData.value.forEach((item: ActivePatient) => {
    const visitDate = getVisitDate(item);
    if (visitDate) {
      try {
        const date = new Date(visitDate);
        const year = date.getFullYear();
        const quarter = getQuarter(visitDate);
        
        if (quarter) {
          const key = `Q${quarter} ${year}`;
          quarterMap[key] = { year, quarter };
        }
      } catch (e) {
        console.warn('Invalid date format', visitDate);
      }
    }
  });

  // Sort quarters chronologically
  const sortedQuarters = Object.keys(quarterMap).sort((a, b) => {
    const quarterA = quarterMap[a];
    const quarterB = quarterMap[b];
    
    return quarterA.year !== quarterB.year 
      ? quarterA.year - quarterB.year 
      : quarterA.quarter - quarterB.quarter;
  });

  // Initialize data structure for each facility
  const facilityData: Record<string, Record<string, number>> = {};
  facilities.forEach(facility => {
    facilityData[facility] = {};
    sortedQuarters.forEach(quarter => {
      facilityData[facility][quarter] = 0;
    });
  });

  // Count active patients by facility and quarter
  const quarterlyPatients: Record<string, Set<string>> = {};
  
  // Initialize sets for each quarter
  sortedQuarters.forEach(quarter => {
    quarterlyPatients[quarter] = new Set();
  });

  // Group patients by quarter and facility, ensuring we count each patient only once per quarter
  patientsData.value.forEach((patient: ActivePatient) => {
    const visitDate = getVisitDate(patient);
    if (visitDate && patient.facility_demo) {
      try {
        const date = new Date(visitDate);
        const year = date.getFullYear();
        const quarter = getQuarter(visitDate);
        
        if (quarter) {
          const quarterKey = `Q${quarter} ${year}`;
          const patientKey = `${patient.record}`; // Unique patient identifier
          
          // Only count this patient once per quarter in this facility
          if (!quarterlyPatients[quarterKey].has(patientKey)) {
            quarterlyPatients[quarterKey].add(patientKey);
            facilityData[patient.facility_demo][quarterKey]++;
          }
        }
      } catch (e) {
        console.warn('Invalid date format', visitDate);
      }
    }
  });

  // Prepare series data for chart
  const seriesData = facilities.map(facility => ({
    name: facility,
    data: sortedQuarters.map(quarter => facilityData[facility][quarter])
  }));

  return {
    categories: sortedQuarters,
    series: seriesData,
    facilities,
    facilityData
  };
};

const processDiseaseData = () => {
  if (!patientsData.value.length) {
    return { categories: [], series: [] };
  }

  // Count patients by disease type
  const diseaseCounts: Record<string, number> = {};
  
  patientsData.value.forEach((patient: ActivePatient) => {
    if (patient.disease_name) {
      diseaseCounts[patient.disease_name] = (diseaseCounts[patient.disease_name] || 0) + 1;
    }
  });

  // Sort diseases by count (descending)
  const sortedDiseases = Object.entries(diseaseCounts)
    .sort(([, countA], [, countB]) => countB - countA)
    .map(([disease]) => disease);

  const seriesData = [{
    name: 'Number of Patients',
    data: sortedDiseases.map(disease => diseaseCounts[disease])
  }];

  return {
    categories: sortedDiseases,
    series: seriesData
  };
};

// Additional computed properties for patient demographics
const patientStats = computed(() => {
  if (!patientsData.value.length) {
    return {
      totalPatients: 0,
      totalDiseases: 0,
      genderCount: { male: 0, female: 0, unknown: 0 },
      ageGroups: { under18: 0, eighteenTo35: 0, thirtySixTo50: 0, over50: 0, unknown: 0 },
      totalVisits: 0,
      diseaseDistribution: {}
    };
  }

  const totalPatients = new Set(patientsData.value.map((p: ActivePatient) => p.record)).size;
  const uniqueDiseases = new Set(patientsData.value.map((p: ActivePatient) => p.disease_name)).size;
  
  const genderCount = {
    male: patientsData.value.filter((p: ActivePatient) => p.gender_demo === '1').length,
    female: patientsData.value.filter((p: ActivePatient) => p.gender_demo === '2').length,
    unknown: patientsData.value.filter((p: ActivePatient) => !p.gender_demo || (p.gender_demo !== '1' && p.gender_demo !== '2')).length
  };

  const ageGroups = {
    under18: 0,
    eighteenTo35: 0,
    thirtySixTo50: 0,
    over50: 0,
    unknown: 0
  };

  const diseaseDistribution: Record<string, number> = {};

  patientsData.value.forEach((patient: ActivePatient) => {
    // Age groups
    if (patient.age_demo) {
      const age = parseInt(patient.age_demo);
      if (age < 18) ageGroups.under18++;
      else if (age <= 35) ageGroups.eighteenTo35++;
      else if (age <= 50) ageGroups.thirtySixTo50++;
      else ageGroups.over50++;
    } else {
      ageGroups.unknown++;
    }

    // Disease distribution
    if (patient.disease_name) {
      diseaseDistribution[patient.disease_name] = (diseaseDistribution[patient.disease_name] || 0) + 1;
    }
  });

  return {
    totalPatients,
    totalDiseases: uniqueDiseases,
    genderCount,
    ageGroups,
    totalVisits: patientsData.value.length,
    diseaseDistribution
  };
});

onMounted(() => {
  // Process facility data
  const { categories: facilityCategories, series: facilitySeriesData } = processFacilityData();
  facilitySeries.value = facilitySeriesData;
  facilityChartOptions.value = {
    ...facilityChartOptions.value,
    xaxis: {
      ...facilityChartOptions.value.xaxis,
      categories: facilityCategories,
    },
  };

  // Process disease data
  const { categories: diseaseCategories, series: diseaseSeriesData } = processDiseaseData();
  diseaseSeries.value = diseaseSeriesData;
  diseaseChartOptions.value = {
    ...diseaseChartOptions.value,
    yaxis: {
      ...diseaseChartOptions.value.yaxis,
      categories: diseaseCategories,
    },
  };
});

// Handle condition click - navigate to specific disease page
const handleConditionClick = (condition: any) => {
  router.get(`/packages/project/39}/core_indicators/all/patients/${condition.name}`);
};

</script>

<template>
  <div>
    <!-- Debug output -->
    <div v-if="!patientsData.length" class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 mb-4">
      <p class="text-yellow-800">No patient data available or data format is incorrect.</p>
      <pre class="text-xs mt-2">Raw data: {{ props.patientsData }}</pre>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
        <h3 class="text-sm font-medium text-blue-800">Total {{ props.condition }} Patients</h3>
        <p class="text-2xl font-bold text-blue-600">{{ patientStats.totalPatients }}</p>
      </div>
      <div class="bg-green-50 p-4 rounded-lg border border-green-200">
        <h3 class="text-sm font-medium text-green-800">Disease Types</h3>
        <p class="text-2xl font-bold text-green-600">{{ patientStats.totalDiseases }}</p>
      </div>
      <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
        <h3 class="text-sm font-medium text-purple-800">Male Patients</h3>
        <p class="text-2xl font-bold text-purple-600">{{ patientStats.genderCount.male }}</p>
      </div>
      <div class="bg-pink-50 p-4 rounded-lg border border-pink-200">
        <h3 class="text-sm font-medium text-pink-800">Female Patients</h3>
        <p class="text-2xl font-bold text-pink-600">{{ patientStats.genderCount.female }}</p>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Facility/Quarter Chart -->
      <div class="bg-white p-4 rounded-lg border border-gray-200">
        <apexchart
          v-if="facilitySeries.length > 0"
          type="bar"
          height="350"
          :options="facilityChartOptions"
          :series="facilitySeries"
        ></apexchart>
        <div v-else class="text-center text-gray-500 py-8">
          No facility data available
        </div>
      </div>

      <!-- Disease Distribution Chart -->
      <div class="bg-white p-4 rounded-lg border border-gray-200">
        <apexchart
          v-if="diseaseSeries.length > 0"
          type="bar"
          height="350"
          :options="diseaseChartOptions"
          :series="diseaseSeries"
        ></apexchart>
        <div v-else class="text-center text-gray-500 py-8">
          No disease distribution data available
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <div v-if="facilitySeries.length > 0" class="py-2.5">
      <h3 class="text-lg font-semibold mb-4">{{ props.condition }} Patients by Facility and Quarter</h3>
      <div class="mt-5 overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead>
            <tr class="bg-gray-200 text-gray-700">
              <th class="py-3 px-4 text-left">Facility</th>
              <th v-for="(category, index) in facilityChartOptions.xaxis.categories" :key="index" class="py-3 px-4 text-left">{{ category }}</th>
              <th class="py-3 px-4 text-left font-semibold">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="facilitySeries in facilitySeries" :key="facilitySeries.name" class="border-b border-gray-200">
              <td class="py-3 px-4 border-b border-gray-200">{{ facilitySeries.name }}</td>
              <td v-for="(count, index) in facilitySeries.data" :key="index" class="py-3 px-4 border-b border-gray-200">{{ count }}</td>
              <td class="py-3 px-4 border-b border-gray-200 font-semibold">
                {{ facilitySeries.data.reduce((sum, count) => sum + count, 0) }}
              </td>
            </tr>
            <!-- Total Row -->
            <tr class="bg-gray-50 font-semibold">
              <td class="py-3 px-4 border-b border-gray-200">Total</td>
              <td 
                v-for="(_, quarterIndex) in facilityChartOptions.xaxis.categories" 
                :key="quarterIndex" 
                class="py-3 px-4 border-b border-gray-200"
              >
                {{ facilitySeries.reduce((sum, facility) => sum + facility.data[quarterIndex], 0) }}
              </td>
              <td class="py-3 px-4 border-b border-gray-200">
                {{ facilitySeries.reduce((total, facility) => total + facility.data.reduce((sum, count) => sum + count, 0), 0) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Age Distribution -->
    <div v-if="patientsData.length" class="mt-8">
      <h3 class="text-lg font-semibold mb-4">Age Distribution of All {{ props.condition }} Patients</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-center">
          <div class="text-sm text-gray-600">Under 18</div>
          <div class="text-xl font-bold text-gray-800">{{ patientStats.ageGroups.under18 }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-center">
          <div class="text-sm text-gray-600">18-35 Years</div>
          <div class="text-xl font-bold text-gray-800">{{ patientStats.ageGroups.eighteenTo35 }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-center">
          <div class="text-sm text-gray-600">36-50 Years</div>
          <div class="text-xl font-bold text-gray-800">{{ patientStats.ageGroups.thirtySixTo50 }}</div>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-center">
          <div class="text-sm text-gray-600">Over 50 Years</div>
          <div class="text-xl font-bold text-gray-800">{{ patientStats.ageGroups.over50 }}</div>
        </div>
      </div>
    </div>

    <!-- Disease Distribution Table -->
    <div v-if="Object.keys(patientStats.diseaseDistribution).length > 0" class="mt-8">
      <h3 class="text-lg font-semibold mb-4">Detailed Disease Distribution</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead>
            <tr class="bg-gray-200 text-gray-700">
              <th class="py-3 px-4 text-left">Disease Type</th>
              <th class="py-3 px-4 text-left">Number of Patients</th>
              <th class="py-3 px-4 text-left">Percentage</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(count, disease) in patientStats.diseaseDistribution" 
              :key="disease"
              class="border-b border-gray-200 hover:bg-gray-50 cursor-pointer"
              @click="handleConditionClick({ name: disease })"
            >
              <td class="py-3 px-4 border-b border-gray-200">{{ disease }}</td>
              <td class="py-3 px-4 border-b border-gray-200">{{ count }}</td>
              <td class="py-3 px-4 border-b border-gray-200">
                {{ ((count / patientStats.totalPatients) * 100).toFixed(1) }}%
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>