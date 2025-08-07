<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    series: {
        type: Array as () => number[],
        required: true
    },
    labels: {
        type: Array as () => string[],
        required: true
    },
    title: {
        type: String,
        default: 'Distribution'
    },
    colors: {
        type: Array as () => string[],
        default: () => ['#3B82F6', '#10B981', '#EF4444', '#F59E0B', '#8B5CF6']
    }
});

const chartOptions = computed(() => ({
    chart: {
        type: 'pie',
        toolbar: {
            show: true,
            tools: {
                download: true,
                selection: false,
                zoom: false,
                zoomin: false,
                zoomout: false,
                pan: false,
                reset: false
            }
        }
    },
    title: {
        text: props.title,
        align: 'center',
        style: {
            fontSize: '16px',
            fontWeight: 'bold',
            color: '#374151'
        },
    },
    labels: props.labels,
    colors: props.colors,
    legend: {
        position: 'bottom',
        fontSize: '14px',
        itemMargin: {
            horizontal: 8,
            vertical: 4
        },
        markers: {
            width: 12,
            height: 12,
            radius: 6
        }
    },
    dataLabels: {
        enabled: true,
        style: {
            fontSize: '12px',
            fontWeight: 'bold'
        },
        dropShadow: {
            enabled: false
        }
    },
    tooltip: {
        y: {
            formatter: (value: number) => value.toLocaleString()
        }
    },
    responsive: [{
        breakpoint: 768,
        options: {
            chart: {
                width: '100%'
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center'
            }
        }
    }]
}));
</script>

<template>
    <div>
        <div v-if="series.length === 0 || labels.length === 0" 
             class="h-full flex flex-col items-center justify-center p-8 text-gray-500">
            <div class="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 mb-4"></div>
            <p>No data available</p>
        </div>
        <apexchart 
            v-else
            type="pie" 
            :options="chartOptions" 
            :series="series"
            height="350"
        ></apexchart>
    </div>
</template>