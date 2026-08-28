<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

interface ProjectSummary {
    project_id: number | null;
    app_title: string;
    project_name: string;
}

const props = defineProps<{
    project: ProjectSummary;
    recordCount: number;
    recordsByProject: Record<string, number>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: props.project.app_title, href: `/data6/project/${props.project.project_id}` },
];
</script>

<template>
    <Head :title="`${project.app_title} Overview`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 px-4 py-8 dark:from-slate-900 dark:to-slate-800 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wide text-red-600 dark:text-red-400">REDCap data6</p>
                        <h1 class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ project.app_title }}</h1>
                        <p class="mt-2 text-slate-600 dark:text-slate-300">Initial project overview</p>
                    </div>
                    <Link href="/dashboard" class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400">All projects</Link>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Unique clients</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ recordCount }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Split projects onboarded</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ Object.keys(recordsByProject).length }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Project ID</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ project.project_id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>