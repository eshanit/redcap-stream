<script setup lang='ts'>
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import AllRespondents from '@/components/tables/agtables/ncd/hbc1a/AllRespondents.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import BoxPlot from '@/components/charts/chartjs/sgratz/BoxPlot.vue';
import ViolinPlot from '@/components/charts/chartjs/sgratz/ViolinPlot.vue';

const props = defineProps<{
    project: IProject,
    summaries: any,
    statsWithOutliers: any,
    statsWithoutOutliers: any,
    packageData: any[],
    outlierInfo: any
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: props.project.app_title,
        href: '/project/' + props.project.project_id,
    },
    {
        title: 'Custom Packages',
        href: '/packages/project/' + props.project.project_id + '/dashboard',
    },
    {
        title: 'HBa1c',
        href: '/packages/project/' + props.project.project_id + '/hba1c_analytics',
    },
    {
        title: 'All Data Analytics',
        href: ''
    }
];


</script>
<template>

    <Head title="Customized Packaged Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Heading title="HBc1a Reports and Analysis" description="Customised Package" class="pt-5" />

            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>A list of all Respondents who have had HBc1a done</CardTitle>
                        <!-- <CardDescription>Number of respondents in this project</CardDescription> -->
                    </CardHeader>
                    <CardContent>
                        <div class="py-2.5">
                            There are {{ props.outlierInfo.count }} values which are considered outliers (those with an
                            HBc1a value of > 20%), and this constitute {{ props.outlierInfo.percent }}% of the data.
                        </div>
                        <div class=" grid grid-cols-3 gap-5">
                            <div>
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Mean HBc1a</CardTitle>
                                        <CardDescription> {{ props.summaries.mean }} </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="text-center">
                                            <span class="text-orange-500 text-4xl "> {{
                                                props.statsWithOutliers.mean.toFixed(2) }}
                                            </span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                            <div>
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Median HBc1a</CardTitle>
                                        <CardDescription> {{ props.summaries.median }} </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="text-center">
                                            <span class="text-sky-500 text-4xl "> {{
                                                props.statsWithOutliers.median.toFixed(2) }}
                                            </span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                            <div>
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Standard Deviation HBc1a</CardTitle>
                                        <CardDescription> {{ props.summaries.stdDev }}</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="text-center">
                                            <span class="text-lime-500 text-4xl "> {{
                                                props.statsWithOutliers.stdDev.toFixed(2) }}
                                            </span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                        <!-- <pre>
                            {{ props.packageData }}
                        </pre> -->

                        <AllRespondents :all-results="props.packageData" />

                        <div class="py-5" />
                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <Card>
                                    <CardHeader>
                                        <CardTitle>HBc1a Box Plot</CardTitle>
                                        <CardDescription>
                                            This boxplot shows the median, spread, and outliers of HbA1c levels,
                                            providing a snapshot of glycemic control within the group.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="flex items-center justify-center" style="min-height: 400px;">
                                        <BoxPlot :package-data="props.packageData" />
                                    </CardContent>
                                </Card>
                            </div>
                            <div>
                                <Card>
                                    <CardHeader>
                                        <CardTitle>HBc1a Violin Plot</CardTitle>
                                        <CardDescription>
                                            This violin plot displays the density and shape of HbA1c levels,
                                            revealing the distribution and common values within the group.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent class="flex items-center justify-center" style="min-height: 400px;">
                                        <ViolinPlot :package-data="props.packageData" />
                                    </CardContent>
                                </Card>
                            </div>
                        </div>

                    </CardContent>
                    <!-- <CardFooter>
                        Card Footer
                    </CardFooter> -->
                </Card>
            </div>

        </div>
    </AppLayout>
</template>