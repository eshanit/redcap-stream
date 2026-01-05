<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface ActivePatient {
  record: string;
  instance: string;
  gender_demo: string | null;
  facility_demo: string | null;
  age_demo: string | null;
  scd_visit_date: string | null;
  scd_appo_date: string | null;
  scd_outcome: string | null;
  scd_diagnosis: string | string[] | null;
}

const props = defineProps<{
  activePatientsData: ActivePatient[] | any;
}>();

// Create a computed property to ensure we always have an array
const patientsData = computed(() => {
  if (Array.isArray(props.activePatientsData)) {
    return props.activePatientsData;
  } else if (props.activePatientsData && typeof props.activePatientsData === 'object') {
    // If it's an object, try to convert it to array
    return Object.values(props.activePatientsData);
  } else {
    return [];
  }
});

const chartOptions = ref({
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
      text: 'Number ofPatients',
    },
  },
  fill: {
    opacity: 1,
  },
  tooltip: {
    y: {
      formatter: (val: number) => `${val} patients`,
    },
  },
  title: {
    text: 'Sickle Cell Disease Patients by Quarter and Facility',
    align: 'center',
    style: {
      fontSize: '16px',
    },
  },
  legend: {
    position: 'top',
  },
});

const series = ref<{ name: string; data: number[] }[]>([]);

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

const processData = () => {
  // Use the computed patientsData instead of props.activePatientsData directly
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
    if (item.scd_visit_date) {
      try {
        const date = new Date(item.scd_visit_date);
        const year = date.getFullYear();
        const quarter = getQuarter(item.scd_visit_date);
        
        if (quarter) {
          const key = `Q${quarter} ${year}`;
          quarterMap[key] = { year, quarter };
        }
      } catch (e) {
        console.warn('Invalid date format', item.scd_visit_date);
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
  // We count unique records per quarter (not visits) to avoid double-counting patients
  const quarterlyPatients: Record<string, Set<string>> = {};
  
  // Initialize sets for each quarter
  sortedQuarters.forEach(quarter => {
    quarterlyPatients[quarter] = new Set();
  });

  // Group patients by quarter and facility, ensuring we count each patient only once per quarter
  patientsData.value.forEach((patient: ActivePatient) => {
    if (patient.scd_visit_date && patient.facility_demo) {
      try {
        const date = new Date(patient.scd_visit_date);
        const year = date.getFullYear();
        const quarter = getQuarter(patient.scd_visit_date);
        
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
        console.warn('Invalid date format', patient.scd_visit_date);
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

// Additional computed properties for patient demographics
const patientStats = computed(() => {
  if (!patientsData.value.length) {
    return {
      totalPatients: 0,
      genderCount: { male: 0, female: 0, unknown: 0 },
      ageGroups: { under18: 0, eighteenTo35: 0, thirtySixTo50: 0, over50: 0, unknown: 0 },
      totalVisits: 0
    };
  }

  const totalPatients = new Set(patientsData.value.map((p: ActivePatient) => p.record)).size;
  
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

  patientsData.value.forEach((patient: ActivePatient) => {
    if (patient.age_demo) {
      const age = parseInt(patient.age_demo);
      if (age < 18) ageGroups.under18++;
      else if (age <= 35) ageGroups.eighteenTo35++;
      else if (age <= 50) ageGroups.thirtySixTo50++;
      else ageGroups.over50++;
    } else {
      ageGroups.unknown++;
    }
  });

  return {
    totalPatients,
    genderCount,
    ageGroups,
    totalVisits: patientsData.value.length
  };
});

onMounted(() => {
  const { categories, series: processedSeries } = processData();
  series.value = processedSeries;
  
  // Update chart options with the categories
  chartOptions.value = {
    ...chartOptions.value,
    xaxis: {
      ...chartOptions.value.xaxis,
      categories,
    },
  };
});
</script>

<template>
  <div>
    
    <!-- Debug output -->
    <div v-if="!patientsData.length" class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 mb-4">
      <p class="text-yellow-800">No patient data available or data format is incorrect.</p>
      <pre class="text-xs mt-2">Raw data: {{ props.activePatientsData }}</pre>
    </div>

    <pre v-else class="hidden">
        {{ patientsData }}
    </pre>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
        <h3 class="text-sm font-medium text-blue-800">Total Patients</h3>
        <p class="text-2xl font-bold text-blue-600">{{ patientStats.totalPatients }}</p>
      </div>
      <div class="bg-green-50 p-4 rounded-lg border border-green-200">
        <h3 class="text-sm font-medium text-green-800">Total Visits</h3>
        <p class="text-2xl font-bold text-green-600">{{ patientStats.totalVisits }}</p>
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

    <!-- Chart -->
    <apexchart
      v-if="series.length > 0"
      type="bar"
      height="350"
      :options="chartOptions"
      :series="series"
    ></apexchart>

    <!-- Data Table -->
    <div v-if="series.length > 0" class="py-2.5">
      <div class="mt-5 overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead>
            <tr class="bg-gray-200 text-gray-700">
              <th class="py-3 px-4 text-left">Facility</th>
              <th v-for="(category, index) in chartOptions.xaxis.categories" :key="index" class="py-3 px-4 text-left">{{ category }}</th>
              <th class="py-3 px-4 text-left font-semibold">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="facilitySeries in series" :key="facilitySeries.name" class="border-b border-gray-200">
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
                v-for="(_, quarterIndex) in chartOptions.xaxis.categories" 
                :key="quarterIndex" 
                class="py-3 px-4 border-b border-gray-200"
              >
                {{ series.reduce((sum, facility) => sum + facility.data[quarterIndex], 0) }}
              </td>
              <td class="py-3 px-4 border-b border-gray-200">
                {{ series.reduce((total, facility) => total + facility.data.reduce((sum, count) => sum + count, 0), 0) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Age Distribution -->
    <div v-if="patientsData.length" class="mt-8">
      <h3 class="text-lg font-semibold mb-4">Age Distribution of Active Sickle Cell Patients</h3>
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
  </div>
</template>