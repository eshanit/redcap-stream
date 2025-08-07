<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Link } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { Users, CalendarX2, PackageOpen, Binoculars } from 'lucide-vue-next';
import DonutChart from '@/components/charts/chartjs/Donut.vue';
import BarChart from '@/components/charts/chartjs/BarChart.vue';
import GenderTable from '@/components/custom/tables/Gender.vue';
import Button from '@/components/ui/button/Button.vue';

const props = defineProps<{
    project: IProject,
    respondentCount: number,
    respondentGender: any,
    respondentFacility: any,
    lostToFollowUp: number
}>()


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: props.project.app_title,
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Project Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Heading :title="props.project.app_title" :description="props.project.creation_time" class="pt-5" />
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                <div class="col-span-1 md:col-span-3">
                    <Card class="mb-5">
                        <CardHeader>
                            <CardTitle>Respondents</CardTitle>
                            <CardDescription>Number of respondents in this project</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex gap-5">
                                <Users class="ml-auto size-18" color="green" />
                                <div class="text-sky-500 text-4xl font-semibold">
                                    {{ props.respondentCount }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Lost To Follow Up</CardTitle>
                            <CardDescription>Respondents who missed Next Review Date by at least 60 days</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex gap-5">
                                <CalendarX2 class="ml-auto size-18" color="red" />
                                <div class="flex gap-1">
                                    <div class="text-sky-500 text-4xl font-semibold">
                                        {{ lostToFollowUp }}
                                    </div>
                                    <div class="border-r" />
                                    <div class="text-orange-500 text-xl font-semibold">
                                        {{ (100 * lostToFollowUp / props.respondentCount).toFixed(2) }}%
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="col-span-1 md:col-span-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Respondents by gender</CardTitle>
                            <CardDescription>Number of respondents broken down by gender</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col md:flex-row gap-5">
                                <DonutChart :chart-data="Object.values(props.respondentGender)" :chart-labels="['Male', 'Female']" />
                                <GenderTable :gender-data="{ 'Male': props.respondentGender[1], 'Female': props.respondentGender[2] }" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="col-span-1 md:col-span-5">
                    <Card>
                        <CardHeader>
                            <CardTitle>Respondents by facility</CardTitle>
                            <CardDescription>Number of respondents broken down by facility</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <BarChart :chart-data="props.respondentFacility" />
                        </CardContent>
                    </Card>
                </div>
            </div>

            <div class="pt-10 border-t" />

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div id="packages" class="col-span-1">
                    <Card>
                        <CardHeader>
                            <CardTitle>Customized Packages</CardTitle>
                            <CardDescription>This section contains customized analysis and reports for <span class="text-green-500">{{ props.project.app_title }}</span>. Click below to enter</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex gap-5">
                                <PackageOpen class="ml-auto size-18" color="blue" />
                                <Link :href="route('packages.dashboard', [project.project_id])">
                                    <Button variant="outline" class="border border-orange-500 text-orange-500 bg-white rounded w-full py-2 hover:bg-orange-500 hover:text-white cursor-pointer">
                                        I'm feeling lucky
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div id="packages" class="col-span-1">
                    <Card>
                        <CardHeader>
                            <CardTitle>Questionnaire Insights</CardTitle>
                            <CardDescription>View insights for each questionnaire item in <span class="text-green-500">{{ props.project.app_title }}</span>. Click below to enter</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex gap-5">
                                <Binoculars class="ml-auto size-18" color="orange" />
                                <Link :href="route(project.project_name+'.questionnaire.dashboard', [project.project_id])">
                                    <Button variant="outline" class="border border-green-500 text-green-500 bg-white rounded w-full py-2 hover:bg-green-500 hover:text-white cursor-pointer">
                                        Let's get rolling
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

        </div>
    </AppLayout>
</template>