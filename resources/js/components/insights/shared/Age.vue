<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import BarChart from '@/components/charts/apexcharts/BarChart.vue';
import PieChart from '@/components/charts/apexcharts/PieChart.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import useCountAgeGroups from '@/composables/useCountAgeGroups';
import useSimpleStatistics from '@/composables/useSimpleStatistics';
const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>();

const downloadData = () => {
    useDownloadData(props.projectData, props.itemMetadata?.field_name);
};
</script>
<template>
    <Card>
        <CardHeader>
            <CardTitle>{{ props.itemMetadata?.element_label }}</CardTitle>
            <CardDescription>{{ props.description }}</CardDescription>
        </CardHeader>
        <CardContent>
            <div>{{ props.textInside }}</div>
            <div class="py-5">
                <Button color="green" class="cursor-pointer" @click="downloadData">
                    Download Data
                </Button>
            </div>
            <div class="" v-if="props.projectData?.length">
                <div class=" grid grid-cols-3 gap-5">
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Mean Age</CardTitle>
                                <CardDescription>This value provides a central tendency of the ages in the dataset, indicating that on average, participants are around this age. </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <span class="text-orange-500 text-4xl "> {{ useSimpleStatistics(props.projectData).mean }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Median Age</CardTitle>
                                <CardDescription>This statistic represents the middle point of the age distribution, suggesting that half of the individuals are younger than this age, and half are older. </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <span class="text-sky-500 text-4xl "> {{ useSimpleStatistics(props.projectData).median }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Modal Age</CardTitle>
                                <CardDescription>This mode indicates the age that appears most often among the participants, highlighting a common age group within the surveyed population</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <span class="text-lime-500 text-4xl "> {{ useSimpleStatistics(props.projectData).mode }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
                <div class="py-5" />
                <div class="grid grid-cols-12 gap-5">
                    <div class="grid col-span-8">
                        <BarChart chart-decription="Age group counts"
                            :chart-data="useCountAgeGroups(props.projectData)" />
                    </div>
                    <div class="grid col-span-4">
                        <PieChart chart-decription="% of respondents in Age Groups"
                            :chart-data="useCountAgeGroups(props.projectData)" />
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>