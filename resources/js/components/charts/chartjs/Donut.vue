<template>
    <div class="flex justify-center items-center">
      <canvas ref="donutChart"></canvas>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref, onMounted, defineProps } from 'vue';
  import { Chart, registerables } from 'chart.js';
  
  // Register Chart.js components
  Chart.register(...registerables);
  
  // Define props with TypeScript
  const props = defineProps<{
    chartData: number[];
    chartLabels: string[];
  }>();
  
  // Reference to the canvas element
  const donutChart = ref<HTMLCanvasElement | null>(null);
  
  // Create the chart
  onMounted(() => {
    if (donutChart.value) {
      const ctx = donutChart.value.getContext('2d');
      if (ctx) {
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: props.chartLabels,
            datasets: [{
              data: props.chartData,
              backgroundColor: [ '#36A2EB','#FF6384', '#FFCE56'],
              hoverOffset: 4,
            }],
          },
        });
      }
    }
  });
  </script>
  
  <style scoped>
  canvas {
    max-width: 400px;
  }
  </style>