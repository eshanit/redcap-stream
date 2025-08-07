<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import DeathQuarterChart from '@/components/charts/apexcharts/DeathQuarterChart.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    project: IProject,
    deathStatistics: {
        total_deaths: number;
        deaths_by_quarter: Record<string, Record<string, number>>;
    }
}>();

const packageRoot = computed(() => {
  if (props.project.project_id == 39) {
    return 'ncd_pplus'
  }
  return 'ncd'
})

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: props.project.app_title,
    href: '/' + packageRoot.value + '/project/' + props.project.project_id,
  },
  {
    title: 'Custom Packages',
    href: '/packages/project/' + props.project.project_id + '/dashboard',
  },
  {
    title: 'Core Indicators',
    href: '/packages/project/' + props.project.project_id + '/core_indicators',
  },
  {
    title: 'Deceased Patients',
    href: ''
  }
];


// Computed property to format chart data
const chartData = computed(() => {
    const years = Object.keys(props.deathStatistics.deaths_by_quarter);
    const quarters = ['Q1', 'Q2', 'Q3', 'Q4'];

    return {
        series: quarters.map(quarter => ({
            name: quarter,
            data: years.map(year => props.deathStatistics.deaths_by_quarter[year][quarter] || 0)
        })),
        options: {
            chart: {
                type: 'bar',
                stacked: true,
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                },
            },
            xaxis: {
                categories: years,
                title: {
                    text: 'Years'
                }
            },
            yaxis: {
                title: {
                    text: 'Number of Deaths'
                }
            },
            legend: {
                position: 'top'
            },
            colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'] // Tailwind colors
        }
    };
});

//
const yearlyTotals = computed(() => {
    const totals: Record<string, number> = {};

    Object.entries(props.deathStatistics.deaths_by_quarter).forEach(
        ([year, quarters]) => {
            totals[year] = Object.values(quarters).reduce(
                (sum: number, count: number) => sum + count,
                0
            );
        }
    );

    return totals;
});
</script>

<template>

    <Head title="Customized Packaged Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col h-full gap-4 p-4 rounded-xl bg-gray-50">
            <Heading title="Mortality Reports and Analysis" description="Customised Package" class="pt-5" />
            <!-- <pre>
                {{ props.deathStatistics }}
            </pre> -->
            <Card class="hover:shadow-lg transition-shadow duration-200">
                <CardHeader>
                    <CardTitle class="flex items-center gap-3 text-lg">
                        <span>Ever Deceased</span>
                    </CardTitle>
                    <CardDescription class="text-sm">
                        Number of patients enrolled who have died.
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid gap-6 md:grid-cols-1">
                    <!-- Chart Section -->
                    <div>
                        <DeathQuarterChart description="Mortality Overview" :data="deathStatistics" />
                    </div>
                    <!-- Total Deaths Card -->
                    <div class="md:col-span-2">
                        <Card class="bg-blue-50 border-blue-200">
                            <CardHeader class="py-3">
                                <CardTitle class="text-lg">Total Deaths</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="text-3xl font-bold text-blue-600">
                                    {{ deathStatistics.total_deaths }}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </CardContent>

            </Card>
        </div>
    </AppLayout>
</template>