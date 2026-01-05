<template>
  <div class="relative h-full w-full">
    <apexchart
      ref="chart"
      type="bar"
      height="100%"
      :options="chartOptions"
      :series="chartSeries"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps } from 'vue';

const props = defineProps<{
  chartData: Record<string, number>;
  chartColors?: string[];
  chartTitle?: string;
  horizontal?: boolean;
}>();

const chartSeries = computed(() => [
  {
    name: props.chartTitle || 'Facility',
    data: Object.values(props.chartData),
  },
]);

const chartOptions = computed(() => ({
  chart: {
    type: 'bar',
    height: '100%',
    width: '100%',
    toolbar: {
      show: false,
    },
    animations: {
      enabled: true,
      speed: 800,
      animateGradually: {
        enabled: true,
        delay: 150,
      },
    },
  },
  plotOptions: {
    bar: {
      horizontal: props.horizontal || false,
      borderRadius: 6,
      borderRadiusApplication: 'end',
      columnWidth: '70%',
      distributed: true,
    },
  },
  dataLabels: {
    enabled: false,
  },
  xaxis: {
    categories: Object.keys(props.chartData),
    labels: {
      style: {
        colors: '#475569',
        fontSize: '12px',
        fontFamily: 'Inter, sans-serif',
        fontWeight: 500,
      },
    },
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
  },
  yaxis: {
    labels: {
      style: {
        colors: '#475569',
        fontSize: '12px',
        fontFamily: 'Inter, sans-serif',
        fontWeight: 500,
      },
      formatter: function(val: number) {
        return val.toLocaleString();
      },
    },
  },
  colors: props.chartColors || ['#8B5CF6', '#EC4899', '#3B82F6', '#10B981', '#F59E0B'],
  grid: {
    borderColor: '#e2e8f0',
    strokeDashArray: 4,
    xaxis: {
      lines: {
        show: false,
      },
    },
    yaxis: {
      lines: {
        show: true,
      },
    },
  },
  tooltip: {
    enabled: true,
    theme: 'light',
    style: {
      fontSize: '12px',
      fontFamily: 'Inter, sans-serif',
    },
    y: {
      formatter: function(val: number) {
        return val.toLocaleString();
      },
    },
  },
  responsive: [
    {
      breakpoint: 640,
      options: {
        plotOptions: {
          bar: {
            horizontal: true,
            columnWidth: '50%',
          },
        },
        xaxis: {
          labels: {
            style: {
              fontSize: '10px',
            },
          },
        },
        yaxis: {
          labels: {
            style: {
              fontSize: '10px',
            },
          },
        },
      },
    },
  ],
}));
</script>

<style scoped>
/* Ensure the chart container fills its parent */
:deep(.apexcharts-canvas) {
  width: 100% !important;
}

/* Dark mode support */
.dark :deep(.apexcharts-tooltip) {
  background: #1e293b !important;
  border-color: #475569 !important;
  color: #f1f5f9 !important;
}

.dark :deep(.apexcharts-xaxis-label),
.dark :deep(.apexcharts-yaxis-label) {
  fill: #cbd5e1 !important;
}

.dark :deep(.apexcharts-gridline) {
  stroke: #475569 !important;
}
</style>