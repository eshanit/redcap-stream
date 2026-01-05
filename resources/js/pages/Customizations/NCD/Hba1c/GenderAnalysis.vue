<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import GenderReports from '@/components/insights/ncd/hba1c/GenderReports.vue';
import { Users, Activity, TrendingUp, BarChart3, PieChart, Target, Scale } from 'lucide-vue-next';
import ApexDonutChart from '@/components/charts/apexcharts/ncd/ApexDonutChart.vue';

const props = defineProps<{
    project: IProject,
    genderData: any
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
        title: 'Gender Analysis',
        href: ''
    }
];

// Calculate gender statistics
const maleData = computed(() => props.genderData?.Male || {});
const femaleData = computed(() => props.genderData?.Female || {});

const maleCount = computed(() => maleData.value.count || 0);
const femaleCount = computed(() => femaleData.value.count || 0);
const totalCount = computed(() => maleCount.value + femaleCount.value);

const malePercentage = computed(() => totalCount.value > 0 ? Math.round((maleCount.value / totalCount.value) * 100) : 0);
const femalePercentage = computed(() => totalCount.value > 0 ? Math.round((femaleCount.value / totalCount.value) * 100) : 0);

// Prepare data for donut chart
const genderDistributionData = computed(() => [maleCount.value, femaleCount.value]);
const genderDistributionLabels = computed(() => ['Male', 'Female']);
const genderDistributionColors = computed(() => ['#3B82F6', '#EC4899']);

// Calculate mean HbA1c by gender
const maleMean = computed(() => maleData.value.mean ? maleData.value.mean.toFixed(2) : 'N/A');
const femaleMean = computed(() => femaleData.value.mean ? femaleData.value.mean.toFixed(2) : 'N/A');

// Client-side parsing and classification (robust normalization)
const parseHba = (v: any) => {
    if (v === null || v === undefined) return NaN;
    let s = String(v).trim();
    s = s.replace(/%/g, '').replace(/,/g, '');
    const n = parseFloat(s);
    if (!Number.isFinite(n)) return NaN;
    return n > 0 && n <= 1 ? n * 100 : n;
};

const classifyRecords = (records: any) => {
    const counts = { normal: 0, prediabetic: 0, diabetic: 0 };
    if (!records) return counts;

    const rows = Array.isArray(records) ? records : Object.values(records || {});

    rows.forEach((r: any) => {
        const raw = r.ncd_hb1ac ?? r.hba1c ?? r.mean_hba1c ?? null;
        const value = parseHba(raw);
        if (!Number.isFinite(value)) return;
        if (value < 5.7) counts.normal++;
        else if (value >= 5.7 && value <= 6.4) counts.prediabetic++;
        else counts.diabetic++;
    });

    return counts;
};

const maleClassifications = computed(() => classifyRecords(maleData.value.records));
const femaleClassifications = computed(() => classifyRecords(femaleData.value.records));
</script>

<template>
    <Head title="HbA1c Gender Analysis" />
    
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
                                    <Users class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                        Gender Analysis
                                    </h1>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        Comparative HbA1c analysis by gender for {{ props.project.app_title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-2 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Total Participants</div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white">
                                    {{ totalCount }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gender Distribution Overview -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                        <!-- Gender Distribution Chart -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="mb-6">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                        <PieChart class="h-5 w-5 text-white" />
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Gender Distribution</h3>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-sm">
                                    Proportion of male and female participants in the study
                                </p>
                            </div>
                            <div class="h-64 relative">
                                <ApexDonutChart 
                                    :chart-data="genderDistributionData"
                                    :chart-labels="genderDistributionLabels"
                                    :chart-colors="genderDistributionColors"
                                    show-legend
                                />
                            </div>
                            <div class="mt-6 grid grid-cols-2 gap-4">
                                <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ maleCount }}
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-300">Male ({{ malePercentage }}%)</div>
                                </div>
                                <div class="text-center p-3 bg-pink-50 dark:bg-pink-900/20 rounded-xl">
                                    <div class="text-2xl font-bold text-pink-600 dark:text-pink-400">
                                        {{ femaleCount }}
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-300">Female ({{ femalePercentage }}%)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Gender Comparison Stats -->
                        <div class="col-span-1 lg:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Male Stats Card -->
                                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-2xl p-6 shadow-lg border border-blue-200 dark:border-blue-800/30">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                            <Users class="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Male Participants</h3>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">HbA1c statistics for male participants</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-600 dark:text-slate-300">Mean HbA1c</span>
                                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ maleMean }}%</span>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ maleClassifications.normal }}</div>
                                                <div class="text-xs text-slate-600 dark:text-slate-300">Normal</div>
                                            </div>
                                            <div class="text-center p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                                <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ maleClassifications.prediabetic }}</div>
                                                <div class="text-xs text-slate-600 dark:text-slate-300">Prediabetic</div>
                                            </div>
                                            <div class="text-center p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                                <div class="text-lg font-bold text-red-600 dark:text-red-400">{{ maleClassifications.diabetic }}</div>
                                                <div class="text-xs text-slate-600 dark:text-slate-300">Diabetic</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Female Stats Card -->
                                <div class="bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 rounded-2xl p-6 shadow-lg border border-pink-200 dark:border-pink-800/30">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-r from-pink-500 to-rose-500 rounded-xl flex items-center justify-center">
                                            <Users class="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Female Participants</h3>
                                            <p class="text-sm text-slate-600 dark:text-slate-300">HbA1c statistics for female participants</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-600 dark:text-slate-300">Mean HbA1c</span>
                                            <span class="text-lg font-bold text-pink-600 dark:text-pink-400">{{ femaleMean }}%</span>
                                        </div>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ femaleClassifications.normal }}</div>
                                                <div class="text-xs text-slate-600 dark:text-slate-300">Normal</div>
                                            </div>
                                            <div class="text-center p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                                <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ femaleClassifications.prediabetic }}</div>
                                                <div class="text-xs text-slate-600 dark:text-slate-300">Prediabetic</div>
                                            </div>
                                            <div class="text-center p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                                <div class="text-lg font-bold text-red-600 dark:text-red-400">{{ femaleClassifications.diabetic }}</div>
                                                <div class="text-xs text-slate-600 dark:text-slate-300">Diabetic</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gender Comparison Summary -->
                            <div class="mt-6 bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                                        <Scale class="h-5 w-5 text-white" />
                                    </div>
                                    <h4 class="font-bold text-slate-900 dark:text-white">Gender Comparison Summary</h4>
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    This analysis compares HbA1c levels between male and female participants to identify 
                                    potential gender-based differences in glycemic control and diabetes prevalence.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Gender Reports -->
                    <div class="mb-8">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Detailed Gender Reports</h3>
                            <p class="text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                                Comprehensive analysis of HbA1c data stratified by gender
                            </p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Male Analysis -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                            <Users class="h-5 w-5 text-white" />
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Male Analysis</h3>
                                            <p class="text-slate-600 dark:text-slate-300 text-sm">
                                                Detailed HbA1c statistics and distribution for male participants
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
                                    <GenderReports :gender-data="props.genderData" gender-type="Male" />
                                </div>
                            </div>

                            <!-- Female Analysis -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 bg-gradient-to-r from-pink-500 to-rose-500 rounded-xl flex items-center justify-center">
                                            <Users class="h-5 w-5 text-white" />
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Female Analysis</h3>
                                            <p class="text-slate-600 dark:text-slate-300 text-sm">
                                                Detailed HbA1c statistics and distribution for female participants
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-pink-50 dark:bg-pink-900/20 rounded-xl p-4">
                                    <GenderReports :gender-data="props.genderData" gender-type="Female" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Key Insights -->
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 rounded-2xl p-6 border border-red-200 dark:border-red-800/30">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                                <Activity class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Gender-Based Insights</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Clinical Significance</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Gender differences in HbA1c levels can indicate variations in diabetes risk, 
                                    metabolic patterns, and response to treatment between males and females.
                                </p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Research Implications</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Understanding gender-specific patterns helps in developing targeted interventions 
                                    and personalized diabetes management strategies.
                                </p>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Monitoring Considerations</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Regular gender-stratified analysis is important for identifying disparities 
                                    and ensuring equitable healthcare outcomes across different population groups.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Cards -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Link 
                            :href="route('packages.hba1c.alldata', [project.project_id])"
                            class="group relative block"
                        >
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                                        <Database class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white">All Data Analysis</h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300">Complete dataset overview</p>
                                    </div>
                                </div>
                            </div>
                        </Link>
                        
                        <Link 
                            :href="route('packages.hba1c.avgresp', [project.project_id])"
                            class="group relative block"
                        >
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                                        <Users class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white">Respondent Analysis</h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300">Individual participant data</p>
                                    </div>
                                </div>
                            </div>
                        </Link>
                        
                        <Link 
                            :href="route('packages.hba1c.facility', [project.project_id])"
                            class="group relative block"
                        >
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                                        <Building class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white">Facility Analysis</h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300">Site-based comparisons</p>
                                    </div>
                                </div>
                            </div>
                        </Link>
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
    
    .grid-cols-3 {
        grid-template-columns: 1fr;
    }
}
</style>

<script lang="ts">
// Import icons that might be used in the template
import { Database, Building } from 'lucide-vue-next';
</script>