<script setup lang="ts">
import { computed } from 'vue';
import HumanFemaleIcon from 'vue-material-design-icons/HumanFemale.vue';
import HumanMaleIcon from 'vue-material-design-icons/HumanMale.vue';
import DonutChart from '@/components/charts/apexcharts/DonutChart.vue';
import BoxPlot from '@/components/charts/chartjs/sgratz/BoxPlotGender.vue';
import ViolinPlot from '@/components/charts/chartjs/sgratz/ViolinPlotGender.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'

interface IPackageData {
    mean_hba1c: number,
    classification: string,
    visit_count: number,
    health_facility: string,
    age: number
};

const props = defineProps<{
    genderType: string
    genderData: any
}>()



const chartLabels = computed(() => ['Normal', 'Prediabetic', 'Diabetic']);

const chartColors = computed(() => ['#10B981', '#F59E0B', '#EF4444']);

const chartData = computed(() => {
    return [
        props.genderData[props.genderType].classifications.normal,
        props.genderData[props.genderType].classifications.prediabetic,
        props.genderData[props.genderType].classifications.diabetic
    ]
})

const genderRecords: IPackageData[] = Object.values(props.genderData[props.genderType].records)


</script>
<template>

    <Head title="HBA1c Dashboard" />


            <div id="">

                <div class="">
                    <Card>
                        <CardHeader>
                            <CardTitle>Mean HBa1c</CardTitle>
                            <!-- <CardDescription>Count</CardDescription> -->
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-12 gap-5">
                                <div class="grid col-span-1">
                                    <div v-if="props.genderType == 'Male'">
                                        <HumanMaleIcon class="text-sky-500" :size="125" />
                                    </div>
                                    <div v-else>
                                        <HumanFemaleIcon class="text-pink-500" :size="125" />
                                    </div>
                                   
                                </div> 
                                <div class="grid-cols-11 ">
                                    <div class=" text-gray-500 font-bold flex">
                                        <div class="text-5xl ">
                                            {{ props.genderData[props.genderType].mean_hba1c }}
                                        </div>
                                        <div class="text-2xl">
                                            %
                                        </div>
                                    </div>
                                 
                                </div>
                                
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="py-5" />

                <div class="grid lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1 gap-5">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ props.genderType }} Respondents</CardTitle>
                            <CardDescription>Count</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-blue-600">
                                {{ props.genderData[props.genderType].count }}
                            </div>
                        </CardContent>
                    </Card>
                    <!-- Normal Range Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                Normal
                            </CardTitle>
                            <CardDescription>&lt; 5.7% HbA1c</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-green-600">
                                {{ props.genderData[props.genderType].classifications.normal }}
                                <span class="text-sm font-normal text-gray-500 ml-2">
                                    ({{ props.genderData[props.genderType].classificationPercentages.normal }}%)
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Prediabetic Range Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                                Prediabetic
                            </CardTitle>
                            <CardDescription>5.7% - 6.4% HbA1c</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-yellow-600">
                                {{ props.genderData[props.genderType].classifications.prediabetic }}
                                <span class="text-sm font-normal text-gray-500 ml-2">
                                    ({{ props.genderData[props.genderType].classificationPercentages.prediabetic }}%)
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Diabetic Range Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                Diabetic
                            </CardTitle>
                            <CardDescription>≥ 6.5% HbA1c</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-red-600">
                                {{ props.genderData[props.genderType].classifications.diabetic }}
                                <span class="text-sm font-normal text-gray-500 ml-2">
                                    ({{ props.genderData[props.genderType].classificationPercentages.diabetic }}%)
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="py-2.5" />

                <Card class="lg:col-span-1">
                    <CardHeader>
                        <CardTitle>HbA1c Distribution ({{ props.genderType }})</CardTitle>
                        <CardDescription>Classification of respondents</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <DonutChart :series="chartData" :labels="chartLabels" :colors="chartColors" />
                    </CardContent>
                </Card>
            </div>
            <div>
            </div>
            <div class="grid grid-cols-1 gap-1.5">
                <!-- <pre>
                    {{ Object.values(props.genderData.Male.records) }}
                </pre> -->
                <div>
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                <span class="text-sky-600" v-if="props.genderType == 'Male'">{{ props.genderType }}</span>
                                <span class="text-pink-600" v-else>{{ props.genderType }}</span>
                                
                                HBa1c Box Plot</CardTitle>
                            <CardDescription>
                                This boxplot shows the median, spread, and outliers of HbA1c levels,
                                providing a snapshot of glycemic control within the group.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex items-center justify-center" style="min-height: 400px;">
                            <BoxPlot :package-data="genderRecords" :gender="props.genderType" />
                        </CardContent>
                    </Card>
                </div>
                <div>
                    <Card>
                        <CardHeader>
                            <CardTitle><span class="text-sky-600" v-if="props.genderType == 'Male'">{{ props.genderType }}</span>
                                <span class="text-pink-600" v-else>{{ props.genderType }}</span> Violin Plot</CardTitle>
                            <CardDescription>
                                This violin plot displays the density and shape of HbA1c levels,
                                revealing the distribution and common values within the group.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="flex items-center justify-center" style="min-height: 400px;">
                            <ViolinPlot :package-data="genderRecords" :gender="props.genderType" />
                        </CardContent>
                    </Card>
                </div>
            </div>

</template>