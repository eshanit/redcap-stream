<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { Users, CalendarX2, PackageOpen, Binoculars, BarChart3, PieChart } from 'lucide-vue-next';
import ApexDonutChart from '@/components/charts/apexcharts/ncd/ApexDonutChart.vue';
import ApexBarChart from '@/components/charts/apexcharts/ncd/ApexBarChart.vue';

const props = defineProps<{
    project: IProject,
    respondentCount: number,
    respondentGender: any,
    respondentFacility: any,
    lostToFollowUp: number
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/dashboard',
    },
    {
        title: props.project.app_title,
        href: '#',
    },
];

// Calculate percentage for lost to follow up
const lostToFollowUpPercentage = props.respondentCount > 0 
    ? ((props.lostToFollowUp / props.respondentCount) * 100).toFixed(2)
    : '0.00';

// Prepare gender data for ApexCharts
const genderData = computed(() => [
    props.respondentGender[1] || 0,
    props.respondentGender[2] || 0
]);

const genderLabels = computed(() => ['Male', 'Female']);

// Prepare facility data for ApexCharts
const facilityData = computed(() => props.respondentFacility || {});
</script>

<template>
    <Head :title="`${props.project.app_title} - Dashboard`" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-slate-900 dark:to-slate-800">
            <!-- Page Header -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                    {{ props.project.app_title }}
                                </h1>
                            </div>
                            <p class="text-slate-600 dark:text-slate-300">
                                Project ID: {{ props.project.project_id }}
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <Link
                                :href="route('packages.dashboard', [project.project_id])"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-orange-500 text-white font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 inline-flex items-center gap-2"
                            >
                                <PackageOpen class="h-4 w-4" />
                                Custom Reports
                            </Link>
                        </div>
                    </div>

                    <!-- Project Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Total Respondents Card -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Total Respondents</p>
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">
                                        {{ respondentCount }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                                    <Users class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Active participants in study
                            </div>
                        </div>

                        <!-- Lost to Follow Up Card -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Lost to Follow Up</p>
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">
                                        {{ lostToFollowUp }}
                                        <span class="text-lg text-red-500 ml-2">
                                            ({{ lostToFollowUpPercentage }}%)
                                        </span>
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                    <CalendarX2 class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Missed review by 60+ days
                            </div>
                        </div>

                        <!-- Gender Distribution Card -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Gender Distribution</p>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white mt-1">
                                        {{ respondentGender[1] || 0 }} Male / {{ respondentGender[2] || 0 }} Female
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                    <PieChart class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Breakdown by gender
                            </div>
                        </div>

                        <!-- Facilities Count Card -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Facilities</p>
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">
                                        {{ Object.keys(respondentFacility || {}).length }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                    <BarChart3 class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Participating centers
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
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
                                    Breakdown of respondents by gender across the study
                                </p>
                            </div>
                            
                            <!-- Apex Donut Chart -->
                            <div class="h-64 relative">
                                <ApexDonutChart 
                                    :chart-data="genderData"
                                    :chart-labels="genderLabels"
                                    :chart-colors="['#3B82F6', '#EC4899']"
                                    show-legend
                                />
                            </div>
                            
                            <!-- Quick stats below chart -->
                            <div class="mt-6 grid grid-cols-2 gap-4">
                                <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ respondentGender[1] || 0 }}
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-300">Male</div>
                                </div>
                                <div class="text-center p-3 bg-pink-50 dark:bg-pink-900/20 rounded-xl">
                                    <div class="text-2xl font-bold text-pink-600 dark:text-pink-400">
                                        {{ respondentGender[2] || 0 }}
                                    </div>
                                    <div class="text-sm text-slate-600 dark:text-slate-300">Female</div>
                                </div>
                            </div>
                        </div>

                        <!-- Facility Distribution Chart -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="mb-6">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                        <BarChart3 class="h-5 w-5 text-white" />
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Facility Distribution</h3>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-sm">
                                    Number of respondents per participating facility
                                </p>
                            </div>
                            <div class="h-64 relative">
                                <ApexBarChart 
                                    :chart-data="facilityData"
                                    :chart-colors="['#8B5CF6', '#EC4899', '#3B82F6', '#10B981', '#F59E0B']"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Customized Packages Card -->
                        <div class="bg-gradient-to-br from-white to-red-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-start justify-between mb-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                            <PackageOpen class="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Customized Packages</h3>
                                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                                                Advanced analysis and reports
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        Access specialized analysis packages and automated reports tailored for 
                                        <span class="font-semibold text-slate-900 dark:text-white">{{ props.project.app_title }}</span>.
                                        Generate comprehensive insights with one click.
                                    </p>
                                </div>
                            </div>
                            <Link 
                                :href="route('packages.dashboard', [project.project_id])"
                                class="inline-flex items-center justify-center gap-2 w-full px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-medium hover:shadow-lg transition-all duration-300 hover:scale-[1.02]"
                            >
                                <PackageOpen class="h-5 w-5" />
                                Go to Customized Reports
                            </Link>
                        </div>

                        <!-- Questionnaire Insights Card -->
                        <div class="bg-gradient-to-br from-white to-orange-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-start justify-between mb-6">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                                            <Binoculars class="h-6 w-6 text-white" />
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Questionnaire Insights</h3>
                                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                                                Detailed field analysis
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        Dive deep into individual questionnaire items for 
                                        <span class="font-semibold text-slate-900 dark:text-white">{{ props.project.app_title }}</span>.
                                        Analyze response patterns, distributions, and correlations.
                                    </p>
                                </div>
                            </div>
                            <Link 
                                :href="route(project.project_name+'.questionnaire.dashboard', [project.project_id])"
                                class="inline-flex items-center justify-center gap-2 w-full px-6 py-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 text-white font-medium hover:shadow-lg transition-all duration-300 hover:scale-[1.02]"
                            >
                                <Binoculars class="h-5 w-5" />
                                Go to Questionnaire Overview
                            </Link>
                        </div>
                    </div>

                    <!-- Project Information Footer -->
                    <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-700">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Project ID</div>
                                <div class="text-lg font-semibold text-slate-900 dark:text-white">{{ props.project.project_id }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Creation Date</div>
                                <div class="text-lg font-semibold text-slate-900 dark:text-white">{{ props.project.creation_time }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Last Updated</div>
                                <div class="text-lg font-semibold text-slate-900 dark:text-white">Recent</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom styles for better chart integration */
:deep(.apexcharts-canvas) {
    width: 100% !important;
}

/* Ensure charts are responsive */
@media (max-width: 768px) {
    .h-64 {
        height: 250px;
    }
}
</style>