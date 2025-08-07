<script setup lang="ts">
  import { ref,watch, defineProps } from 'vue';
  
  // Register the ApexCharts component
  const props = defineProps<{
    chartDescription: string;
    chartData: Record<string, number>;
  }>();
  
  const chartSeries = ref([
    {
      name: 'Counts',
      data: Object.values(props.chartData),
    },
  ]);
  
  const chartOptions = {
    chart: {
      height: 350,
      type: 'line',
    },
    title: {
      text: props.chartDescription,
      align: 'left',
    },
    xaxis: {
      categories: Object.keys(props.chartData),
    },
    yaxis: {
      title: {
        text: 'Counts',
      },
      min: 0,
    },
    stroke: {
      curve: 'straight',
    },
  };
  
  // If you need to update the chart when props change
  const updateChart = () => {
    chartSeries.value[0].data = Object.values(props.chartData);
    chartOptions.xaxis.categories = Object.keys(props.chartData);
  };
  
  watch(() => props.chartData, updateChart);

  </script>
<template>
    <div>
      <apexchart 
        type="line" 
        :options="chartOptions" 
        :series="chartSeries" 
        height="350">
      </apexchart>
    </div>
  </template>
  
  <style scoped>
  /* Add any styles you need */
  </style>