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
];



</script>
<template>

    <Head title="Projects" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <div v-for="project in props.projects">
                    <Link :href="route(project.project_name+'.project.dashboard',[project.project_id])">
                        <div
                            class="relative overflow-hidden rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-10 text-center hover:bg-sky-300 hover:text-white">
                            <div class="text-lg font-semibold text-sky-700 border-b hover:text-white">
                                {{ project.app_title }}
                            </div>
                            <div>
                                {{ format(project.creation_time, 'dd MMMM yyyy') }}
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>