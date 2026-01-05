<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { Users, UserPlus, Calendar, Activity, TrendingUp, AlertCircle, Heart, Clock, BarChart3 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    project: IProject,
}>()

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
    href: ''
  }
];

// Core indicators data
const coreIndicators = [
  {
    id: 'enrollment',
    title: 'Enrollment Analysis',
    description: 'Comprehensive enrollment analysis for Pen Plus respondents',
    icon: UserPlus,
    gradient: 'from-blue-500 to-cyan-500',
    route: 'packages.core.enrollment',
    features: ['Enrollment trends', 'Demographic breakdown', 'Screening metrics']
  },
  {
    id: 'active-patients',
    title: 'Active Patients',
    description: 'Patients currently active at Pen Plus Clinic',
    icon: Users,
    gradient: 'from-green-500 to-emerald-500',
    route: 'packages.core.pplusactive.patients',
    features: ['Active patient count', 'Retention rates', 'Visit patterns']
  },
  {
    id: 'ltfu',
    title: 'Lost to Follow-Up',
    description: 'Analysis of patients lost to follow-up in the program',
    icon: AlertCircle,
    gradient: 'from-red-500 to-orange-500',
    route: 'packages.core.pplusltfu.patients',
    features: ['LTFU rates', 'Risk factors', 'Intervention tracking']
  },
  {
    id: 'mortality',
    title: 'Mortality Analysis',
    description: 'Comprehensive mortality analysis and trend monitoring',
    icon: Heart,
    gradient: 'from-purple-500 to-pink-500',
    route: 'packages.core.pplusmortality.patients',
    features: ['Mortality rates', 'Cause analysis', 'Trend monitoring']
  }
];

// Clinical metrics summary (you can populate this with actual data)
const clinicalMetrics = [
  { label: 'Total Enrolled', value: '1,245', change: '+12%', color: 'blue' },
  { label: 'Active Patients', value: '892', change: '+8%', color: 'green' },
  { label: 'LTFU Rate', value: '7.2%', change: '-2%', color: 'red' },
  { label: 'Mortality Rate', value: '3.1%', change: '-1%', color: 'purple' }
];
</script>

<template>
    <Head title="Core Indicators Dashboard" />
    
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
                                    <Activity class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                        Core Indicators
                                    </h1>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        Comprehensive analysis of program performance metrics for {{ props.project.app_title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-2 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Key Metrics</div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white">
                                    4
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Metrics Summary -->
                    <!-- <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div v-for="metric in clinicalMetrics" :key="metric.label" 
                             class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-slate-500 dark:text-slate-400">{{ metric.label }}</div>
                                <div :class="[
                                    'text-xs font-medium px-2 py-1 rounded-full',
                                    metric.change.startsWith('+') ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' :
                                    'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400'
                                ]">
                                    {{ metric.change }}
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ metric.value }}</div>
                        </div>
                    </div> -->

                    <!-- Core Indicators Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <Link 
                            v-for="indicator in coreIndicators"
                            :key="indicator.id"
                            :href="route(indicator.route, [project.project_id])"
                            class="group relative block"
                        >
                            <div class="h-full bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:border-slate-300 dark:hover:border-slate-600">
                                <!-- Indicator Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div :class="`bg-gradient-to-r ${indicator.gradient} w-12 h-12 rounded-xl flex items-center justify-center`">
                                            <component 
                                                :is="indicator.icon"
                                                class="h-6 w-6 text-white"
                                            />
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors duration-300">
                                                {{ indicator.title }}
                                            </h3>
                                            <div class="inline-flex items-center px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-xs text-slate-600 dark:text-slate-300 mt-1">
                                                <Clock class="h-3 w-3 mr-1" />
                                                Core Indicator
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Indicator -->
                                    <div class="text-slate-400 group-hover:text-red-500 transition-colors duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-6">
                                    <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">
                                        {{ indicator.description }}
                                    </p>
                                </div>

                                <!-- Features -->
                                <div class="mb-6">
                                    <div class="flex flex-wrap gap-2">
                                        <span 
                                            v-for="feature in indicator.features"
                                            :key="feature"
                                            class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-xs text-slate-600 dark:text-slate-300 rounded-full"
                                        >
                                            {{ feature }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Button -->
                                <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                                    <div class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-700 dark:to-slate-800 text-slate-700 dark:text-slate-300 font-medium group-hover:bg-gradient-to-r group-hover:from-red-500 group-hover:to-orange-500 group-hover:text-white transition-all duration-300">
                                        View Analysis
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Gradient Hover Effect -->
                                <div class="absolute inset-0 bg-gradient-to-r from-red-500/0 via-orange-500/0 to-red-500/0 group-hover:from-red-500/5 group-hover:via-orange-500/5 group-hover:to-red-500/5 transition-all duration-500 opacity-0 group-hover:opacity-100 rounded-2xl"></div>
                            </div>
                        </Link>
                    </div>

                    <!-- Program Performance Summary -->
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 rounded-2xl p-6 border border-red-200 dark:border-red-800/30 mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                                <TrendingUp class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">Program Performance Overview</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-3">Key Performance Indicators</h4>
                                <ul class="space-y-2 text-slate-600 dark:text-slate-300">
                                    <li class="flex items-start gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                        <span><strong>Enrollment:</strong> Tracks new patient registration and program uptake</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                                        <span><strong>Active Patients:</strong> Monitors ongoing engagement and retention</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <div class="w-2 h-2 bg-red-500 rounded-full mt-2 flex-shrink-0"></div>
                                        <span><strong>LTFU:</strong> Identifies dropouts and intervention opportunities</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 flex-shrink-0"></div>
                                        <span><strong>Mortality:</strong> Tracks outcomes and program effectiveness</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4">
                                <h4 class="font-bold text-slate-900 dark:text-white mb-3">Monitoring Recommendations</h4>
                                <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        Review enrollment trends monthly
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        Monitor active patient retention weekly
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                        Investigate LTFU cases within 30 days
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        Analyze mortality patterns quarterly
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Resources -->
                    <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-700">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Additional Resources</h3>
                            <p class="text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                                Explore additional analysis tools and reports for comprehensive program evaluation
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center mb-4">
                                    <BarChart3 class="h-5 w-5 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Trend Analysis</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Long-term performance trends and predictive analytics for program optimization
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mb-4">
                                    <Users class="h-5 w-5 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Demographic Reports</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Patient demographic analysis including age, gender, and geographic distribution
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mb-4">
                                    <Calendar class="h-5 w-5 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Temporal Analysis</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Seasonal patterns, time-based trends, and temporal correlation analysis
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
/* Smooth transitions */
* {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid {
        gap: 1rem;
    }
}
</style>