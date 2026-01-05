<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { format, parseISO } from 'date-fns';

const props = defineProps<{
    projects: Array<IProject>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Projects',
        href: '#',
    },
];
</script>

<template>
    <Head title="Projects" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-slate-900 dark:to-slate-800">
            <!-- Header -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">
                                Your Projects
                                <span class="block text-lg font-normal text-slate-600 dark:text-slate-300 mt-2">
                                    Manage and analyze all your research projects
                                </span>
                            </h1>
                        </div>
                        <Link
                            :href="route('projects.create')"
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-500 to-orange-500 text-white font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 inline-flex items-center justify-center gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            New Project
                        </Link>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Total Projects</p>
                                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ projects.length }}</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Active Projects</p>
                                <p class="text-3xl font-bold text-slate-900 dark:text-white">
                                    {{ projects.filter(p => p.status === 'active').length }}
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Recent Activity</p>
                                <p class="text-lg font-bold text-slate-900 dark:text-white">
                                    {{ projects.length > 0 ? format(projects[0].creation_time, 'MMM dd, yyyy') : 'No projects' }}
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projects Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <Link
                        v-for="project in projects"
                        :key="project.project_id"
                        :href="route(project.project_name+'.project.dashboard',[project.project_id])"
                        class="group relative block"
                    >
                        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-6 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:border-red-300 dark:hover:border-red-500/50">
                            <!-- Project Icon -->
                            <div class="mb-4 flex items-center justify-between">
                                <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-medium',
                                        project.status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'
                                            : project.status === 'draft'
                                            ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400'
                                            : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'
                                    ]"
                                >
                                    {{ project.status === 'active' ? 'Active' : project.status === 'draft' ? 'Draft' : project.status }}
                                </span>
                            </div>

                            <!-- Project Info -->
                            <div class="space-y-3">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors duration-300">
                                    {{ project.app_title }}
                                </h3>
                                
                                <div class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2">
                                    Project ID: {{ project.project_id }}
                                </div>

                                <div class="flex items-center justify-between pt-3 border-t border-slate-200 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400">
                                        Created {{ format(project.creation_time, 'MMM dd, yyyy') }}
                                    </div>
                                    <div class="text-red-500 group-hover:translate-x-1 transition-transform duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Hover gradient effect -->
                            <div class="absolute inset-0 bg-gradient-to-r from-red-500/0 via-orange-500/0 to-red-500/0 group-hover:from-red-500/5 group-hover:via-orange-500/5 group-hover:to-red-500/5 transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                        </div>
                    </Link>

                    <!-- Empty State -->
                    <div 
                        v-if="projects.length === 0"
                        class="col-span-full"
                    >
                        <div class="text-center py-12">
                            <div class="mx-auto h-24 w-24 bg-gradient-to-r from-red-100 to-orange-100 dark:from-red-900/20 dark:to-orange-900/20 rounded-full flex items-center justify-center mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No projects yet</h3>
                            <p class="text-slate-600 dark:text-slate-300 mb-6 max-w-md mx-auto">
                                Get started by creating your first research project to begin analyzing your data.
                            </p>
                            <Link
                                :href="route('projects.create')"
                                class="inline-flex items-center px-6 py-3 rounded-xl bg-gradient-to-r from-red-500 to-orange-500 text-white font-medium hover:shadow-lg transition-all duration-300 hover:scale-105"
                            >
                                Create Your First Project
                            </Link>
                        </div>
                    </div>

                    <!-- Add New Project Card -->
                    <Link
                        :href="route('projects.create')"
                        class="group relative block"
                    >
                        <div class="relative overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-red-400 dark:hover:border-red-500 p-8 text-center transition-all duration-300 hover:scale-[1.02]">
                            <div class="w-16 h-16 mx-auto bg-gradient-to-r from-red-500/10 to-orange-500/10 rounded-full flex items-center justify-center mb-4 group-hover:from-red-500/20 group-hover:to-orange-500/20 transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-2">Add New Project</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Create a new research project
                            </p>
                            <div class="absolute inset-0 bg-gradient-to-r from-red-500/0 via-orange-500/0 to-red-500/0 group-hover:from-red-500/5 group-hover:via-orange-500/5 group-hover:to-red-500/5 transition-all duration-500 opacity-0 group-hover:opacity-100"></div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>