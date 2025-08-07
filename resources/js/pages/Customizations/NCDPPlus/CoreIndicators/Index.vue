<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';


const props = defineProps<{
    project: IProject,
}>()

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
    href: ''
  }
];

// Updated Icons
const EnrollmentIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`;

const ActivePatientsIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a6 6 0 0 0-6 6c0 1.78.8 3.37 2.06 4.43.48.35.64.98.35 1.5-.2.4-.55.65-.96.65H5a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h1.44c.4 0 .77.25.96.65.29.52.13 1.15-.35 1.5A6.01 6.01 0 0 0 12 22a6 6 0 0 0 5.96-6.92c-.48-.35-.64-.98-.35-1.5.2-.4.55-.65.96-.65H19a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1h-1.44c-.4 0-.77-.25-.96-.65-.29-.52-.13-1.15.35-1.5A5.98 5.98 0 0 0 18 8a6 6 0 0 0-6-6z"/><circle cx="12" cy="8" r="2"/></svg>`;

const RecentEnrollmentIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M8 14h8"/></svg>`;

const LTFUIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 4.71 3 12l7.29 7.29M20 12H3M14 7l3 3-3 3"/></svg>`;

const DeceasedIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a6 6 0 0 0-6 6c0 1.78.8 3.37 2.06 4.43.48.35.64.98.35 1.5-.2.4-.55.65-.96.65H5a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h1.44c.4 0 .77.25.96.65.29.52.13 1.15-.35 1.5A6.01 6.01 0 0 0 12 22a6 6 0 0 0 5.96-6.92c-.48-.35-.64-.98-.35-1.5.2-.4.55-.65.96-.65H19a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1h-1.44c-.4 0-.77-.25-.96-.65-.29-.52-.13-1.15.35-1.5A5.98 5.98 0 0 0 18 8a6 6 0 0 0-6-6z"/><circle cx="12" cy="8" r="2"/></svg>`;

const MortalityIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a6 6 0 0 0-6 6c0 1.78.8 3.37 2.06 4.43.48.35.64.98.35 1.5-.2.4-.55.65-.96.65H5a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h1.44c.4 0 .77.25.96.65.29.52.13 1.15-.35 1.5A6.01 6.01 0 0 0 12 22a6 6 0 0 0 5.96-6.92c-.48-.35-.64-.98-.35-1.5.2-.4.55-.65.96-.65H19a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1h-1.44c-.4 0-.77-.25-.96-.65-.29-.52-.13-1.15.35-1.5A5.98 5.98 0 0 0 18 8a6 6 0 0 0-6-6z"/><circle cx="12" cy="8" r="2"/></svg>`;

</script>

<template>

    <Head title="Core Indicators Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <Heading title="Core Indicators Analysis"
                description="Comprehensive analysis of the respondents Core Indicators Analysis" class="pt-2" />

            <div class="flex flex-col items-center">
                <!-- First Row -->
                <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Enrollment Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="EnrollmentIcon" class="text-blue-500" />
                                <span>Enrollment</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                Enrollment analysis for pen plus respondents.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <Link :href="route('packages.core.enrollment', [project.project_id])">
                                <Button
                                    class="w-full cursor-pointer bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700">
                                    View Full Analysis
                                </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                     <!-- Recent Enrollments Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="RecentEnrollmentIcon" class="text-orange-500" />
                                <span>Recent Enrollments (3mo)</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                New patient enrollments analysis
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <Link :href="route('packages.core.enrolled.3months', [project.project_id])">
                                <Button
                                    class="w-full cursor-pointer bg-gradient-to-r from-orange-500 to-orange-600 text-white hover:from-orange-600 hover:to-orange-700">
                                    View Recent Enrollments
                                </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Second Row -->
                <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                         <!-- Active Patients Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="ActivePatientsIcon" class="text-green-500" />
                                <span>Active Patients</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                Patients active at PEN Plus clinic.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <Link :href="route('packages.core.active.patients', [project.project_id])">
                                <Button
                                    class="w-full cursor-pointer bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700">
                                    View Active Patients
                                </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- LTFU Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="LTFUIcon" class="text-red-500" />
                                <span>Recent LTFU (<3 months)</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                Lost to follow-up analysis
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <Link :href="route('packages.core.enrolled.ltfu3months', [project.project_id])">
                                <Button
                                    class="w-full cursor-pointer bg-gradient-to-r from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700">
                                    View LTFU Analysis
                                </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Third Row -->
                <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Deceased Patients Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="DeceasedIcon" class="text-purple-500" />
                                <span>Deceased Patients</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                Comprehensive mortality analysis
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <Link :href="route('packages.core.deaths.patients', [project.project_id])">
                                <Button
                                    class="w-full cursor-pointer bg-gradient-to-r from-purple-500 to-purple-600 text-white hover:from-purple-600 hover:to-purple-700">
                                    View Mortality Data
                                </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Mortality Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="MortalityIcon" class="text-gray-600" />
                                <span>Recent Mortality (< 3mo)</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                Short-term mortality analysis
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <Link :href="route('packages.core.enrolled.death3months', [project.project_id])">
                                <Button
                                    class="w-full cursor-pointer bg-gradient-to-r from-gray-600 to-gray-700 text-white hover:from-gray-700 hover:to-gray-800">
                                    View Recent Mortality
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

<style scoped>
/* Add any custom styles here if needed */
.card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease;
}
</style>