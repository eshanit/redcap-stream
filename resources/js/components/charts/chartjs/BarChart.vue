<script setup lang="ts">
import { ref, onMounted, defineProps } from 'vue';
import { Chart, registerables } from 'chart.js';

// Register Chart.js components
Chart.register(...registerables);

// Define props
const props = defineProps<{
  chartData: Record<string, number>;
}>();

// Reference to the canvas element
const barChartCanvas = ref<HTMLCanvasElement | null>(null);

// Function to render the chart
const renderChart = () => {
  if (!barChartCanvas.value) return;

  const ctx = barChartCanvas.value.getContext('2d');
  const labels = Object.keys(props.chartData);
  const data = Object.values(props.chartData);

  new Chart(ctx!, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Counts',
        data: data,
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1,
      }],
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
};

// Lifecycle hook to render the chart once the component is mounted
onMounted(() => {
  renderChart();
});
</script>

<template>
    <div>
      <canvas ref="barChartCanvas"></canvas>
    </div>
  </template>
  
 
  
  <style scoped>
  canvas {
    max-width: 1000px;
  }
  </style>