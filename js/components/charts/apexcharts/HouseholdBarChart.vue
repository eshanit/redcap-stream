<script setup lang="ts">
import { ref } from 'vue';
import { IProjectData } from '@/types/IProject';

const props = defineProps<{
    data: IProjectData[]
}>()


// Group household sizes
const householdCounts:Record<string, number> = {
    '1': 0, '2': 0, '3': 0, '4': 0, '5': 0,
    '6': 0, '7': 0, '8': 0, '9': 0, '10': 0,
    '>10': 0,
};

props.data.forEach(item => {
    const size = parseInt(item.value);
    if (size >= 1 && size <= 10) {
        householdCounts[size]++;
    } else if (size > 10) {
        householdCounts['>10']++;
    }
});

// Prepare data for the chart
const categories = Object.keys(householdCounts);
const seriesData = Object.values(householdCounts);

const chartSeries = ref([
    {
        name: 'Number of Respondents',
        data: seriesData,
    },
]);

const chartOptions = {
    chart: {
        id: 'household-size-chart',
    },
    xaxis: {
        categories: categories,
        title: {
            text: 'Household Size',
        },
    },
    yaxis: {
        title: {
            text: 'Number of Respondents',
        },
    },
    title: {
        text: 'Respondents by Household Size',
        align: 'center',
    },
};

</script>

<template>
    <div>
        <apexchart type="bar" :options="chartOptions" :series="chartSeries" />
    </div>
</template>


<style scoped>
/* Add any necessary styling */
</style>