<script setup lang="ts">
import { ref, computed } from 'vue';
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import useCountRecordsPerYear from '@/composables/useCountRecordsPerYear';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import LineChart from '@/components/charts/apexcharts/LineChart.vue';

const props = defineProps<{
    mostCardTitle: string,
    currentCardTitle: string,
    lineChartDescription: string,
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>();

type IChartData = Record<string, any>;

const chartData = computed<IChartData>(() => {

    const originalData = useCountRecordsPerYear(props.projectData);
    
    const aggregatedData: IChartData = {};
    let totalBefore2014 = 0;
    let maxRespondents = 0;
    let yearWithMostRespondents = '';
    const currentYear = new Date().getFullYear().toString();
    let currentYearRespondents = 0;

    for (const year in originalData) {
        const yearNum = parseInt(year);
   
            aggregatedData[year] = originalData[year];

            // Check for the year with the most Respondents
            if (originalData[year] > maxRespondents) {
                maxRespondents = originalData[year];
                yearWithMostRespondents = year;
            }
        

        // Capture current year Respondents
        if (year === currentYear) {
            currentYearRespondents = originalData[year];
        }
    }

    // Add the aggregated count for "2014 and below" at the start
  

    return {
        orderedData: aggregatedData,
        yearmax: yearWithMostRespondents,
        maxres: maxRespondents,
        currentyearres: currentYearRespondents
    };
});


// Function to download data
const downloadData = () => {
    useDownloadData(props.projectData, props.itemMetadata?.field_name);
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle><div v-html="props.itemMetadata?.element_label" /></CardTitle>
            <CardDescription>{{ props.description }}</CardDescription>
        </CardHeader>
        <CardContent>
            <div>
                <div>{{ props.textInside }}</div>
                <div class="py-5">
                    <Button color="green" class="cursor-pointer" @click="downloadData">
                        Download Data
                    </Button>
                </div>

                <div class="" v-if="props.projectData?.length > 0">
                    <div class=" grid grid-cols-2 gap-5">
                        <div>
                            <Card>
                                <CardHeader>
                                    <CardTitle>{{ props.mostCardTitle }}</CardTitle>
                                    <CardDescription>{{ chartData.maxres }} respondents</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="text-center">
                                        <span class="text-orange-500 text-4xl "> {{ chartData.yearmax }}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                        <div>
                            <Card>
                                <CardHeader>
                                    <CardTitle>{{ props.currentCardTitle }} </CardTitle>
                                    <CardDescription>{{ new Date().getFullYear().toString() }}</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="text-center">
                                        <span class="text-sky-500 text-4xl "> {{ chartData.currentyearres }}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                    </div>
                    <div class="py-5" />
                    <div>
                        <LineChart :chart-data="chartData.orderedData" />
                    </div>
                    <div class="py-2.5" />
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr class="w-full bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                    <th class="py-3 px-6 text-left">Year</th>
                                    <th class="py-3 px-6 text-left" v-for="year in Object.keys(chartData.orderedData)"
                                        :key="year">{{ year }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-200">
                                    <td class="py-3 px-6 text-left">Count</td>
                                    <td class="py-3 px-6 text-left" v-for="year in Object.keys(chartData.orderedData)"
                                        :key="year">{{ chartData.orderedData[year] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="py-2.5" />
                </div>
            </div>

        </CardContent>
    </Card>
</template>