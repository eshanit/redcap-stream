<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import GenderReports from '@/components/insights/ncd/hba1c/GenderReports.vue';

const props = defineProps<{
    project: IProject,
    genderData: any
}>()


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
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
        title: 'HBa1c',
        href: '/packages/project/' + props.project.project_id + '/hba1c_analytics',
    },
    {
        title: 'All Data Analytics',
        href: ''
    }
];


</script>
<template>

    <Head title="HBA1c Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Heading title="HBc1a Reports and Analysis" description="Customised Package" class="pt-5" />
        </div>
        <div class="grid grid-cols-2 gap-5 p-5">
            <div id="males" class="bg-sky-50 p-5 rounded-lg">
                <GenderReports :gender-data="props.genderData" gender-type="Male" />
            </div>
            <div id="females" class="bg-pink-50 p-5 rounded-lg">
                <GenderReports :gender-data="props.genderData" gender-type="Female" />
            </div>
        </div>
    </AppLayout>
</template>