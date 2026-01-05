<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { Users, UserPlus, Building, TrendingUp, BarChart3, PieChart, Calendar, Activity, Target } from 'lucide-vue-next';
import ApexPieChart from '@/components/charts/apexcharts/ApexPieChart.vue';
import ApexBarChart from '@/components/charts/apexcharts/ncd/ApexBarChart.vue';
import EnrollQuarterChart from '@/components/charts/apexcharts/EnrollQuarterChart.vue';
import EnrolledTable from '@/components/tables/agtables/ncdpplus/coreinsights/Enrolled.vue';
import { computed } from 'vue';

const props = defineProps<{
    project: IProject,
    respondentCount: number,
    respondentGender: any,
    respondentFacility: any,
    enrolled: any[],
    lostToFollowUp: number
}>();

const packageRoot = computed(() => {
  if (props.project.project_id == 39) {
    return 'ncd_pplus'
  }
  return 'ncd'
})

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
    href: '/' + packageRoot.value + '/project/' + props.project.project_id,
  },
  {
    title: 'Custom Packages',
    href: '/packages/project/' + props.project.project_id + '/dashboard',
  },
  {
    title: 'Core Indicators',
    href: '/packages/project/' + props.project.project_id + '/core_indicators',
  },
  {
    title: 'Enrollment Analysis',
    href: ''
  }
];

// Calculate gender distribution for charts
const genderDataForChart = computed(() => {
  const male = props.respondentGender?.Male || 0;
  const female = props.respondentGender?.Female || 0;
  return [male, female];
});

const genderLabelsForChart = computed(() => ['Male', 'Female']);
const genderColors = computed(() => ['#3B82F6', '#EC4899']);

// Calculate total enrolled
const totalEnrolled = computed(() => {
  const male = props.respondentGender?.Male || 0;
  const female = props.respondentGender?.Female || 0;
  return male + female;
});

// Calculate facility count
const facilityCount = computed(() => Object.keys(props.respondentFacility || {}).length);
</script>

<template>
    <Head title="Enrollment Analysis" />
    
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
                                    <UserPlus class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                        Enrollment Analysis
                                    </h1>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        Comprehensive enrollment metrics for {{ props.project.app_title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-2 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Total Enrolled</div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white">
                                    {{ totalEnrolled }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Gender Distribution</p>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white mt-1">
                                        {{ respondentGender?.Male || 0 }} Male / {{ respondentGender?.Female || 0 }} Female
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                    <Users class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Breakdown by gender
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Facilities</p>
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">
                                        {{ facilityCount }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                    <Building class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Participating centers
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Enrollment Rate</p>
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">
                                        {{ totalEnrolled }}
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                                    <TrendingUp class="h-6 w-6 text-white" />
                                </div>
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                Total ever enrolled
                            </div>
                        </div>
                    </div>

                    <!-- Main Analysis Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 mb-8">
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                        <UserPlus class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Ever Enrolled</h2>
                                        <p class="text-slate-600 dark:text-slate-300 text-sm">
                                            Respondents enrolled at Pen Plus Clinics
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gender and Facility Charts -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                            <!-- Gender Distribution Card -->
                            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-6">
                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                            <PieChart class="h-5 w-5 text-white" />
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Respondents Enrolled by Gender</h3>
                                    </div>
                                    <p class="text-slate-600 dark:text-slate-300 text-sm">
                                        Number of respondents broken down by gender
                                    </p>
                                </div>
                                
                                <!-- Apex Pie Chart -->
                                <div class="h-64 relative">
                                    <ApexPieChart 
                                        :chart-data="genderDataForChart"
                                        :chart-labels="genderLabelsForChart"
                                        :chart-colors="genderColors"
                                        show-legend
                                    />
                                </div>
                                
                                <!-- Gender Stats -->
                                <div class="mt-6 grid grid-cols-2 gap-4">
                                    <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                            {{ respondentGender?.Male || 0 }}
                                        </div>
                                        <div class="text-sm text-slate-600 dark:text-slate-300">Male</div>
                                    </div>
                                    <div class="text-center p-3 bg-pink-50 dark:bg-pink-900/20 rounded-xl">
                                        <div class="text-2xl font-bold text-pink-600 dark:text-pink-400">
                                            {{ respondentGender?.Female || 0 }}
                                        </div>
                                        <div class="text-sm text-slate-600 dark:text-slate-300">Female</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Facility Distribution Card -->
                            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-6">
                                <div class="mb-6">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                            <Building class="h-5 w-5 text-white" />
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Respondents by Facility</h3>
                                    </div>
                                    <p class="text-slate-600 dark:text-slate-300 text-sm">
                                        Number of respondents broken down by facility
                                    </p>
                                </div>
                                <div class="h-64 relative">
                                    <ApexBarChart 
                                        :chart-data="props.respondentFacility"
                                        :chart-colors="['#8B5CF6', '#EC4899', '#3B82F6', '#10B981', '#F59E0B']"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Quarterly Enrollment Chart -->
                        <div class="mb-8">
                            <div class="mb-6">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                                        <Calendar class="h-5 w-5 text-white" />
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Quarterly Enrollment Trends</h3>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-sm">
                                    Enrollment patterns over time by quarter
                                </p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4">
                                <EnrollQuarterChart :enrolled-data="props.enrolled" />
                            </div>
                        </div>

                        <!-- Enrolled Respondents Table -->
                        <div class="mb-8">
                            <div class="mb-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center">
                                            <Users class="h-5 w-5 text-white" />
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">All Enrolled Respondents</h3>
                                            <p class="text-slate-600 dark:text-slate-300 text-sm">
                                                Below is a list of all the registered/enrolled respondents
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ props.enrolled?.length || 0 }} records
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                                <EnrolledTable :all-results="props.enrolled" />
                            </div>
                        </div>
                    </div>

                    <!-- Enrollment Insights -->
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 rounded-2xl p-6 border border-red-200 dark:border-red-800/30 mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                                <Target class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Enrollment Insights</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Program Growth</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    The enrollment analysis provides critical insights into program reach and effectiveness. 
                                    Tracking enrollment patterns helps identify successful outreach strategies and areas needing improvement.
                                </p>
                            </div>
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4">
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Key Metrics</h4>
                                <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        Gender balance in enrollment
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        Facility performance comparison
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        Quarterly enrollment trends
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                        Geographic distribution
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <Link 
                            :href="route('packages.core.pplusactive.patients', [project.project_id])"
                            class="group relative block"
                        >
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                                        <Activity class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white">Active Patients</h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300">Current patient status</p>
                                    </div>
                                </div>
                            </div>
                        </Link>
                        
                        <Link 
                            :href="route('packages.core.pplusltfu.patients', [project.project_id])"
                            class="group relative block"
                        >
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                                        <AlertCircle class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white">LTFU Analysis</h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300">Lost to follow-up</p>
                                    </div>
                                </div>
                            </div>
                        </Link>
                        
                        <Link 
                            :href="route('packages.core.pplusmortality.patients', [project.project_id])"
                            class="group relative block"
                        >
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                                        <Heart class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white">Mortality</h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300">Mortality analysis</p>
                                    </div>
                                </div>
                            </div>
                        </Link>
                        
                        <!-- <Link 
                            :href="route('packages.core.core_indicators', [project.project_id])"
                            class="group relative block"
                        >
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                                        <BarChart3 class="h-5 w-5 text-white" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white">Core Indicators</h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300">All indicators</p>
                                    </div>
                                </div>
                            </div>
                        </Link> -->
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
    
    .grid-cols-4 {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 640px) {
    .grid-cols-4 {
        grid-template-columns: 1fr;
    }
}
</style>

<script lang="ts">
// Import icons that might be used in the template
import { AlertCircle, Heart } from 'lucide-vue-next';
</script>