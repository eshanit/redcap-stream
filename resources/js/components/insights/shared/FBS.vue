<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import { useFbsAnalysisGeneral } from '@/composables/useFBSAnalysisGeneral';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import BoxPlot from '@/components/charts/apexcharts/BoxPlot.vue';
import BarChart from '@/components/charts/apexcharts/BarChart.vue';
import PieChart from '@/components/charts/apexcharts/PieChart.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>();

const downloadData = () => {
    useDownloadData(props.projectData, props.itemMetadata?.field_name);
};

// Call the analysis function once and store the result
const fbsAnalysis = useFbsAnalysisGeneral(props.projectData);

</script>
<template>
    <Card>
        <CardHeader>
            <CardTitle>
                <div v-html="props.itemMetadata?.element_label" />
            </CardTitle>
            <CardDescription>{{ props.description }}</CardDescription>
        </CardHeader>
        <CardContent>
            <div>{{ props.textInside }}</div>
            <div class="py-5">
                <Button color="green" class="cursor-pointer" @click="downloadData">
                    Download Data
                </Button>
            </div>


            <div>
                <div class="py-5">
                    Simple statistics on respondents FBS
                </div>
                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Mean FBS</CardTitle>
                                <CardDescription>This value provides a central tendency of the FBS in the dataset,
                                    indicating that on average, participants are around this blood sugar level.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <span class="text-orange-500 text-4xl "> {{
                                        fbsAnalysis.overallMean.value.toFixed(2) }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Median FBS</CardTitle>
                                <CardDescription>This statistic represents the middle point of the FBS distribution,
                                    suggesting that half of the respondents have a blood sugar level below this
                                    value. </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <span class="text-sky-500 text-4xl "> {{
                                        fbsAnalysis.overallMedian.value.toFixed(2) }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Standard Deviation FBS</CardTitle>
                                <CardDescription>The variability in FBS levels. A higher standard deviation suggests
                                    greater diversity in blood sugar levels among respondents</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="text-center">
                                    <span class="text-lime-500 text-4xl "> {{
                                        fbsAnalysis.overallStdDev.value.toFixed(2) }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
                <div class="py-5" />

                <div>
                    <BoxPlot chart-decription="Boxplot on FBS values" :chart-data="props.projectData" />
                </div>

            </div>
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>FBS Categorization</CardTitle>
                        <CardDescription>

                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-12 gap-5">
                            <div class="grid col-span-2">
                                <div>
                                    <div>
                                        <Card>
                                            <CardHeader>
                                                <CardTitle>
                                                    <div class=" text-sky-500">Normal</div>
                                                </CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <div>
                                                    FBS level is less than 5.7.
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </div>
                                    <div class="py-1.5" />
                                    <div>
                                        <Card>
                                            <CardHeader>
                                                <CardTitle>
                                                    <div class="text-orange-500">
                                                        Pre Diabetic
                                                    </div>
                                                </CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <div>
                                                    FBS level is between 5.7 and 6.5
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </div>
                                    <div class="py-1.5" />
                                    <div>
                                        <Card>
                                            <CardHeader>
                                                <CardTitle>
                                                    <div class="text-red-500">
                                                        Diabetic
                                                    </div>
                                                </CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <div>
                                                    FBS level is greater than 6.5
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </div>
                                </div>
                            </div>
                            <div class="grid col-span-6">
                                <BarChart chart-decription="Count Respondents (FBS)"
                                    :chart-data="fbsAnalysis.categoryCounts.value" />
                            </div>
                            <div class="grid col-span-4">
                                <PieChart chart-decription="% Respondents (FBS)"
                                    :chart-data="fbsAnalysis.categoryCounts.value" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

        </CardContent>
    </Card>
</template>
