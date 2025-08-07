<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';

const props = defineProps<{
  description: string,
  data: { 
    total_deaths: number,
    deaths_by_quarter: Record<string, Record<string, number>>
  }
}>();

const chartOptions = ref({
  chart: {
    type: 'bar',
    height: 350,
    toolbar: { show: true },
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '55%',
      endingShape: 'rounded',
    },
  },
  dataLabels: { enabled: false },
  stroke: {
    show: true,
    width: 2,
    colors: ['transparent'],
  },
  xaxis: {
    title: { text: 'Quarter' },
    categories: [] as string[],
  },
  yaxis: {
    title: { text: 'Number of Deaths' },
  },
  fill: { opacity: 1 },
  tooltip: {
    y: {
      formatter: (val: number) => `${val} deaths`,
    },
  },
  title: {
    text: props.description,
    align: 'center',
    style: { fontSize: '16px' },
  },
  colors: ['#ef4444'] // Red color for deaths
});

const series = ref<{ name: string; data: number[] }[]>([]);

const processData = () => {
  const categories: string[] = [];
  const counts: number[] = [];

  // Sort years numerically
  const years = Object.keys(props.data.deaths_by_quarter)
    .map(Number)
    .sort((a, b) => a - b)
    .map(String);

  years.forEach(year => {
    const quarters = props.data.deaths_by_quarter[year];
    ['Q1', 'Q2', 'Q3', 'Q4'].forEach(quarter => {
      if (quarters[quarter] > 0) {
        categories.push(`${quarter} ${year}`);
        counts.push(quarters[quarter]);
      }
    });
  });

  return { categories, counts };
};

onMounted(() => {
  const { categories, counts } = processData();
  series.value = [{
    name: 'Deaths',
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
  <div>
    <apexchart
      type="bar"
      height="350"
      :options="chartOptions"
      :series="series"
    ></apexchart>
    
    <div class="py-2.5">
      <div class="mt-5 overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead>
            <tr class="bg-gray-200 text-gray-700">
              <th class="py-3 px-4 text-left">Quarter/Year</th>
              <th 
                v-for="(category, index) in chartOptions.xaxis.categories" 
                :key="index" 
                class="py-3 px-4 text-left"
              >
                {{ category }}
              </th>
              <th class="py-3 px-4 text-left font-semibold">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="py-3 px-4 border-b border-gray-200 font-medium">Deaths</td>
              <td 
                v-for="(count, index) in series[0]?.data" 
                :key="index" 
                class="py-3 px-4 border-b border-gray-200"
              >
                {{ count }}
              </td>
              <td class="py-3 px-4 border-b border-gray-200 font-semibold">
                {{ data.total_deaths }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>