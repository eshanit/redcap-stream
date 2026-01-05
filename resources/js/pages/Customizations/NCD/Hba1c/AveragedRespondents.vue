<script setup lang='ts'>
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import AveragedRespondents from '@/components/tables/agtables/ncd/hbc1a/AveragedRespondents.vue';
import AveragedRespondentsPart from '@/components/tables/agtables/ncd/hbc1a/AveragedRespondentsPart.vue';
import Hb1acRespondent from '@/components/insights/shared/Hb1acRespondent.vue';
import useGetRespondentHBa1cData from '@/composables/useGetRespondentHBa1cData';
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { Users, TrendingUp, Activity, BarChart3, Target, AlertCircle, CheckCircle, Clock } from 'lucide-vue-next';
import ApexDonutChart from '@/components/charts/apexcharts/ncd/ApexDonutChart.vue';

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
        title: 'Projects',
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
        title: 'HbA1c Analysis',
        href: '/packages/project/' + props.project.project_id + '/hba1c_analytics',
    },
    {
        title: 'Mean HbA1c Analytics',
        href: ''
    }
];

const emittedRecord = ref('')

const handleRecordEmit = (recordId: string) => {
    emittedRecord.value = recordId;
};

const chartData = computed(() => [
    props.classificationCounts.normal,
    props.classificationCounts.prediabetic,
    props.classificationCounts.diabetic
]);

const chartLabels = computed(() => ['Normal', 'Prediabetic', 'Diabetic']);

const chartColors = computed(() => ['#10B981', '#F59E0B', '#EF4444']);

const projectId = ref(props.project.project_id);
const { recordData, isLoading, error } = useGetRespondentHBa1cData(projectId, emittedRecord);

// Calculate total percentage for validation
const totalPercentage = computed(() => 
    props.classificationPercentages.normal + 
    props.classificationPercentages.prediabetic + 
    props.classificationPercentages.diabetic
);

// Helper to get status color
const getStatusColor = (status: string) => {
    switch (status) {
        case 'normal': return 'text-green-600 dark:text-green-400';
        case 'prediabetic': return 'text-yellow-600 dark:text-yellow-400';
        case 'diabetic': return 'text-red-600 dark:text-red-400';
        default: return 'text-slate-600 dark:text-slate-400';
    }
};

// Helper to get status gradient
const getStatusGradient = (status: string) => {
    switch (status) {
        case 'normal': return 'from-green-500 to-emerald-500';
        case 'prediabetic': return 'from-yellow-500 to-orange-500';
        case 'diabetic': return 'from-red-500 to-pink-500';
        default: return 'from-slate-500 to-slate-700';
    }
};

// Helper to get status icon
const getStatusIcon = (status: string) => {
    switch (status) {
        case 'normal': return CheckCircle;
        case 'prediabetic': return Clock;
        case 'diabetic': return AlertCircle;
        default: return Activity;
    }
};
</script>

<template>
    <Head title="Mean HbA1c Analytics" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-slate-900 dark:to-slate-800">
            <!-- Page Header -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="mb-8">
                    <!-- Header with Title and Stats -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                    <TrendingUp class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                        Mean HbA1c Analytics
                                    </h1>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        Individual respondent analysis for {{ props.project.app_title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-2 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Total Respondents</div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white">
                                    {{ props.numRespondents }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Classification Summary -->
                    <div class="mb-8">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Clinical Classification Summary</h3>
                            <p class="text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                                Distribution of respondents across HbA1c diagnostic categories
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Donut Chart -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                            <BarChart3 class="h-5 w-5 text-white" />
                                        </div>
                                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Clinical Distribution</h3>
                                    </div>
                                    <p class="text-slate-600 dark:text-slate-300 text-sm">
                                        Percentage breakdown of respondents by HbA1c classification
                                    </p>
                                </div>
                                <div class="h-64 relative">
                                    <ApexDonutChart 
                                        :chart-data="chartData"
                                        :chart-labels="chartLabels"
                                        :chart-colors="chartColors"
                                        show-legend
                                    />
                                </div>
                            </div>

                            <!-- Classification Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Normal Card -->
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-5 shadow-lg border border-green-200 dark:border-green-800/30">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                                            <CheckCircle class="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 dark:text-white">Normal</h4>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">&lt; 5.7% HbA1c</p>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-1">
                                            {{ props.classificationCounts.normal }}
                                        </div>
                                        <div class="text-sm text-slate-600 dark:text-slate-300">
                                            {{ props.classificationPercentages.normal }}% of respondents
                                        </div>
                                    </div>
                                </div>

                                <!-- Prediabetic Card -->
                                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-2xl p-5 shadow-lg border border-yellow-200 dark:border-yellow-800/30">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center">
                                            <Clock class="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 dark:text-white">Prediabetic</h4>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">5.7% - 6.4% HbA1c</p>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mb-1">
                                            {{ props.classificationCounts.prediabetic }}
                                        </div>
                                        <div class="text-sm text-slate-600 dark:text-slate-300">
                                            {{ props.classificationPercentages.prediabetic }}% of respondents
                                        </div>
                                    </div>
                                </div>

                                <!-- Diabetic Card -->
                                <div class="bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 rounded-2xl p-5 shadow-lg border border-red-200 dark:border-red-800/30">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-500 rounded-xl flex items-center justify-center">
                                            <AlertCircle class="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 dark:text-white">Diabetic</h4>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">≥ 6.5% HbA1c</p>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-red-600 dark:text-red-400 mb-1">
                                            {{ props.classificationCounts.diabetic }}
                                        </div>
                                        <div class="text-sm text-slate-600 dark:text-slate-300">
                                            {{ props.classificationPercentages.diabetic }}% of respondents
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Validation Note -->
                    <div v-if="Math.abs(totalPercentage - 100) > 0.1" class="mb-8">
                        <div class="bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl p-4 border border-orange-200 dark:border-orange-800/30">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <AlertCircle class="h-4 w-4 text-white" />
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    <span class="font-semibold">Note:</span> Percentages may not total exactly 100% due to rounding.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Data Section -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 mb-8">
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                        <Users class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Detailed Respondent Data</h3>
                                        <p class="text-slate-600 dark:text-slate-300 text-sm">
                                            Click on any record to view individual respondent details
                                        </p>
                                    </div>
                                </div>
                                <div class="text-sm text-slate-500 dark:text-slate-400 hidden sm:block">
                                    {{ props.tableData.length }} records
                                </div>
                            </div>
                        </div>

                        <!-- Two Column Layout when Record Selected -->
                        <div v-if="emittedRecord !== ''" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column: Table -->
                            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4">
                                <div class="mb-4 flex items-center justify-between">
                                    <h4 class="font-bold text-slate-900 dark:text-white">All Respondents</h4>
                                    <button 
                                        @click="emittedRecord = ''"
                                        class="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
                                    >
                                        View All
                                    </button>
                                </div>
                                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                                    <AveragedRespondentsPart :table-data="props.tableData" @record="handleRecordEmit" />
                                </div>
                            </div>

                            <!-- Right Column: Respondent Details -->
                            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4">
                                <div class="mb-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-slate-900 dark:text-white">Respondent Details</h4>
                                        <div class="text-xs px-2 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                            Record ID: {{ emittedRecord }}
                                        </div>
                                    </div>
                                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                                        Detailed HbA1c analysis for selected respondent
                                    </p>
                                </div>
                                
                                <div class="h-full min-h-[400px]">
                                    <div v-if="isLoading" class="flex items-center justify-center h-full">
                                        <div class="text-center">
                                            <LoadingSpinner class="mb-3" />
                                            <p class="text-slate-600 dark:text-slate-300">Loading respondent data...</p>
                                        </div>
                                    </div>
                                    <div v-else-if="error" class="flex items-center justify-center h-full">
                                        <div class="text-center">
                                            <AlertCircle class="h-12 w-12 text-red-500 mx-auto mb-3" />
                                            <p class="text-slate-600 dark:text-slate-300">Error loading respondent data</p>
                                        </div>
                                    </div>
                                    <div v-else-if="recordData" class="h-full">
                                        <Hb1acRespondent :respondent-data="recordData" />
                                    </div>
                                    <div v-else class="flex items-center justify-center h-full">
                                        <div class="text-center">
                                            <Users class="h-12 w-12 text-slate-400 mx-auto mb-3" />
                                            <p class="text-slate-600 dark:text-slate-300">Select a respondent to view details</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Full Table when No Record Selected -->
                        <div v-else>
                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                                <AveragedRespondents :table-data="props.tableData" @record="handleRecordEmit" />
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Guidelines -->
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 rounded-2xl p-6 border border-red-200 dark:border-red-800/30">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                                <Target class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Clinical Guidelines</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Normal Range</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    HbA1c levels below 5.7% are considered normal and indicate good glycemic control.
                                    Regular monitoring and healthy lifestyle maintenance are recommended.
                                </p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Prediabetic Range</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    HbA1c levels between 5.7% and 6.4% indicate prediabetes. 
                                    Lifestyle interventions and increased monitoring can help prevent progression to diabetes.
                                </p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Diabetic Range</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    HbA1c levels of 6.5% or higher indicate diabetes. 
                                    Medical intervention, medication management, and regular monitoring are essential.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Ensure charts are responsive */
:deep(.apexcharts-canvas) {
    width: 100% !important;
}

/* Smooth transitions */
* {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .h-64 {
        height: 250px;
    }
    
    .grid-cols-1 {
        grid-template-columns: 1fr;
    }
    
    .grid-cols-2 {
        grid-template-columns: 1fr;
    }
}
</style>