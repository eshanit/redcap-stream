<template>
  <div class="relative h-full w-full">
    <apexchart
      ref="chart"
      type="donut"
      height="100%"
      :options="chartOptions"
      :series="chartData"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps<{
  chartData: number[];
  chartLabels: string[];
  chartColors?: string[];
  chartTitle?: string;
  showLegend?: boolean;
}>();

const chartOptions = computed(() => ({
  chart: {
    type: 'donut',
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
      dynamicAnimation: {
        enabled: true,
        speed: 350,
      },
    },
  },
  labels: props.chartLabels,
  colors: props.chartColors || ['#3B82F6', '#EC4899', '#10B981', '#F59E0B', '#8B5CF6'],
  dataLabels: {
    enabled: false,
  },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        background: 'transparent',
        labels: {
          show: true,
          name: {
            show: true,
            fontSize: '14px',
            fontFamily: 'Inter, sans-serif',
            fontWeight: 600,
            color: '#1e293b',
          },
          value: {
            show: true,
            fontSize: '20px',
            fontFamily: 'Inter, sans-serif',
            fontWeight: 700,
            color: '#1e293b',
            formatter: function(val: string) {
              return val;
            },
          },
          total: {
            show: true,
            showAlways: false,
            label: 'Total',
            fontSize: '14px',
            fontWeight: 600,
            fontFamily: 'Inter, sans-serif',
            color: '#475569',
            formatter: function(w: any) {
              return w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0);
            },
          },
        },
      },
    },
  },
  legend: {
    show: props.showLegend ?? false,
    position: 'bottom',
    horizontalAlign: 'center',
    fontSize: '12px',
    fontFamily: 'Inter, sans-serif',
    fontWeight: 400,
    labels: {
      colors: '#475569',
      useSeriesColors: false,
    },
    itemMargin: {
      horizontal: 8,
      vertical: 4,
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
        const total = props.chartData.reduce((a, b) => a + b, 0);
        const percentage = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
        return `${val} (${percentage}%)`;
      },
    },
  },
  stroke: {
    width: 2,
    colors: ['#ffffff'],
  },
  responsive: [
    {
      breakpoint: 480,
      options: {
        chart: {
          height: 250,
        },
        legend: {
          position: 'bottom',
          fontSize: '11px',
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
:deep(.apexcharts-tooltip) {
  background: #ffffff !important;
  border: 1px solid #e2e8f0 !important;
  border-radius: 0.5rem !important;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

.dark :deep(.apexcharts-tooltip) {
  background: #1e293b !important;
  border-color: #475569 !important;
  color: #f1f5f9 !important;
}

:deep(.apexcharts-legend-text) {
  color: #475569 !important;
  font-family: 'Inter', sans-serif !important;
}

.dark :deep(.apexcharts-legend-text) {
  color: #cbd5e1 !important;
}

:deep(.apexcharts-datalabel-label),
:deep(.apexcharts-datalabel-value) {
  font-family: 'Inter', sans-serif !important;
}
</style>