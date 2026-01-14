<script setup lang='ts'>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Sparkles, Package, BarChart3, FileText, TrendingUp, Zap } from 'lucide-vue-next';

const props = defineProps<{
    project: IProject,
    customProjectData: any[]
}>()

const packageRoot = computed(() => {
    if (props.project.project_id == 39) {
        return 'ncd_pplus'
    }
    if (props.project.project_id == 50) {
        return 'ahp'
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
        href: ''
    }
];

// Helper function to get icon based on package name
const getPackageIcon = (packageName: string) => {
    const name = packageName.toLowerCase();
    if (name.includes('report') || name.includes('analytics')) return BarChart3;
    if (name.includes('export') || name.includes('data')) return FileText;
    if (name.includes('trend') || name.includes('insight')) return TrendingUp;
    if (name.includes('quick') || name.includes('fast')) return Zap;
    return Package;
};

// Helper function to get gradient colors based on index
const getGradientColors = (index: number) => {
    const gradients = [
        'from-blue-500 to-cyan-500',
        'from-purple-500 to-pink-500',
        'from-green-500 to-emerald-500',
        'from-orange-500 to-red-500',
        'from-indigo-500 to-purple-500',
        'from-cyan-500 to-blue-500',
    ];
    return gradients[index % gradients.length];
};

// Helper function to get background colors based on index
const getBgColors = (index: number) => {
    const bgs = [
        'bg-blue-50 dark:bg-blue-900/20',
        'bg-purple-50 dark:bg-purple-900/20',
        'bg-green-50 dark:bg-green-900/20',
        'bg-orange-50 dark:bg-orange-900/20',
        'bg-indigo-50 dark:bg-indigo-900/20',
        'bg-cyan-50 dark:bg-cyan-900/20',
    ];
    return bgs[index % bgs.length];
};

// Helper function to get package href
const getPackageHref = (customProject: any) => {
    if (customProject.customization_name === 'Review Appointments') {
        return route('packages.payment', [props.project.project_id]);
    }
    return `/packages/project/${props.project.project_id}/${customProject.path}`;
};
</script>

<template>
    <Head :title="`${props.project.app_title} - Custom Packages`" />
    
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
                                    <Sparkles class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                        Custom Packages
                                    </h1>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        {{ props.project.app_title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-2 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="text-sm text-slate-500 dark:text-slate-400">Packages</div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white">
                                    {{ props.customProjectData.length }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="max-w-3xl mb-8">
                        <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed">
                            Explore specialized analysis packages and automated reports tailored for your research project. 
                            Each package offers unique insights and visualization tools to accelerate your data analysis.
                        </p>
                    </div>

                    <!-- Packages Grid -->
                    <div v-if="props.customProjectData.length > 0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <Link
                                v-for="(customProject, index) in customProjectData"
                                :key="customProject.path"
                                :href="getPackageHref(customProject)"
                                class="group relative block"
                            >
                                <div class="h-full bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:border-slate-300 dark:hover:border-slate-600">
                                    <!-- Icon and Header -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div :class="`${getBgColors(index)} w-12 h-12 rounded-xl flex items-center justify-center`">
                                                <component 
                                                    :is="getPackageIcon(customProject.customization_name)"
                                                    :class="`h-6 w-6 text-slate-700 dark:text-slate-300`"
                                                />
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors duration-300">
                                                    {{ customProject.customization_name }}
                                                </h3>
                                                <div class="inline-flex items-center px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-xs text-slate-600 dark:text-slate-300 mt-1">
                                                    <Sparkles class="h-3 w-3 mr-1" />
                                                    Custom Package
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
                                        <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed line-clamp-3">
                                           <span v-html="customProject.description || 'Detailed analysis and reports for this research project.'"></span>
                                        </p>
                                    </div>

                                    <!-- Stats or Additional Info -->
                                    <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                                        <div class="text-sm text-slate-500 dark:text-slate-400">
                                            {{ customProject.report_count || '0' }} reports
                                        </div>
                                        <div class="text-xs px-2 py-1 rounded-full bg-gradient-to-r from-red-500/10 to-orange-500/10 text-red-600 dark:text-red-400 font-medium">
                                            View Details
                                        </div>
                                    </div>

                                    <!-- Gradient Hover Effect -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-red-500/0 via-orange-500/0 to-red-500/0 group-hover:from-red-500/5 group-hover:via-orange-500/5 group-hover:to-red-500/5 transition-all duration-500 opacity-0 group-hover:opacity-100 rounded-2xl"></div>
                                </div>
                            </Link>
                        </div>

                        <!-- Empty slot for new package -->
                        <div class="mt-8">
                            <Link 
                                :href="route('packages.request', [project.project_id])"
                                class="group relative block"
                            >
                                <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-red-400 dark:hover:border-red-500 rounded-2xl p-8 text-center transition-all duration-300 hover:scale-[1.02] hover:bg-white/50 dark:hover:bg-slate-800/50">
                                    <div class="w-16 h-16 mx-auto bg-gradient-to-r from-red-500/10 to-orange-500/10 rounded-full flex items-center justify-center mb-4 group-hover:from-red-500/20 group-hover:to-orange-500/20 transition-all duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">Request New Package</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                                        Build a custom analysis package for your research
                                    </p>
                                    <div class="text-red-500 text-sm font-medium inline-flex items-center gap-1">
                                        Get Started
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-red-500/0 via-orange-500/0 to-red-500/0 group-hover:from-red-500/5 group-hover:via-orange-500/5 group-hover:to-red-500/5 transition-all duration-500 opacity-0 group-hover:opacity-100 rounded-2xl"></div>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-16 text-center">
                        <div class="max-w-md mx-auto">
                            <div class="w-24 h-24 mx-auto bg-gradient-to-r from-red-100 to-orange-100 dark:from-red-900/20 dark:to-orange-900/20 rounded-full flex items-center justify-center mb-6">
                                <Sparkles class="h-12 w-12 text-red-500" />
                            </div>
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                                No Custom Packages Yet
                            </h3>
                            <p class="text-slate-600 dark:text-slate-300 mb-8 max-w-sm mx-auto">
                                You haven't created any custom packages for this project yet. Start by creating your first package to unlock specialized analysis tools.
                            </p>
                            <Link 
                                :href="route('packages.create', [project.project_id])"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-red-500 to-orange-500 text-white font-medium hover:shadow-lg transition-all duration-300 hover:scale-105"
                            >
                                <Sparkles class="h-5 w-5" />
                                Create Your First Package
                            </Link>
                        </div>
                    </div>

                    <!-- Feature Highlights -->
                    <div class="mt-16 pt-8 border-t border-slate-200 dark:border-slate-700">
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Package Features</h3>
                            <p class="text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                                Each custom package includes powerful tools to enhance your research analysis
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center mb-4">
                                    <BarChart3 class="h-5 w-5 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Advanced Analytics</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Comprehensive statistical analysis and data visualization tools
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mb-4">
                                    <FileText class="h-5 w-5 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Automated Reports</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Generate scheduled reports with customizable templates and formats
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mb-4">
                                    <TrendingUp class="h-5 w-5 text-white" />
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white mb-2">Trend Analysis</h4>
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    Track patterns and trends over time with predictive insights
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
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

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