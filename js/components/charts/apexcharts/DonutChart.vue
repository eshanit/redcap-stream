<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
  series: {
    type: Array as () => number[],
    required: true
  },
  labels: {
    type: Array as () => string[],
    required: true
  },
  colors: {
    type: Array as () => string[],
    default: () => ['#10B981', '#F59E0B', '#EF4444'] // green, yellow, red
  },
  title: {
    type: String,
    default: 'HbA1c Distribution'
  },
  height: {
    type: Number,
    default: 350
  }
});

const chartOptions = ref({
  chart: {
    type: 'donut',
    fontFamily: 'Inter, sans-serif',
    animations: {
      enabled: true,
      speed: 800,
      easing: 'easeinout'
    }
  },
  labels: props.labels,
  colors: props.colors,
  legend: {
    position: 'bottom',
    fontSize: '14px',
    fontWeight: 500,
    itemMargin: {
      horizontal: 8,
      vertical: 8
    },
    markers: {
      radius: 12,
      offsetX: -4
    }
  },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        labels: {
          show: true,
          total: {
            show: true,
            showAlways: true,
            label: 'Total Respondents',
            fontSize: '16px',
            fontWeight: 600,
            color: '#6B7280',
            formatter: function (w: any) {
              return w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0);
            }
          },
          value: {
            fontSize: '24px',
            fontWeight: 700,
            color: '#111827',
            formatter: function (val: string) {
              return val;
            }
          }
        }
      }
    }
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    width: 2,
    colors: ['#fff']
  },
  tooltip: {
    enabled: true,
    y: {
      formatter: (val: number) => `${val} (${Math.round((val / props.series.reduce((a, b) => a + b, 0)) * 100)}%)`,
      title: {
        formatter: (seriesName: string) => seriesName
      }
    }
  },
  responsive: [{
    breakpoint: 640,
    options: {
      chart: {
        height: 300
      },
      legend: {
        position: 'bottom'
      }
    }
  }]
});

// Update chart when props change
watch(() => props.series, (newVal) => {
  if (newVal) {
    chartOptions.value.labels = props.labels;
    chartOptions.value.colors = props.colors;
  }
});
</script>

<template>
  <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ title }}</h3>
    <VueApexCharts
      type="donut"
      :height="height"
      :options="chartOptions"
      :series="series"
    />
    <div v-if="series.reduce((a, b) => a + b, 0) === 0" class="text-center py-8 text-gray-500">
      No data available
    </div>
  </div>
</template>