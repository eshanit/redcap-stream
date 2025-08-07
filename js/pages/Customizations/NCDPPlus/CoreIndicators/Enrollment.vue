<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import BarChart from '@/components/charts/chartjs/BarChart.vue';
import GenderTable from '@/components/custom/tables/Gender.vue';
import PieChart from '@/components/charts/apexcharts/PieChart.vue';
import EnrolledTable from '@/components/tables/agtables/ncdpplus/coreinsights/Enrolled.vue';
import EnrollQuarterChart from '@/components/charts/apexcharts/EnrollQuarterChart.vue';
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
import { computed } from 'vue';

const props = defineProps<{
    project: IProject,
    respondentCount: number,
    respondentGender: any,
    respondentFacility: any,
    enrolled: any[],
    lostToFollowUp: number
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
    title: 'All Enrolled',
    href: ''
  }
];

</script>

<template>
    <Head title="Customized Packaged Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col h-full gap-4 p-4 rounded-xl bg-gray-50">
            <Heading title="Enrollment Analysis" description="Customised Package" class="pt-5" />
            <Card class="hover:shadow-lg transition-shadow duration-200">
                <CardHeader>
                    <CardTitle class="flex items-center gap-3 text-lg">
                        <span>Ever Enrolled</span>
                    </CardTitle>
                    <CardDescription class="text-sm">
                        Respondents enrolled at Pen Plus Clinics
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <Card>
                            <CardHeader>
                                <CardTitle>Respondents Enrolled by Gender</CardTitle>
                                <CardDescription>Number of respondents broken down by gender</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="flex flex-col md:flex-row gap-5">
                                    <div class="flex-1">
                                        <PieChart :chart-data="props.respondentGender" chart-description="Enrollment by gender" />
                                    </div>
                                    <div class="flex-none w-1/3">
                                        <GenderTable
                                            :gender-data="{ 'Male': props.respondentGender.Male, 'Female': props.respondentGender.Female }" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardTitle>Respondents by Facility</CardTitle>
                                <CardDescription>Number of respondents broken down by facility</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <BarChart :chart-data="props.respondentFacility" />
                            </CardContent>
                        </Card>
                    </div>
                    <div class="py-5">
                        <EnrollQuarterChart :enrolled-data="props.enrolled" />
                    </div>
                    <div class="py-5 text-sm">
                        Below is a list of all the registered/enrolled respondents
                    </div>
                    <EnrolledTable :all-results="props.enrolled" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>