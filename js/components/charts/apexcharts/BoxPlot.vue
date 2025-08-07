<script setup lang="ts">
import { computed } from 'vue';
import * as ss from 'simple-statistics'

const props = defineProps<{
    chartDecription: string;
    chartData: { value: string }[];
}>();

const calculateStatistics = (data: number[]) => {
    const min = Math.min(...data);
    const q1 = ss.quantile(data, 0.25);
    const median = ss.median(data);
    const q3 = ss.quantile(data, 0.75);
    const max = Math.max(...data);
    return [min, q1, median, q3, max];
};

// Use the function to get statistics
const statistics = computed(() => {
    const values = props.chartData.map(item => parseFloat(item.value));
    return calculateStatistics(values);
});


const data = computed(() => {
    return {
        options: {
            title: {
                text: props.chartDecription,
                align: 'left',
                margin: 10,
                offsetX: 0,
                offsetY: 0,
                floating: false,
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold',
                    color: '#263238'
                },
            },
            plotOptions: {
                boxPlot: {
                    dataLabels: {
                        enabled: true,
                    },
                },
            },
            chart: {
                id: props.chartDecription,
            },
        },

        series: [{
            type: "boxPlot",
            data: [{
                x: props.chartDecription,
                y: statistics.value,
            }]
        }]
    };
});
</script>

<template>
    <div class="grid grid-cols-2 gap-5">
        <div>
            <apexchart type="boxPlot" :options="data.options" :series="data.series"></apexchart>
        </div>
        <div>
            <h3>Statistics:</h3>

            <div class="border rounded-lg p-5">
                <div class="grid grid-cols-2 gap-5 hover:bg-gray-100 py-2.5 ">
                    <div class="text-sky-700 font-semibold">
                        Minimum FBS
                    </div>
                    <div>
                        {{ statistics[0] }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5 hover:bg-gray-100 py-2.5 border-t ">
                    <div class="text-sky-700 font-semibold">
                        First Quartile (Q1)
                    </div>
                    <div>
                        {{ statistics[1] }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5 hover:bg-gray-100 py-2.5 border-t">
                    <div class="text-sky-700 font-semibold">
                        Median
                    </div>
                    <div>
                        {{ statistics[2] }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5 hover:bg-gray-100 py-2.5 border-t">
                    <div class="text-sky-700 font-semibold">
                        Third Quartile (Q3)
                    </div>
                    <div>
                        {{ statistics[3] }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5 hover:bg-gray-100 py-2.5 border-t">
                    <div class="text-sky-700 font-semibold">
                        Maximum FBS
                    </div>
                    <div>
                        {{ statistics[4] }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>