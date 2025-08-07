<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Chart, LinearScale, CategoryScale, ChartConfiguration } from 'chart.js';
import { BoxPlotController, BoxAndWiskers } from '@sgratzl/chartjs-chart-boxplot';

Chart.register(BoxAndWiskers, LinearScale, CategoryScale, BoxPlotController);

interface IPackageData {
    mean_hba1c: number,
    classification: string,
    visit_count: number,
    health_facility: string,
    age: number
};

const props = defineProps<{
    packageData: IPackageData[],
    gender: string
}>();

const boxPlotChartCanvas = ref<HTMLCanvasElement | null>(null);

const processData = computed(() => {


    const MAX_HBA1C = 20;
    return props.packageData
        .map(item => {
            const val = Number(item.mean_hba1c);
            return val > MAX_HBA1C ? MAX_HBA1C : val;
        })
        .filter(value => !isNaN(value) && isFinite(value));
});

const data: ChartConfiguration<'boxplot'>['data'] = {
    labels: [props.gender+' HBa1c'],
    datasets: [
        {
            label: 'HBa1c %',
            data: [
                processData.value,
            ],
            borderColor: 'green',
            medianColor: 'blue',
            borderWidth: 1,
            outlierRadius: 3,
            itemRadius: 3,
            lowerBackgroundColor: 'lightblue',
            outlierBackgroundColor: '#999999',                    // Adjust outlier size if needed
        }
    ],
};

const config: ChartConfiguration<'boxplot'> = {
    type: 'boxplot',
    data,
    options: {
        responsive: true,
        maintainAspectRatio: false, // Allow the chart to resize
        scales: {
            y: {
                title: {
                    display: true,
                    text: 'HbA1c (%)',
                    color: 'rgba(0, 0, 0, 0.8)' // Y-axis label color
                },
                ticks: {
                    color: 'rgba(0, 0, 0, 0.7)' // Y-axis tick color
                }
            },
            x: {
                ticks: {
                    color: 'rgba(0, 0, 0, 0.7)' // X-axis tick color
                }
            }
        },
        plugins: {
            legend: {
                display: false,          // Hide the legend
            },
            title: {
                display: true,
                text: props.gender + ' Distribution of HbA1c Levels',
                color: 'rgba(0, 0, 0, 0.9)' // Title color
            }
        },
        elements: {
            boxandwhiskers: {
                itemRadius: 2,
                itemHitRadius: 4,
            },
        },
    },
};

const renderChart = () => {
    if (!boxPlotChartCanvas.value) return;

    const ctx = boxPlotChartCanvas.value.getContext('2d');
    new Chart(ctx!, config);
};

onMounted(() => {
    renderChart();
});
</script>

<template>
    <canvas ref="boxPlotChartCanvas" style="max-width: 1000px; max-height: 700px;"></canvas>
</template>