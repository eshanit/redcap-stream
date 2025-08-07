<template>
    <div>
      <canvas ref="lineChartCanvas"></canvas>
    </div>
  </template>
  
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
  const lineChartCanvas = ref<HTMLCanvasElement | null>(null);
  
  // Function to render the chart
  const renderChart = () => {
    if (!lineChartCanvas.value) return;
  
    const ctx = lineChartCanvas.value.getContext('2d');
    const labels = Object.keys(props.chartData);
    const data = Object.values(props.chartData);
  
    new Chart(ctx!, {
      type: 'line', // Change type to 'line'
      data: {
        labels: labels,
        datasets: [{
          label: 'Counts',
          data: data,
          fill: false, // No fill under the line
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 2,
          tension: 0.1, // Smooth line
        }],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
        plugins: {
          legend: {
            display: true,
            position: 'top',
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
  
  <style scoped>
  canvas {
    max-width: 1000px;
  }
  </style>