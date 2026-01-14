<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { Lock, CreditCard, CheckCircle, XCircle, ArrowLeft, ExternalLink, Shield } from 'lucide-vue-next';

const props = defineProps<{
    project: IProject;
    package: {
        id: number;
        name: string;
        description: string;
        price?: number;
        currency?: string;
        features?: string[];
        trial_days?: number;
    };
}>();

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
        href: '/' + (props.project.project_id == 39 ? 'ncd_pplus' : props.project.project_id == 50 ? 'ahp' : 'ncd') + '/project/' + props.project.project_id,
    },
    {
        title: 'Custom Packages',
        href: route('packages.dashboard', [props.project.project_id]),
    },
    {
        title: props.package.name,
        href: ''
    }
];

const features = props.package.features || [
    'Advanced data analysis tools',
    'Custom report generation',
    'Priority support',
    'Export capabilities',
    'API access'
];
</script>

<template>
    <Head :title="`${props.project.app_title} - ${props.package.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 dark:from-slate-900 dark:to-slate-800">
            <!-- Page Header -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center">
                                    <Lock class="h-5 w-5 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                        {{ props.package.name }}
                                    </h1>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        {{ props.project.app_title }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <Link
                                :href="route('packages.dashboard', [project.project_id])"
                                class="px-5 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600 transition-all duration-300 hover:scale-105 inline-flex items-center gap-2"
                            >
                                <ArrowLeft class="h-4 w-4" />
                                Back to Packages
                            </Link>
                        </div>
                    </div>

                    <!-- Payment Required Message -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Main Content -->
                        <div class="lg:col-span-2 space-y-8">
                            <!-- Premium Banner -->
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3">
                                            <Shield class="h-6 w-6" />
                                            <h2 class="text-xl font-bold">Premium Package</h2>
                                        </div>
                                        <p class="text-blue-100">
                                            Full access requires a one-time payment
                                        </p>
                                    </div>
                                    <div v-if="props.package.price" class="text-right">
                                        <div class="text-3xl font-bold">
                                            {{ props.package.currency || '$' }}{{ props.package.price }}
                                        </div>
                                        <p class="text-sm text-blue-100">One-time payment</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Card -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-lg border border-slate-200 dark:border-slate-700">
                                <div class="flex items-start gap-4 mb-6">
                                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-100 to-orange-100 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-full flex items-center justify-center flex-shrink-0">
                                        <Lock class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">
                                            Payment Required for Full Access
                                        </h3>
                                        <p class="text-slate-600 dark:text-slate-300">
                                            You're currently viewing a limited preview of this package. To unlock all features and tools, a one-time payment is required.
                                        </p>
                                    </div>
                                </div>

                                <!-- Limited Access Warning -->
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-5 mb-6">
                                    <div class="flex items-start gap-3">
                                        <XCircle class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2">
                                                Current Limitations
                                            </h4>
                                            <ul class="space-y-2 text-yellow-700 dark:text-yellow-400">
                                                <li class="flex items-center gap-2">
                                                    <div class="w-1.5 h-1.5 bg-yellow-400 rounded-full"></div>
                                                    <span>Only partial data preview available</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <div class="w-1.5 h-1.5 bg-yellow-400 rounded-full"></div>
                                                    <span>Export and download features disabled</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <div class="w-1.5 h-1.5 bg-yellow-400 rounded-full"></div>
                                                    <span>Advanced analysis tools locked</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Package Description -->
                                <div class="mb-8">
                                    <h4 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                                        What's included in this package:
                                    </h4>
                                    <p class="text-slate-600 dark:text-slate-300 mb-6">
                                        {{ props.package.description || 'This package includes advanced analysis tools and reporting features designed specifically for your research needs.' }}
                                    </p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div v-for="(feature, index) in features" :key="index" 
                                             class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                                            <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0" />
                                            <span class="text-slate-700 dark:text-slate-300">{{ feature }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Options -->
                                <div class="space-y-6">
                                    <h4 class="text-lg font-semibold text-slate-900 dark:text-white">
                                        Get Full Access Now
                                    </h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Pay Now Button -->
                                        <Link
                                            :href="route('packages.dashboard', [project.project_id, package.id])"
                                            class="group p-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl text-white text-center transition-all duration-300 hover:shadow-lg hover:scale-[1.02]"
                                        >
                                            <div class="flex items-center justify-center gap-3">
                                                <CreditCard class="h-5 w-5" />
                                                <div class="text-left">
                                                    <div class="font-bold text-lg">Pay Now</div>
                                                    <div v-if="props.package.price" class="text-sm text-blue-200">
                                                        {{ props.package.currency || '$' }}{{ props.package.price }} • One-time payment
                                                    </div>
                                                </div>
                                            </div>
                                        </Link>

                                        <!-- Request Quote -->
                                        <Link
                                            :href="route('packages.request', [project.project_id])"
                                            class="group p-4 bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 rounded-xl text-white text-center transition-all duration-300 hover:shadow-lg hover:scale-[1.02]"
                                        >
                                            <div class="flex items-center justify-center gap-3">
                                                <ExternalLink class="h-5 w-5" />
                                                <div class="text-left">
                                                    <div class="font-bold text-lg">Request Quote</div>
                                                    <div class="text-sm text-slate-300">
                                                        Custom pricing options
                                                    </div>
                                                </div>
                                            </div>
                                        </Link>
                                    </div>

                                    <!-- Trial Option (if available) -->
                                    <div v-if="props.package.trial_days" 
                                         class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-5">
                                        <div class="flex items-start gap-3">
                                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" />
                                            <div>
                                                <h4 class="font-semibold text-green-800 dark:text-green-300 mb-2">
                                                    Free Trial Available
                                                </h4>
                                                <p class="text-green-700 dark:text-green-400">
                                                    Try this package free for 14 days. No commitment required.
                                                </p>
                                                <Link
                                                    :href="route('packages.request', [project.project_id])"
                                                    class="inline-flex items-center gap-2 mt-3 text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium"
                                                >
                                                    Contact us
                                                    <ExternalLink class="h-4 w-4" />
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Support Info -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                <h4 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                                    Need Help?
                                </h4>
                                <div class="space-y-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <CreditCard class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                Payment Questions
                                            </p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                                Contact our billing team
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <Shield class="h-4 w-4 text-green-600 dark:text-green-400" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-700 dark:text-slate-300 mb-1">
                                                Secure Payment
                                            </p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                                256-bit SSL encryption
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Benefits -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-100 dark:border-blue-800">
                                <h4 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                                    Benefits of Upgrading
                                </h4>
                                <ul class="space-y-3">
                                    <li class="flex items-start gap-3">
                                        <CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                                        <span class="text-slate-700 dark:text-slate-300">Unlimited access to all tools</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                                        <span class="text-slate-700 dark:text-slate-300">Priority technical support</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                                        <span class="text-slate-700 dark:text-slate-300">Regular updates & improvements</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                                        <span class="text-slate-700 dark:text-slate-300">Export all data & reports</span>
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
/* Custom styles */
</style>