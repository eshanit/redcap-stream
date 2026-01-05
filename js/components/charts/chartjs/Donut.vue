<template>
  <div class="relative h-full w-full">
    <canvas ref="donutChart" class="max-h-full max-w-full"></canvas>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onUnmounted, defineProps } from 'vue';
import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

// Define props with TypeScript
const props = defineProps<{
  chartData: number[];
  chartLabels: string[];
  chartColors?: string[];
}>();

// Reference to the canvas element
const donutChart = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;

// Default colors if none provided
const defaultColors = ['#3B82F6', '#EC4899', '#10B981', '#F59E0B', '#8B5CF6'];

// Create the chart
onMounted(() => {
  createChart();
});

// Watch for prop changes and update chart
watch(
  () => [props.chartData, props.chartLabels],
  () => {
    if (chartInstance) {
      updateChart();
    } else {
      createChart();
    }
  },
  { deep: true }
);

// Clean up chart instance
onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }
});

function createChart() {
  if (!donutChart.value) return;

  const ctx = donutChart.value.getContext('2d');
  if (!ctx) return;

  // Destroy existing chart if it exists
  if (chartInstance) {
    chartInstance.destroy();
  }

  chartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: props.chartLabels,
      datasets: [{
        data: props.chartData,
        backgroundColor: props.chartColors || defaultColors,
        borderColor: '#ffffff',
        borderWidth: 2,
        hoverOffset: 10,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false, // Hide legend to save space
        },
        tooltip: {
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          titleColor: '#1e293b',
          bodyColor: '#475569',
          borderColor: '#e2e8f0',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
          displayColors: true,
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.raw as number;
              const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
              const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      },
      cutout: '60%',
      animation: {
        animateScale: true,
        animateRotate: true,
        duration: 1000,
        easing: 'easeOutQuart'
      }
    }
  });
}

function updateChart() {
  if (!chartInstance) return;

  chartInstance.data.labels = props.chartLabels;
  chartInstance.data.datasets[0].data = props.chartData;
  chartInstance.data.datasets[0].backgroundColor = props.chartColors || defaultColors;
  chartInstance.update();
}
</script>

<style scoped>
/* No fixed dimensions - let parent container control size */
.relative {
  position: relative;
}

.max-h-full {
  max-height: 100%;
}

.max-w-full {
  max-width: 100%;
}

/* Ensure canvas fills container */
canvas {
  display: block;
  width: 100% !important;
  height: 100% !important;
}
</style>