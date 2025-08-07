<script setup lang="ts">
import { ref, computed, watch } from 'vue';


const props = defineProps<{
  recordData: {
    visits: Array<{
      hba1c: number;
      visit_date?: string;
    }>;
    statistics: any;
    demographics: any;
  };
}>();

const chartOptions = computed(() => {
  // Sort visits by date if available, otherwise by index
  const sortedEntries = [...props.recordData.visits].sort((a, b) => {
    if (a.visit_date && b.visit_date) {
      return new Date(a.visit_date).getTime() - new Date(b.visit_date).getTime();
    }
    return 0;
  });

  return {
    chart: {
      type: 'line',
      height: 350,
      toolbar: {
        show: true,
        tools: {
          download: true,
          selection: true,
          zoom: true,
          zoomin: true,
          zoomout: true,
          pan: true,
          reset: true
        }
      }
    },
    stroke: {
      curve: 'smooth',
      width: 3
    },
    markers: {
      size: 5,
      hover: {
        size: 7
      }
    },
    xaxis: {
      categories: sortedEntries.map((entry, index) =>
        entry.visit_date
          ? new Date(entry.visit_date).toLocaleDateString()
          : `Visit ${index + 1}`
      ),
      title: {
        text: 'Visit Date',
        style: {
          fontSize: '12px',
          fontWeight: 600
        }
      }
    },
    yaxis: {
      title: {
        text: 'HbA1c (%)',
        style: {
          fontSize: '12px',
          fontWeight: 600
        }
      },
      min: Math.max(0, Math.floor(Math.min(...sortedEntries.map(e => e.hba1c)) - 1)),
      max: Math.ceil(Math.max(...sortedEntries.map(e => e.hba1c)) + 1),
      labels: {
        formatter: (value: number) => value.toFixed(1)
      }
    },
    annotations: {
      yaxis: [
        {
          y: 5.7,
          borderColor: '#FFA500',
          label: {
            borderColor: '#FFA500',
            style: {
              color: '#fff',
              background: '#FFA500'
            },
            text: 'Normal (<=5.6)'
          }
        },
        {
          y: 6.5,
          borderColor: '#FF0000',
          label: {
            borderColor: '#FF0000',
            style: {
              color: '#fff',
              background: '#FF0000'
            },
            text: 'Diabetes (>=6.5)'
          }
        }
      ]
    },
    tooltip: {
      y: {
        formatter: (value: number) => `${value.toFixed(2)}%`
      }
    }
  };
});

const series = computed(() => [{
  name: 'HbA1c',
  data: props.recordData.visits.map(entry => entry.hba1c)
}]);
</script>

<template>
  
  <div class="bg-white rounded-lg shadow p-4">
    <div class="mb-4">
      <!-- <h3 class="text-lg font-semibold">Record #{{ recordData.statistics.basic.record }}</h3> -->
      <div class="text-sm text-gray-600">
        <span class="mr-3">Age: {{ recordData?.demographics.age }}</span>
        <span class="mr-3">Gender: {{ recordData?.demographics.gender }}</span>
        <span>Facility: {{ recordData.demographics.health_facility }}</span>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
      <div class="bg-blue-50 p-3 rounded">
        <div class="text-sm text-blue-600">Min HbA1c</div>
        <div class="text-2xl font-bold">{{ recordData.statistics.basic.min }}%</div>
      </div>
      <div class="bg-green-50 p-3 rounded">
        <div class="text-sm text-green-600">Mean HbA1c</div>
        <div class="text-2xl font-bold">{{ recordData.statistics.basic.mean }}%</div>
      </div>
      <div class="bg-red-50 p-3 rounded">
        <div class="text-sm text-red-600">Max HbA1c</div>
        <div class="text-2xl font-bold">{{ recordData.statistics.basic.max }}%</div>
      </div>
    </div>

    <apexchart :options="chartOptions" :series="series" height="350" />

    <!-- Additional Statistics Section -->
    <div class="border-t pt-4">
      <h4 class="font-medium text-gray-700 mb-3">Detailed Statistics</h4>

      <div class="grid grid-cols-1  gap-4">
        <!-- Spread Statistics -->
        <div class="bg-gray-50 p-3 rounded">
          <h5 class="font-medium text-gray-600 mb-2">Distribution Spread</h5>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Q1 (25th percentile):</span>
              <span class="font-medium">{{ recordData.statistics.spread.q1 }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Q3 (75th percentile):</span>
              <span class="font-medium">{{ recordData.statistics.spread.q3 }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">IQR (Q3-Q1):</span>
              <span class="font-medium">{{ recordData.statistics.spread.iqr }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Coefficient of Variation:</span>
              <span class="font-medium">{{ recordData.statistics.spread.cv }}</span>
            </div>
          </div>
        </div>

        <!-- Clinical Classification -->
        <div class="bg-gray-50 p-3 rounded">
          <h5 class="font-medium text-gray-600 mb-2">Clinical Classification</h5>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Normal (<5.7%):</span>
                  <span class="font-medium">{{ recordData.statistics.clinical_thresholds.normal }} visits</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Prediabetes (5.7-6.4%):</span>
              <span class="font-medium">{{ recordData.statistics.clinical_thresholds.prediabetes }} visits</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Diabetes (≥6.5%):</span>
              <span class="font-medium">{{ recordData.statistics.clinical_thresholds.diabetes }} visits</span>
            </div>
          </div>
        </div>

        <!-- Time Analysis -->
        <div class="bg-gray-50 p-3 rounded" v-if="recordData.statistics.time_analysis.days_between_first_last_visit">
          <h5 class="font-medium text-gray-600 mb-2">Time Analysis</h5>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Observation period:</span>
              <span class="font-medium">{{ recordData.statistics.time_analysis.days_between_first_last_visit }} days</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Average change per day:</span>
              <span class="font-medium">{{ recordData.statistics.time_analysis.rate_of_change_per_day.toFixed(4) }}%/day</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Total visits:</span>
              <span class="font-medium">{{ recordData.statistics.basic.count }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
</template>