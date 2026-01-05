<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { Mail, ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    project: IProject
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
        href: '/' + (props.project.project_id == 39 ? 'ncd_pplus' : props.project.project_id == 50 ? 'ahp' : 'ncd') + '/project/' + props.project.project_id,
    },
    {
        title: 'Custom Packages',
        href: route('packages.dashboard', [props.project.project_id]),
    },
    {
        title: 'Request Package',
        href: ''
    }
];
</script>

<template>
    <Head :title="`${props.project.app_title} - Request Custom Package`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-slate-900 dark:to-slate-800">
            <!-- Page Header -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                    <Mail class="h-5 w-5 text-white" />
                                </div>
                                <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                    Request Custom Package
                                </h1>
                            </div>
                            <p class="text-slate-600 dark:text-slate-300">
                                {{ props.project.app_title }}
                            </p>
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

                    <!-- Request Form/Message -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto bg-gradient-to-r from-red-100 to-orange-100 dark:from-red-900/20 dark:to-orange-900/20 rounded-full flex items-center justify-center mb-6">
                                <Mail class="h-10 w-10 text-red-500" />
                            </div>
                            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">
                                Custom Package Request
                            </h2>
                            <p class="text-slate-600 dark:text-slate-300 mb-8 max-w-2xl mx-auto leading-relaxed">
                                To request a custom analysis package for your research project, please contact our development team.
                                We'll work with you to create specialized reports and visualizations tailored to your specific needs.
                            </p>

                            <!-- Contact Information -->
                            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-6 mb-8">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                                    Contact Information
                                </h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-center gap-3">
                                        <Mail class="h-5 w-5 text-red-500" />
                                        <span class="text-slate-700 dark:text-slate-300">
                                            Email: <a href="mailto:tinashemangoro@gmail.com" class="text-red-600 dark:text-red-400 hover:underline">tinashemangoro@gmail.com</a>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                We'll respond to your request within 24-48 hours with next steps for your custom package development.
                            </p>
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