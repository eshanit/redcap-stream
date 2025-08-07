<script setup lang="ts">
import { ref, computed, onMounted, Ref } from 'vue';
import { Users, Activity, TrendingUp } from 'lucide-vue-next';

interface Enrollment {
  record: string;
  status: string | null;
  latest_instance: string | null;
  next_appo_date: string | null;
  scd_enrollment_date: string | null;
}

const props = defineProps<{
  description: string,
  data: any[];
}>();

// Initialize series with default empty data to prevent undefined errors
const series:Ref<any[]> = ref([{
  name: 'Sicke Cell Disease',
  data: []
}]);

const totalPatients = computed(() => props.data.length);
const growthRate = computed(() => {
  // Safely check if we have enough data points
  if (series.value[0]?.data?.length < 2) return null;
  
  const data = series.value[0].data;
  const last = data[data.length - 1];
  const prev = data[data.length - 2];
  
  // Handle division by zero case
  if (prev === 0) return null;
  
  return ((last - prev) / prev) * 100;
});

const chartOptions = ref({
  chart: {
    type: 'bar',
    height: 350,
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
  colors: ['#3b82f6'],
  plotOptions: {
    bar: {
      borderRadius: 4,
      columnWidth: '70%',
      dataLabels: {
        position: 'top'
      }
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
        formatter: () => 'Patients:'
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
  responsive: [{
    breakpoint: 768,
    options: {
      chart: {
        height: 300
      },
      dataLabels: {
        enabled: false
      }
    }
  }]
});


const getQuarter = (dateStr: string | null): { quarter: number | null; year: number | null } => {
  if (!dateStr) return { quarter: null, year: null };

  try {
    const date = new Date(dateStr);
    return {
      quarter: Math.ceil((date.getMonth() + 1) / 3),
      year: date.getFullYear()
    };
  } catch {
    return { quarter: null, year: null };
  }
};

const processData = () => {
  const enrollmentByQuarter: Record<string, number> = {};

  props.data.forEach((item) => {
    if (item.scd_enrollment_date) {
      const { quarter, year } = getQuarter(item.scd_enrollment_date);
      if (quarter && year) {
        const key = `Q${quarter} ${year}`;
        enrollmentByQuarter[key] = (enrollmentByQuarter[key] || 0) + 1;
      }
    }
  });

  const sortedQuarters = Object.keys(enrollmentByQuarter).sort((a, b) => {
    const [qA, yA] = a.split(' ');
    const [qB, yB] = b.split(' ');
    const yearDiff = parseInt(yA) - parseInt(yB);
    return yearDiff !== 0 ? yearDiff : parseInt(qA[1]) - parseInt(qB[1]);
  });

  // Update the series reactive reference
  series.value = [{
    name: 'Heart Failure',
    data: sortedQuarters.map(q => enrollmentByQuarter[q]),
  }];

  return {
    categories: sortedQuarters,
    counts: sortedQuarters.map(q => enrollmentByQuarter[q]),
  };
};

onMounted(() => {
  const { categories } = processData();
  
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
        <h3 class="text-lg font-semibold text-gray-800">Quarterly enrollment trends for Sickle Cell Disease</h3>
        <p class="text-sm text-gray-500">{{ props.description }}</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <div class="flex items-center gap-2 bg-blue-50 px-3 py-2 rounded-lg">
          <Users class="w-4 h-4 text-blue-600" />
          <span class="text-sm font-medium text-gray-700">
            {{ totalPatients }} Total Patients
          </span>
        </div>
        <div 
          v-if="growthRate && series[0]?.data.length > 1" 
          class="flex items-center gap-2 bg-green-50 px-3 py-2 rounded-lg"
        >
          <TrendingUp class="w-4 h-4" :class="growthRate >= 0 ? 'text-green-600' : 'text-red-600'" />
          <span class="text-sm font-medium" :class="growthRate >= 0 ? 'text-green-700' : 'text-red-700'">
            {{ Math.abs(growthRate).toFixed(1) }}% {{ growthRate >= 0 ? 'Increase' : 'Decrease' }} QoQ
          </span>
        </div>
      </div>
    </div>

    <apexchart
      type="bar"
      height="350"
      :options="chartOptions"
      :series="series"
      class="border rounded-lg bg-gray-50 p-4"
    ></apexchart>

    <div class="mt-8">
      <div class="flex items-center justify-between mb-4">
        <h4 class="font-medium text-gray-800">Detailed Quarter Data</h4>
        <span class="text-sm text-gray-500">Historical enrollment</span>
      </div>
      
      <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Metric
              </th>
              <th 
                v-for="(category, index) in chartOptions.xaxis.categories" 
                :key="index" 
                scope="col" 
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                {{ category }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Total
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                Sickle Cell Disease Patients
              </td>
              <td 
                v-for="(count, index) in series[0]?.data" 
                :key="index" 
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
              >
                {{ count }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ totalPatients }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>