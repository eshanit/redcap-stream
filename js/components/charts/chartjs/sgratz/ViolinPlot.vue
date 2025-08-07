<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Chart, LinearScale, CategoryScale, ChartConfiguration } from 'chart.js';
import { ViolinController, Violin } from '@sgratzl/chartjs-chart-boxplot';

Chart.register(ViolinController, Violin, LinearScale, CategoryScale);

interface IPackageData {
    record: string;
    event: string;
    ncd_health_facility: string;
    ncd_age: number;
    ncd_gender: string;
    ncd_hb1ac: number | string;
    ncd_visit_date: string;
};

const props = defineProps<{
    packageData: IPackageData[]
}>();

const violinChartCanvas = ref<HTMLCanvasElement | null>(null);

const processData = computed(() => {
    const MAX_HBA1C = 20;
    return props.packageData
        .map(item => {
            const val = Number(item.ncd_hb1ac);
            return val > MAX_HBA1C ? MAX_HBA1C : val;
        })
        .filter(value => !isNaN(value) && isFinite(value));
});

const data: ChartConfiguration<'violin'>['data'] = {
    labels: ['HbA1c'],
    datasets: [
        {
            label: 'HbA1c %',
            data: [
                processData.value,
            ],
            borderColor: 'green',
            backgroundColor: 'rgba(144, 238, 144, 0.5)', // Light Green
        }
    ],
};

const config: ChartConfiguration<'violin'> = {
    type: 'violin',
    data,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                title: {
                    display: true,
                    text: 'HbA1c (%)',
                    color: 'rgba(0, 0, 0, 0.8)'
                },
                ticks: {
                    color: 'rgba(0, 0, 0, 0.7)'
                }
            },
            x: {
                ticks: {
                    color: 'rgba(0, 0, 0, 0.7)'
                }
            }
        },
        plugins: {
            legend: {
                display: false,
            },
            title: {
                display: true,
                text: 'Distribution of HbA1c Levels',
                color: 'rgba(0, 0, 0, 0.9)'
            }
        }
    },
};

const renderChart = () => {
    if (!violinChartCanvas.value) return;

    const ctx = violinChartCanvas.value.getContext('2d');
    new Chart(ctx!, config);
};

onMounted(() => {
    renderChart();
});
</script>

<template>
    <canvas ref="violinChartCanvas" style="max-width: 1000px; max-height: 700px;"></canvas>
</template>