<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

interface Enrollment {
  record: string;
  status: string | null;
  latest_instance: string | null;
  next_appo_date: string | null;
  dm_enrollment_date: string | null;
}

const props = defineProps<{
  description: string,
  data: Enrollment[];
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
      text: 'Number of Patient(s)',
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
    text: props.description,
    align: 'center',
    style: {
      fontSize: '16px',
    },
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
  const enrollmentByQuarter: Record<string, number> = {};

  props.data.forEach((item) => {
    if (item.dm_enrollment_date) {
      try {
        const date = new Date(item.dm_enrollment_date);
        const year = date.getFullYear();
        const quarter = getQuarter(item.dm_enrollment_date);
        
        if (quarter) {
          const key = `Q${quarter} ${year}`;
          
          if (!enrollmentByQuarter[key]) {
            enrollmentByQuarter[key] = 0;
          }
          enrollmentByQuarter[key]++;
        }
      } catch (e) {
        console.warn('Invalid date format', item.dm_enrollment_date);
      }
    }
  });

  // Sort quarters chronologically
  const sortedQuarters = Object.keys(enrollmentByQuarter).sort((a, b) => {
    const [qA, yA] = a.split(' ');
    const [qB, yB] = b.split(' ');
    
    const yearA = parseInt(yA);
    const yearB = parseInt(yB);
    const quarterA = parseInt(qA.substring(1));
    const quarterB = parseInt(qB.substring(1));
    
    return yearA !== yearB 
      ? yearA - yearB 
      : quarterA - quarterB;
  });

  return {
    categories: sortedQuarters,
    counts: sortedQuarters.map(q => enrollmentByQuarter[q]),
  };
};

onMounted(() => {
  const { categories, counts } = processData();
  series.value = [{
    name: 'Type II Diabetes',
    data: counts,
  }];
  
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
            <th class="py-3 px-4 text-left">Metric</th>
            <th v-for="(category, index) in chartOptions.xaxis.categories" :key="index" class="py-3 px-4 text-left">{{ category }}</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="py-3 px-4 border-b border-gray-200">Number of Enrollments</td>
            <td v-for="(count, index) in series[0]?.data" :key="index" class="py-3 px-4 border-b border-gray-200">{{ count }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

