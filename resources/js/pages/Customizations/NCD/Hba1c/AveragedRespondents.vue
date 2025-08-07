<script setup lang='ts'>
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import AveragedRespondents from '@/components/tables/agtables/ncd/hbc1a/AveragedRespondents.vue';
import AveragedRespondentsPart from '@/components/tables/agtables/ncd/hbc1a/AveragedRespondentsPart.vue';
import Hb1acRespondent from '@/components/insights/shared/Hb1acRespondent.vue';
import useGetRespondentHBa1cData from '@/composables/useGetRespondentHBa1cData';
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue';
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

const props = defineProps<{
    project: IProject,
    tableData: any[],
    numRespondents: number,
    classificationCounts: {
        normal: number,
        prediabetic: number,
        diabetic: number
    },
    classificationPercentages: {
        normal: number,
        prediabetic: number,
        diabetic: number
    }
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
        title: 'Mean HBa1c Analytics',
        href: ''
    }
];

const emittedRecord = ref('')

const handleRecordEmit = (recordId: string) => {
    emittedRecord.value = recordId;
    console.log('Received record:', recordId);  // Debug log
};
//
const chartData = computed(() => [
    props.classificationCounts.normal,
    props.classificationCounts.prediabetic,
    props.classificationCounts.diabetic
]);

const chartLabels = computed(() => ['Normal', 'Prediabetic', 'Diabetic']);

const chartColors = computed(() => ['#10B981', '#F59E0B', '#EF4444']); // green, yellow, red

// const recordData = (record: string) => {
//     return props.recordsWithStats.find(r => r.record == record);
// }

// Reactive references for the composable
const projectId = ref(props.project.project_id);

const { recordData, isLoading, error } = useGetRespondentHBa1cData(projectId, emittedRecord);

</script>
<template>

    <Head title="Customized Packaged Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Heading title="HBc1a Reports and Analysis" description="Customised Package" class="pt-5" />
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Respondents Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Total Respondents</CardTitle>
                        <CardDescription>All participants with HbA1c data</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-blue-600">
                            {{ props.numRespondents }}
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
                            {{ props.classificationCounts.normal }}
                            <span class="text-sm font-normal text-gray-500 ml-2">
                                ({{ props.classificationPercentages.normal }}%)
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
                            {{ props.classificationCounts.prediabetic }}
                            <span class="text-sm font-normal text-gray-500 ml-2">
                                ({{ props.classificationPercentages.prediabetic }}%)
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
                            {{ props.classificationCounts.diabetic }}
                            <span class="text-sm font-normal text-gray-500 ml-2">
                                ({{ props.classificationPercentages.diabetic }}%)
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </div>


            <div class="mt-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Detailed Respondent Data</CardTitle>
                        <CardDescription>Click on any record to view individual details</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-12 gap-5" v-if="emittedRecord != ''">
                            <div class="grid col-span-6">
                                <AveragedRespondentsPart :table-data="props.tableData" @record="handleRecordEmit" />
                            </div>
                            <div class="grid col-span-6">
                                <div v-if="isLoading" class="flex items-center justify-center h-32">
                                    <LoadingSpinner />
                                </div>
                                <div v-else>
                                    <Hb1acRespondent :respondent-data="recordData" />
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <AveragedRespondents :table-data="props.tableData" @record="handleRecordEmit" />
                        </div>
                    </CardContent>
                </Card>
            </div>

        </div>
    </AppLayout>
</template>