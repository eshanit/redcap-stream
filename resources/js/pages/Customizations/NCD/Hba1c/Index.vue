<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { Database, Users, User, Building, Activity, TrendingUp, BarChart3, PieChart } from 'lucide-vue-next';

const props = defineProps<{
    project: IProject,
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
        href: ''
    }
];

// Card data with icons and colors
const analysisCards = [
    {
        id: 'alldata',
        title: 'All Data Outlook',
        description: 'Comprehensive statistical analysis of all HbA1c values including distribution, quartiles, and trends.',
        icon: Database,
        gradient: 'from-blue-500 to-cyan-500',
        route: 'packages.hba1c.alldata',
        features: ['Statistical breakdown', 'Distribution analysis', 'Trend visualization']
    },
    {
        id: 'respondent',
        title: 'Respondent Analysis',
        description: 'Individual respondent HbA1c levels compared against population metrics.',
        icon: Users,
        gradient: 'from-purple-500 to-pink-500',
        route: 'packages.hba1c.avgresp',
        features: ['Individual comparisons', 'Population metrics', 'Response patterns']
    },
    {
        id: 'gender',
        title: 'Gender Analysis',
        description: 'Comparative analysis of HbA1c levels between male and female participants.',
        icon: User,
        gradient: 'from-green-500 to-emerald-500',
        route: 'packages.hba1c.gender',
        features: ['Gender comparisons', 'Demographic trends', 'Statistical significance']
    },
    {
        id: 'facility',
        title: 'Facility Analysis',
        description: 'HbA1c level comparisons across different collection facilities.',
        icon: Building,
        gradient: 'from-orange-500 to-red-500',
        route: 'packages.hba1c.facility',
        features: ['Facility comparisons', 'Geographic patterns', 'Quality metrics']
    }
];
</script>

<template>
    <Head title="HbA1c Analysis Dashboard" />
    
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
                                        HbA1c Analysis
                                    </h1>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        {{ props.project.app_title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-2 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Analysis Types</div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white">
                                    4
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="max-w-3xl mb-8">
                        <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed">
                            Comprehensive analysis of hemoglobin A1c levels across multiple dimensions. 
                            Explore statistical distributions, demographic comparisons, and facility-based 
                            variations to gain insights into diabetes management and outcomes.
                        </p>
                    </div>

                    <!-- Analysis Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Link 
                            v-for="card in analysisCards"
                            :key="card.id"
                            :href="route(card.route, [project.project_id])"
                            class="group relative block"
                        >
                            <div class="h-full bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:border-slate-300 dark:hover:border-slate-600">
                                <!-- Card Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div :class="`bg-gradient-to-r ${card.gradient} w-12 h-12 rounded-xl flex items-center justify-center`">
                                            <component 
                                                :is="card.icon"
                                                class="h-6 w-6 text-white"
                                            />
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors duration-300">
                                                {{ card.title }}
                                            </h3>
                                            <div class="inline-flex items-center px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-xs text-slate-600 dark:text-slate-300 mt-1">
                                                Advanced Analysis
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
                                <div class="mb-4">
                                    <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">
                                        {{ card.description }}
                                    </p>
                                </div>

                                <!-- Features -->
                                <div class="mb-6">
                                    <div class="flex flex-wrap gap-2">
                                        <span 
                                            v-for="feature in card.features"
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

                    <!-- Quick Stats Section -->
                    <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-700">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Analysis Capabilities</h3>
                            <p class="text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                                Each analysis type provides unique insights into HbA1c data
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 text-center">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                                    <BarChart3 class="h-6 w-6 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Statistical Analysis</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Mean, median, quartiles, and distribution analysis
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 text-center">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                                    <Users class="h-6 w-6 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Demographic Insights</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Gender, age group, and participant comparisons
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 text-center">
                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                                    <PieChart class="h-6 w-6 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Visualization</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Charts, graphs, and interactive data visualizations
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 text-center">
                                <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                                    <TrendingUp class="h-6 w-6 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Trend Analysis</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Time-based trends and pattern recognition
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Context -->
                    <div class="mt-12 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 rounded-2xl p-6 border border-red-200 dark:border-red-800/30">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-lg flex items-center justify-center">
                                <Activity class="h-5 w-5 text-white" />
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">About HbA1c Analysis</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-slate-600 dark:text-slate-300 mb-3">
                                    Hemoglobin A1c (HbA1c) is a critical marker for long-term glucose control in diabetes management. 
                                    This analysis provides insights into glycemic control patterns across your study population.
                                </p>
                                <p class="text-slate-600 dark:text-slate-300">
                                    Target ranges: Normal &lt;5.7%, Prediabetes 5.7-6.4%, Diabetes ≥6.5%
                                </p>
                            </div>
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4">
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Key Clinical Indicators</h4>
                                <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                        Mean HbA1c levels
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        Target achievement rates
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        Demographic variations
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                        Facility performance
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

/* Custom hover effects */
.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}
</style>