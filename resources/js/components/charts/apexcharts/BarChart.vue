<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    chartDecription: string,
    chartData: any;
}>();

const data = computed(() => {
    const categories = Object.keys(props.chartData);
    //const colors = ['#FF5733', '#33FF57', '#3357FF', '#F1C40F', '#E74C3C']; // Static colors

    // Ensure colors array matches the number of categories
    //const assignedColors = categories.map((_, index) => colors[index % colors.length]);

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
                    fontFamily: undefined,
                    color: '#263238'
                },
            },
            chart: {
                id: props.chartDecription
            },
            xaxis: {
                categories: categories
            },
          //  colors: assignedColors // Set the different colors for the bars
        },
        series: [{
            name: props.chartDecription,
            data: Object.values(props.chartData)
        }]
    };
});
</script>

<template>
    <div>
        <apexchart width="" type="bar" :options="data.options" :series="data.series"></apexchart>
    </div>
</template>