<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    series: {
        type: Array,
        required: true
    },
    categories: {
        type: Array,
        required: true
    },
    title: {
        type: String,
        default: ''
    },
    colors: {
        type: Array,
        default: () => ['#10b981', '#ef4444']
    },
    horizontal: {
        type: Boolean,
        default: false
    }
});

const chartOptions = computed(() => ({
    chart: {
        id: props.title || 'bar-chart',
        toolbar: { show: true }
    },
    plotOptions: {
        bar: {
            horizontal: props.horizontal,
            borderRadius: 4,
            columnWidth: '70%',
        }
    },
    xaxis: {
        categories: props.categories,
        labels: {
            style: {
                fontSize: '12px'
            }
        }
    },
    yaxis: {
        labels: {
            style: {
                fontSize: '12px'
            }
        }
    },
    dataLabels: {
        enabled: false
    },
    colors: props.colors,
    legend: {
        position: 'top',
        horizontalAlign: 'center',
        fontSize: '14px'
    },
    title: {
        text: props.title,
        align: 'left',
        style: {
            fontSize: '16px',
            fontWeight: 'bold',
            color: '#374151'
        },
    },
    tooltip: {
        style: {
            fontSize: '14px'
        }
    },
    responsive: [{
        breakpoint: 768,
        options: {
            plotOptions: {
                bar: {
                    columnWidth: '50%'
                }
            },
            legend: {
                position: 'bottom'
            }
        }
    }]
}));
</script>

<template>
    <div>
        <apexchart 
            type="bar" 
            height="500" 
            :options="chartOptions" 
            :series="series"
        ></apexchart>
    </div>
</template>