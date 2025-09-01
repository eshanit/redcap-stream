<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Button from '@/components/ui/button/Button.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { ref, onMounted, computed } from 'vue';

const props = defineProps<{
    project: IProject,
    respondentCount: number,
    facilityBreakdown: any,
    metrics: any,
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
        title: 'Service Based Indicators',
        href: ''
    }
];

// Animation state
const animatedItems = ref<Record<string, boolean>>({});

// Animate elements on mount
onMounted(() => {
    setTimeout(() => {
        animatedItems.value = {
            header: true,
            summary: true,
            metrics: true,
            facilities: true
        };
    }, 100);
});

// Compute facility names for display
const facilityNames = computed(() => {
    return Object.keys(props.facilityBreakdown || {});
});

// Compute metrics for display with percentages
const metricsWithPercentages = computed(() => {
    return Object.entries(props.metrics || {}).map(([key, value]: [string, any]) => {
        const percentage = props.respondentCount > 0 
            ? ((value.total / props.respondentCount) * 100).toFixed(1) 
            : '0.0';
        return {
            key,
            label: key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase()),
            total: value.total,
            percentage,
            byFacility: value.byFacility
        };
    });
});

// Print report function
const printReport = () => {
    window.print();
};

// Download as PDF function (placeholder)
const downloadPDF = () => {
    alert('PDF download functionality would be implemented here');
};
</script>

<template>
    <Head title="AHP Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto mt-10 px-4 print:px-0">
            <!-- Header -->
            <div class="mb-10 text-center transition-all duration-700" 
                 :class="{'opacity-0 translate-y-4': !animatedItems.header, 'opacity-100 translate-y-0': animatedItems.header}">
                <h1 class="text-4xl md:text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3">
                    Data Management Report
                </h1>
                <p class="text-gray-600 max-w-3xl mx-auto">
                    Providing a management report to help monitor the overall performance/progress of data collection
                    and quality of all the study's teams and regions
                </p>
                <div class="mt-6 flex justify-center gap-4">
                    <Button @click="printReport" class="print:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m4 4h6a2 2 0 002-2v-4a2 2 0 00-2-2h-6a2 2 0 00-2 2v4a2 2 0 002 2z" />
                        </svg>
                        Print Report
                    </Button>
                    <Button @click="downloadPDF" variant="outline" class="print:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </Button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                <Card class="transition-all duration-700 delay-100" 
                      :class="{'opacity-0 translate-y-4': !animatedItems.summary, 'opacity-100 translate-y-0': animatedItems.summary}">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-blue-600 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Total Respondents
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-4xl font-bold text-blue-700">{{ respondentCount }}</div>
                        <p class="text-sm text-gray-500 mt-2">Total number of respondents across all facilities</p>
                    </CardContent>
                </Card>

                <Card class="transition-all duration-700 delay-200" 
                      :class="{'opacity-0 translate-y-4': !animatedItems.summary, 'opacity-100 translate-y-0': animatedItems.summary}">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-purple-600 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-8m-8 0H3m2 0h8M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Facilities
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-4xl font-bold text-purple-700">{{ facilityNames.length }}</div>
                        <p class="text-sm text-gray-500 mt-2">Number of facilities participating in the study</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Metrics Overview -->
            <Card class="mb-10 transition-all duration-700 delay-300" 
                  :class="{'opacity-0 translate-y-4': !animatedItems.metrics, 'opacity-100 translate-y-0': animatedItems.metrics}">
                <CardHeader>
                    <CardTitle class="text-gray-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Service Metrics Overview
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div v-for="(metric, index) in metricsWithPercentages" :key="metric.key" 
                             class="p-4 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-500 hover:shadow-md"
                             :class="{'hover:border-blue-300': index % 3 === 0, 'hover:border-purple-300': index % 3 === 1, 'hover:border-green-300': index % 3 === 2}">
                            <h3 class="font-semibold text-gray-700 mb-2">{{ metric.label }}</h3>
                            <div class="flex items-end justify-between">
                                <span class="text-2xl font-bold text-gray-800">{{ metric.total }}</span>
                                <span class="text-sm px-2 py-1 rounded-full" 
                                      :class="{'bg-blue-100 text-blue-800': index % 3 === 0, 'bg-purple-100 text-purple-800': index % 3 === 1, 'bg-green-100 text-green-800': index % 3 === 2}">
                                    {{ metric.percentage }}%
                                </span>
                            </div>
                            <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" 
                                     :class="{'bg-blue-500': index % 3 === 0, 'bg-purple-500': index % 3 === 1, 'bg-green-500': index % 3 === 2}"
                                     :style="{ width: `${metric.percentage}%` }"></div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Detailed Metrics Table -->
            <Card class="mb-10 transition-all duration-700 delay-400 overflow-hidden" 
                  :class="{'opacity-0 translate-y-4': !animatedItems.metrics, 'opacity-100 translate-y-0': animatedItems.metrics}">
                <CardHeader class="bg-gray-50">
                    <CardTitle class="text-gray-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Detailed Metrics by Facility
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th v-for="facility in facilityNames" :key="facility" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ facility }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(metric, index) in metricsWithPercentages" :key="metric.key" 
                                    class="transition-colors duration-300 hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ metric.label }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ metric.total }}</td>
                                    <td v-for="facility in facilityNames" :key="facility" class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        {{ metric.byFacility[facility] || 0 }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Facility Breakdown -->
            <Card class="transition-all duration-700 delay-500" 
                  :class="{'opacity-0 translate-y-4': !animatedItems.facilities, 'opacity-100 translate-y-0': animatedItems.facilities}">
                <CardHeader>
                    <CardTitle class="text-gray-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-8m-8 0H3m2 0h8M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Facility Distribution
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="(count, facility) in facilityBreakdown" :key="facility" 
                             class="p-4 border rounded-lg bg-white shadow-sm transition-all duration-300 hover:shadow-md">
                            <h3 class="font-semibold text-gray-700 mb-2">{{ facility }}</h3>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-gray-800">{{ count }}</div>
                                    <div class="text-sm text-gray-500">Respondents</div>
                                </div>
                                <div class="w-12 h-12 rounded-full flex items-center justify-center" 
                                     :class="{'bg-blue-100 text-blue-800': count % 3 === 0, 'bg-purple-100 text-purple-800': count % 3 === 1, 'bg-green-100 text-green-800': count % 3 === 2}">
                                    <span class="font-bold">{{ ((count / respondentCount) * 100).toFixed(0) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}

.animate-slide-in {
    animation: slideIn 0.6s ease-out forwards;
}

.print\:hidden {
    @media print {
        display: none;
    }
}

.print\:px-0 {
    @media print {
        padding-left: 0;
        padding-right: 0;
    }
}
</style>