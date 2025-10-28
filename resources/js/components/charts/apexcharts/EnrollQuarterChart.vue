<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface Enrollment {
  record: string;
  gender_demo: string | null;
  facility_demo: string | null;
  age_demo: string | null;
  enroll_date_demo: string | null;
}

const props = defineProps<{
  enrolledData: Enrollment[];
}>();

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
      text: 'Number of Enrollments',
    },
  },
  fill: {
    opacity: 1,
  },
  tooltip: {
    y: {
      formatter: (val: number) => `${val} enrollments`,
    },
  },
  title: {
    text: 'Enrollments by Quarter and Facility',
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
  // Get all unique facilities
  const facilities = Array.from(new Set(
    props.enrolledData
      .map(item => item.facility_demo)
      .filter(Boolean)
  )).sort() as string[];

  // Get all unique quarters
  const quarterMap: Record<string, { year: number; quarter: number }> = {};
  
  props.enrolledData.forEach((item) => {
    if (item.enroll_date_demo) {
      try {
        const date = new Date(item.enroll_date_demo);
        const year = date.getFullYear();
        const quarter = getQuarter(item.enroll_date_demo);
        
        if (quarter) {
          const key = `Q${quarter} ${year}`;
          quarterMap[key] = { year, quarter };
        }
      } catch (e) {
        console.warn('Invalid date format', item.enroll_date_demo);
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

  // Count enrollments by facility and quarter
  props.enrolledData.forEach((item) => {
    if (item.enroll_date_demo && item.facility_demo) {
      try {
        const date = new Date(item.enroll_date_demo);
        const year = date.getFullYear();
        const quarter = getQuarter(item.enroll_date_demo);
        
        if (quarter) {
          const quarterKey = `Q${quarter} ${year}`;
          facilityData[item.facility_demo][quarterKey]++;
        }
      } catch (e) {
        console.warn('Invalid date format', item.enroll_date_demo);
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
    <apexchart
      type="bar"
      height="350"
      :options="chartOptions"
      :series="series"
    ></apexchart>
  </div>
  <div class="py-2.5">
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
</template>