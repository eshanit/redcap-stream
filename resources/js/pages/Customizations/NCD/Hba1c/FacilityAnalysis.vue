<script setup lang='ts'>
import { ref, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    project: IProject,
    tableData: any[],
    numRespondents: number,
    classificationCounts: {
        normal: number,
        prediabetic: number,
        diabetic: number
    },
    classificationPercentages: {
        normal: number,
        prediabetic: number,
        diabetic: number
    }
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
        title: 'Facility HbA1c Analytics',
        href: ''
    }
];

const progress = ref(0);

// Animate progress bar
const animateProgress = () => {
    const targetProgress = 72; // Set your progress percentage
    const interval = setInterval(() => {
        if (progress.value >= targetProgress) {
            clearInterval(interval);
            return;
        }
        progress.value += 1;
    }, 30);
};

onMounted(() => {
    animateProgress();
});
</script>

<template>
    <Head title="Customized Packaged Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Heading title="HBc1a Reports and Analysis" description="Customised Package" class="pt-5" />

            <div class="flex flex-col items-center justify-center min-h-[60vh] text-center gap-8">
                <!-- Construction Icon -->
                <div class="text-7xl mb-4 animate-pulse">🚧</div>

                <!-- Main Heading -->
                <h1 class="text-4xl font-bold text-primary tracking-tight">
                    Under Construction
                </h1>

                <!-- Message -->
                <div class="max-w-2xl text-lg text-muted-foreground">
                    Analysis for HbA1c by Facility is still being engineered. Our team is working hard to deliver this feature soon.
                </div>

                <!-- Progress Bar -->
                <div class="w-full max-w-md space-y-2">
                    <div class="flex justify-between text-sm text-muted-foreground">
                        <span>Progress</span>
                        <span>{{ progress }}%</span>
                    </div>
                    <div class="h-3 w-full rounded-full bg-gray-200 overflow-hidden">
                        <div 
                            class="h-full rounded-full bg-primary transition-all duration-300"
                            :style="{ width: progress + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="text-sm text-muted-foreground mt-4">
                    Estimated completion: <span class="font-medium">Q3 2024</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Add any custom animations or styles here if needed */
</style>