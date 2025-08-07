<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Users, Building2 } from 'lucide-vue-next';

interface Enrollment {
  record: string;
  facility: string;
  dm_enrollment_date?: string | null;
  [key: string]: any;
}

const props = defineProps<{
  description: string,
  data: any[];
}>();

const totalPatients = computed(() => props.data.length);

const colorPalette = [
  '#3b82f6', '#10b981', '#6366f1', '#f59e0b',
  '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'
];

const chartOptions = ref({
  chart: {
    type: 'bar',
    height: 350,
    stacked: false,
    toolbar: {
      show: true,
      tools: {
        download: true,
        selection: true,
        zoom: true,
        zoomin: true,
        zoomout: true,
        pan: true,
        reset: true
      }
    },
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800
    }
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '75%',
      borderRadius: 4,
      endingShape: 'rounded',
    },
  },
  dataLabels: {
    enabled: true,
    formatter: (val: number) => val > 0 ? val : '',
    offsetY: -20,
    style: {
      fontSize: '12px',
      colors: ['#374151']
    }
  },
  stroke: {
    show: true,
    width: 1,
    colors: ['#fff']
  },
  grid: {
    borderColor: '#e5e7eb',
    strokeDashArray: 4,
    row: {
      colors: ['#f9fafb', 'transparent'],
      opacity: 0.5
    }
  },
  xaxis: {
    title: {
      text: 'Quarter',
      style: {
        color: '#374151',
        fontSize: '14px',
        fontWeight: 600
      }
    },
    categories: [] as string[],
    labels: {
      style: {
        colors: '#6b7280',
        fontSize: '12px'
      }
    },
    axisBorder: {
      show: true,
      color: '#e5e7eb'
    },
    axisTicks: {
      show: true,
      color: '#e5e7eb'
    }
  },
  yaxis: {
    title: {
      text: 'Number of Patients',
      style: {
        color: '#374151',
        fontSize: '14px',
        fontWeight: 600
      }
    },
    labels: {
      formatter: (val: number) => Math.floor(val) === val ? val.toString() : '',
      style: {
        colors: '#6b7280',
        fontSize: '12px'
      }
    },
    min: 0
  },
  fill: {
    opacity: 1,
    type: 'solid'
  },
  tooltip: {
    enabled: true,
    style: {
      fontSize: '12px'
    },
    y: {
      formatter: (val: number) => `${val} patient${val !== 1 ? 's' : ''}`,
      title: {
        formatter: (seriesName: string) => `${seriesName}:`
      }
    }
  },
  title: {
    text: props.description,
    align: 'center',
    margin: 20,
    style: {
      color: '#111827',
      fontSize: '18px',
      fontWeight: 600
    }
  },
  colors: colorPalette,
  legend: {
    position: 'bottom',
    horizontalAlign: 'center',
    fontSize: '14px',
    markers: {
      radius: 12
    },
    itemMargin: {
      horizontal: 10,
      vertical: 5
    }
  },
  responsive: [{
    breakpoint: 768,
    options: {
      chart: {
        height: 300
      },
      dataLabels: {
        enabled: false
      },
      legend: {
        position: 'bottom',
        horizontalAlign: 'center'
      }
    }
  }]
});

const series = ref<{ name: string; data: number[]; total?: number }[]>([]);
const facilityTotals = ref<{ name: string; total: number }[]>([]);

const getQuarterKey = (date: Date): string => {
  const quarter = Math.ceil((date.getMonth() + 1) / 3);
  return `Q${quarter} ${date.getFullYear()}`;
};

const processData = () => {
  const facilitiesMap = new Map<string, Map<string, number>>();
  const allQuarters = new Set<string>();
  const facilityTotalCounts = new Map<string, number>();

  // Process all records
  props.data.forEach((item) => {
    const enrollmentDate = item.dm_enrollment_date
    if (!enrollmentDate) return;

    try {
      const date = new Date(enrollmentDate);
      const quarterKey = getQuarterKey(date);
      const facility = item.facility || 'Unknown';

      allQuarters.add(quarterKey);

      if (!facilitiesMap.has(facility)) {
        facilitiesMap.set(facility, new Map());
      }

      const facilityData = facilitiesMap.get(facility)!;
      facilityData.set(quarterKey, (facilityData.get(quarterKey) || 0) + 1);

      // Update total count for facility
      facilityTotalCounts.set(facility, (facilityTotalCounts.get(facility) || 0) + 1);
    } catch (e) {
      console.warn('Invalid date format', enrollmentDate);
    }
  });

  // Sort quarters chronologically
  const sortedQuarters = Array.from(allQuarters).sort((a, b) => {
    const [qA, yA] = a.split(' ');
    const [qB, yB] = b.split(' ');
    return yA.localeCompare(yB) || qA.localeCompare(qB);
  });

  // Generate series data for each facility
  const seriesData = Array.from(facilitiesMap.entries()).map(([facility, quarters], index) => ({
    name: facility,
    data: sortedQuarters.map(q => quarters.get(q) || 0),
    color: colorPalette[index % colorPalette.length],
    total: facilityTotalCounts.get(facility) || 0
  }));

  // Sort facilities by total count (descending)
  seriesData.sort((a, b) => (b.total || 0) - (a.total || 0));

  // Prepare facility totals for display
  facilityTotals.value = seriesData.map(item => ({
    name: item.name,
    total: item.total || 0
  }));

  return {
    categories: sortedQuarters,
    series: seriesData
  };
};

onMounted(() => {
  const { categories, series: processedSeries } = processData();
  series.value = processedSeries;

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
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
      <div>
        <h3 class="text-lg font-semibold text-gray-800">Patient distribution across facilities by quarter</h3>
        <p class="text-sm text-gray-500">{{ props.description }}</p>
      </div>
      <div class="flex items-center gap-2 bg-blue-50 px-3 py-2 rounded-lg">
        <Users class="w-4 h-4 text-blue-600" />
        <span class="text-sm font-medium text-gray-700">
          {{ totalPatients }} {{ totalPatients === 1 ? 'Total Patient' : 'Total Patients' }}
        </span>
      </div>
    </div>

    <apexchart type="bar" height="350" :options="chartOptions" :series="series"
      class="border rounded-lg bg-gray-50 p-4"></apexchart>

    <div class="mt-8 space-y-6">
      <!-- Facility Totals Summary -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <h4 class="font-medium text-gray-800">Facility Summary for Unspecified/Gestational Diabetes</h4>
          <span class="text-sm text-gray-500">All facilities</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="facility in facilityTotals" :key="facility.name"
            class="border rounded-lg p-4 hover:shadow transition-shadow">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-blue-100 rounded-full">
                <Building2 class="w-4 h-4 text-blue-600" />
              </div>
              <div>
                <h5 class="font-medium text-gray-700 truncate">{{ facility.name }}</h5>
                <p class="text-2xl font-bold text-gray-900">{{ facility.total }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Quarter Data -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <h4 class="font-medium text-gray-800">Detailed Quarter Data</h4>
          <span class="text-sm text-gray-500">All time periods</span>
        </div>

        <div class="overflow-x-auto border rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Facility (Total)
                </th>
                <th v-for="(category, index) in chartOptions.xaxis.categories" :key="index" scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ category }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="facility in series" :key="facility.name">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  <div class="flex items-center gap-2">
                    <span>{{ facility.name }}</span>
                    <span class="text-xs bg-gray-100 px-2 py-1 rounded-full">
                      {{ facility.total }}
                    </span>
                  </div>
                </td>
                <td v-for="(count, index) in facility.data" :key="index"
                  class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ count }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>