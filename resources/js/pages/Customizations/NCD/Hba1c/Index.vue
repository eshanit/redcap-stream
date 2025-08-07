<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    project: IProject,
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
        href: ''
    }
];

// SVG Icons
const AllDataIcon = `
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
  <path d="M3.27 6.96 12 12.01l8.73-5.05"/>
  <path d="M12 22.08V12"/>
</svg>
`;

const RespondentIcon = `
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
  <circle cx="9" cy="7" r="4"/>
  <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
  <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
</svg>
`;

const GenderIcon = `
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M12 2a6 6 0 0 0-6 6c0 1.78.8 3.37 2.06 4.43.48.35.64.98.35 1.5-.2.4-.55.65-.96.65H5a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h1.44c.4 0 .77.25.96.65.29.52.13 1.15-.35 1.5A6.01 6.01 0 0 0 12 22a6 6 0 0 0 5.96-6.92c-.48-.35-.64-.98-.35-1.5.2-.4.55-.65.96-.65H19a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1h-1.44c-.4 0-.77-.25-.96-.65-.29-.52-.13-1.15.35-1.5A5.98 5.98 0 0 0 18 8a6 6 0 0 0-6-6z"/>
  <circle cx="12" cy="8" r="2"/>
</svg>
`;

const FacilityIcon = `
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
  <polyline points="9 22 9 12 15 12 15 22"/>
</svg>
`;
</script>

<template>
    <Head title="HBA1c Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
            <Heading 
                title="HBA1c Reports and Analysis" 
                description="Comprehensive analysis of hemoglobin A1c levels across different dimensions" 
                class="pt-2" 
            />
            
            <div class="flex flex-col items-center">
                <!-- First Row -->
                <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- All Data Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="AllDataIcon" class="text-blue-500" />
                                <span>All Data Outlook</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                Comprehensive statistical analysis of all HBA1c values including distribution, quartiles, and trends.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <p class="text-sm text-gray-600">
                                    Explore the complete dataset with detailed statistical breakdowns and visualizations.
                                </p>
                                <Link :href="route('packages.hba1c.alldata', [project.project_id])">
                                    <Button class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 transition-colors">
                                        View Full Analysis
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <!-- Respondent Based Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="RespondentIcon" class="text-purple-500" />
                                <span>Respondent Analysis</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                Individual respondent HBA1c levels compared against population metrics.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <p class="text-sm text-gray-600">
                                    Examine how each participant's levels compare to the overall distribution.
                                </p>
                                <Link :href="route('packages.hba1c.avgresp', [project.project_id])">
                                    <Button class="w-full bg-gradient-to-r from-purple-500 to-purple-600 text-white hover:from-purple-600 hover:to-purple-700 transition-colors">
                                        View Respondent Data
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                
                <!-- Second Row -->
                <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Gender Analysis Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="GenderIcon" class="text-pink-500" />
                                <span>Gender Analysis</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                Comparative analysis of HBA1c levels between male and female participants.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <p class="text-sm text-gray-600">
                                    Discover gender-based differences and trends in hemoglobin A1c measurements.
                                </p>
                                <Link :href="route('packages.hba1c.gender', [project.project_id])">
                                    <Button class="w-full bg-gradient-to-r from-pink-500 to-pink-600 text-white hover:from-pink-600 hover:to-pink-700 transition-colors">
                                        View Gender Report
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <!-- Facility Analysis Card -->
                    <Card class="hover:shadow-lg transition-shadow duration-200">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-3 text-lg">
                                <div v-html="FacilityIcon" class="text-teal-500" />
                                <span>Facility Analysis</span>
                            </CardTitle>
                            <CardDescription class="text-sm">
                                HBA1c level comparisons across different collection facilities.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col gap-4">
                                <p class="text-sm text-gray-600">
                                    Identify facility-specific patterns and variations in test results.
                                </p>
                                <Link :href="route('packages.hba1c.facility', [project.project_id])">
                                    <Button class="w-full bg-gradient-to-r from-teal-500 to-teal-600 text-white hover:from-teal-600 hover:to-teal-700 transition-colors">
                                        View Facility Data
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