<script setup lang='ts'>
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';
import AllRespondents from '@/components/tables/agtables/ncd/hbc1a/AllRespondents.vue';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { Database, AlertCircle, TrendingUp, BarChart3, PieChart, Target, Activity } from 'lucide-vue-next';
import ApexDonutChart from '@/components/charts/apexcharts/ncd/ApexDonutChart.vue';
import ApexBarChart from '@/components/charts/apexcharts/ncd/ApexBarChart.vue';
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
        title: 'All Data Analytics',
        href: ''
    }
];

// Prepare data for charts
const outlierChartData = computed(() => [
    props.packageData.length - props.outlierInfo.count,
    props.outlierInfo.count
]);

const outlierChartLabels = computed(() => ['Normal Values', 'Outliers']);

// Clinical category distribution (robust parsing and normalization)
const clinicalCategories = computed(() => {
    const categories = {
        'Normal (<5.7%)': 0,
        'Prediabetes (5.7-6.4%)': 0,
        'Diabetes (≥6.5%)': 0
    };

    const parseHba = (v: any) => {
        if (v === null || v === undefined) return NaN;
        let s = String(v).trim();
        // remove percent sign or commas
        s = s.replace(/%/g, '').replace(/,/g, '');
        const n = parseFloat(s);
        if (!Number.isFinite(n)) return NaN;
        // if value is a fraction (e.g., 0.065), convert to percent
        if (n > 0 && n <= 1) return n * 100;
        return n;
    };

    // Build an array of parsed numeric HbA1c values for reuse (min/max, charts)
    const parsedValues: number[] = [];

    props.packageData.forEach(item => {
        const raw = item.ncd_hb1ac;
        const value = parseHba(raw);
        if (!Number.isFinite(value)) return; // skip invalid values

        parsedValues.push(value);

        if (value < 5.7) categories['Normal (<5.7%)']++;
        else if (value >= 5.7 && value <= 6.4) categories['Prediabetes (5.7-6.4%)']++;
        else categories['Diabetes (≥6.5%)']++;
    });

    return categories;
});

const hbaNumericValues = computed(() => {
    const vals: number[] = [];
    const parse = (v: any) => {
        if (v === null || v === undefined) return NaN;
        const s = String(v).trim().replace(/%/g, '').replace(/,/g, '');
        const n = parseFloat(s);
        if (!Number.isFinite(n)) return NaN;
        return n > 0 && n <= 1 ? n * 100 : n;
    };

    props.packageData.forEach(d => {
        const n = parse(d.ncd_hb1ac);
        if (Number.isFinite(n)) vals.push(n);
    });

    return vals;
});
</script>

<template>
    <Head title="HbA1c All Data Analytics" />
    
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
                                    <Database class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                        All Data Analytics
                                    </h1>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        Comprehensive HbA1c analysis for {{ props.project.app_title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-2 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Total Values</div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white">
                                    {{ props.packageData.length }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Outlier Alert Banner -->
                    <div v-if="props.outlierInfo.count > 0" class="mb-8">
                        <div class="bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl p-5 border border-orange-200 dark:border-orange-800/30">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center">
                                    <AlertCircle class="h-6 w-6 text-white" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">
                                        Outlier Detection
                                    </h3>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        There are <span class="font-bold text-orange-600 dark:text-orange-400">{{ props.outlierInfo.count }}</span> values 
                                        which are considered outliers (HbA1c value > 20%). This constitutes 
                                        <span class="font-bold text-orange-600 dark:text-orange-400">{{ props.outlierInfo.percentage }}%</span> of the total data.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Key Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Mean HbA1c -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Mean HbA1c</p>
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">
                                        {{ statsWithOutliers.mean.toFixed(2) }}%
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center">
                                    <TrendingUp class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                {{ summaries.mean }}
                            </div>
                        </div>

                        <!-- Median HbA1c -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Median HbA1c</p>
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">
                                        {{ statsWithOutliers.median.toFixed(2) }}%
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                    <BarChart3 class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                {{ summaries.median }}
                            </div>
                        </div>

                        <!-- Standard Deviation -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Standard Deviation</p>
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">
                                        {{ statsWithOutliers.stdDev.toFixed(2) }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                                    <Activity class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                {{ summaries.stdDev }}
                            </div>
                        </div>

                        <!-- Data Range -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Data Range</p>
                                    <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                                        {{ hbaNumericValues.length ? Math.min(...hbaNumericValues).toFixed(1) : '—' }}% - 
                                        {{ hbaNumericValues.length ? Math.max(...hbaNumericValues).toFixed(1) : '—' }}%
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                    <Target class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Min - Max values
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <!-- Clinical Categories -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="mb-6">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                        <PieChart class="h-5 w-5 text-white" />
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Clinical Categories</h3>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-sm">
                                    Distribution of HbA1c values across clinical diagnostic categories
                                </p>
                            </div>
                            <div class="h-64 relative">
                                <ApexDonutChart 
                                    :chart-data="Object.values(clinicalCategories)"
                                    :chart-labels="Object.keys(clinicalCategories)"
                                    :chart-colors="['#10B981', '#F59E0B', '#EF4444']"
                                    show-legend
                                />
                            </div>
                            <div class="mt-6 grid grid-cols-3 gap-4">
                                <div v-for="([category, count], index) in Object.entries(clinicalCategories)" :key="category" 
                                     class="text-center p-3 rounded-xl"
                                     :class="[
                                         index === 0 ? 'bg-green-50 dark:bg-green-900/20' : 
                                         index === 1 ? 'bg-orange-50 dark:bg-orange-900/20' : 
                                         'bg-red-50 dark:bg-red-900/20'
                                     ]">
                                    <div class="text-2xl font-bold" 
                                         :class="[
                                             index === 0 ? 'text-green-600 dark:text-green-400' : 
                                             index === 1 ? 'text-orange-600 dark:text-orange-400' : 
                                             'text-red-600 dark:text-red-400'
                                         ]">
                                        {{ count }}
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-300 mt-1">{{ category.split('(')[0].trim() }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Outliers Distribution -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="mb-6">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                        <AlertCircle class="h-5 w-5 text-white" />
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Outliers Analysis</h3>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-sm">
                                    Proportion of normal values vs outliers in the dataset
                                </p>
                            </div>
                            <div class="h-64 relative">
                                <ApexDonutChart 
                                    :chart-data="outlierChartData"
                                    :chart-labels="outlierChartLabels"
                                    :chart-colors="['#3B82F6', '#EF4444']"
                                    show-legend
                                />
                            </div>
                            <div class="mt-6 grid grid-cols-2 gap-4">
                                <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ props.packageData.length - props.outlierInfo.count }}
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-300">Normal Values</div>
                                </div>
                                <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                        {{ props.outlierInfo.count }}
                                    </div>
                                    <div class="text-xs text-slate-600 dark:text-slate-300">Outliers (>20%)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Visualization Section -->
                    <div class="mb-8">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Data Visualization</h3>
                            <p class="text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                                Visual representations of HbA1c distribution and outliers
                            </p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Box Plot -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                            <BarChart3 class="h-5 w-5 text-white" />
                                        </div>
                                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">HbA1c Box Plot</h3>
                                    </div>
                                    <p class="text-slate-600 dark:text-slate-300 text-sm">
                                        This boxplot shows the median, spread, and outliers of HbA1c levels,
                                        providing a snapshot of glycemic control within the group.
                                    </p>
                                </div>
                                <div class="h-64 flex items-center justify-center">
                                    <BoxPlot :package-data="props.packageData" />
                                </div>
                            </div>

                            <!-- Violin Plot -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                            <PieChart class="h-5 w-5 text-white" />
                                        </div>
                                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">HbA1c Violin Plot</h3>
                                    </div>
                                    <p class="text-slate-600 dark:text-slate-300 text-sm">
                                        This violin plot displays the density and shape of HbA1c levels,
                                        revealing the distribution and common values within the group.
                                    </p>
                                </div>
                                <div class="h-64 flex items-center justify-center">
                                    <ViolinPlot :package-data="props.packageData" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Respondent Data Table -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 mb-8">
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                                        <Database class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">All Respondents Data</h3>
                                        <p class="text-slate-600 dark:text-slate-300 text-sm">
                                            Detailed HbA1c measurements for all {{ props.packageData.length }} respondents
                                        </p>
                                    </div>
                                </div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ props.packageData.length }} records
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                            <AllRespondents :all-results="props.packageData" />
                        </div>
                    </div>

                    <!-- Summary Section -->
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 rounded-2xl p-6 border border-red-200 dark:border-red-800/30">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                                <Activity class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Clinical Summary</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-3">Key Findings</h4>
                                <ul class="space-y-2 text-slate-600 dark:text-slate-300">
                                    <li class="flex items-start gap-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                                        <span>Mean HbA1c of {{ statsWithOutliers.mean.toFixed(2) }}% indicates overall glycemic control level</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                        <span>{{ props.outlierInfo.percentage }}% of values are outliers (>20%), suggesting potential data entry errors or extreme cases</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 flex-shrink-0"></div>
                                        <span>Standard deviation of {{ statsWithOutliers.stdDev.toFixed(2) }} shows data variability</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4">
                                <h4 class="font-bold text-slate-900 dark:text-white mb-3">Recommendations</h4>
                                <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        Review outlier cases for data accuracy
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        Consider stratified analysis by clinical categories
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        Explore demographic factors influencing high values
                                    </li>
                                </ul>
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
}
</style>