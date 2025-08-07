<script setup lang="ts">

const props = defineProps<{
    chartDescription: string,
    chartData: any;
}>();
const data = {
    series: Object.values(props.chartData),
    options: {
        title: {
            text: props.chartDescription,
            align: 'left',
            margin: 10,
            offsetX: 0,
            offsetY: 0,
            floating: false,
            style: {
                fontSize: '18px',
                fontWeight: 'bold',
                fontFamily: undefined,
                color: '#263238'
            },
        },
        chart: {
            width: 280,
            type: 'pie',
        },
        labels: Object.keys(props.chartData),
        responsive: [{
            breakpoint: 280,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    }
}

</script>
<template>
    <div>
        <apexchart width="" type="pie" :options="data.options" :series="data.series"></apexchart>
    </div>
</template>