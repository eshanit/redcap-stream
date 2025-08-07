<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface Enrollment {
  record: string;
  status: string | null;
  latest_instance: string | null;
  liver_enrollment_date?: string | null;
  ckd_enrollment_date?: string | null;
  card_enrollment_date?: string | null;
  dm_enrollment_date?: string | null;
  resp_enrollment_date?: string | null;
  scd_enrollment_date?: string | null;
  [key: string]: any;
}

const props = defineProps<{
  description: string,
  data: Enrollment[],
}>();

const totalPatients = computed(() => props.data.length);

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
  colors: ['#3b82f6', '#10b981', '#6366f1'],
  plotOptions: {
    bar: {
      borderRadius: 4,
      columnWidth: '70%',
      distributed: false,
      endingShape: 'rounded',
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

const series = ref<{ name: string; data: number[] }[]>([]);

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
    const enrollmentDate = item.card_enrollment_date || item.dm_enrollment_date || 
                         item.resp_enrollment_date || item.ckd_enrollment_date || 
                         item.liver_enrollment_date || item.scd_enrollment_date;
    
    if (enrollmentDate) {
      const { quarter, year } = getQuarter(enrollmentDate);
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

  return {
    categories: sortedQuarters,
    counts: sortedQuarters.map(q => enrollmentByQuarter[q]),
  };
};

onMounted(() => {
  const { categories, counts } = processData();
  series.value = [{
    name: 'Enrollments',
    data: counts,
  }];
  
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
        <h3 class="text-lg font-semibold text-gray-800">{{ props.description }}</h3>
        <p class="text-sm text-gray-500">Quarterly patient enrollment trends</p>
      </div>
      <div class="flex items-center gap-2 bg-blue-50 px-3 py-2 rounded-lg">
        <Users class="w-4 h-4 text-blue-600" />
        <span class="text-sm font-medium text-gray-700">
          {{ totalPatients }} {{ totalPatients === 1 ? 'Total Patient' : 'Total Patients' }}
        </span>
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
        <span class="text-sm text-gray-500">All time periods</span>
      </div>
      
      <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Quarter
              </th>
              <th 
                v-for="(category, index) in chartOptions.xaxis.categories" 
                :key="index" 
                scope="col" 
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                {{ category }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                Enrollments
              </td>
              <td 
                v-for="(count, index) in series[0]?.data" 
                :key="index" 
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
              >
                {{ count }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>