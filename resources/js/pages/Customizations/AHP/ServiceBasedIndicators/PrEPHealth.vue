<script setup lang="ts">
import { ref, Ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import PieChart from '@/components/charts/apexcharts/ahp/PieChart.vue';
import BarChart from '@/components/charts/apexcharts/ahp/BarChart.vue';
import {
    Filter, UserCheck, UserX, Percent, Users, ChevronDown, BarChart2, PieChart as PieChartIcon,
    Activity, HeartPulse, BookUser, Baby, Syringe, Stethoscope, ClipboardList, TestTube2
} from 'lucide-vue-next';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const props = defineProps<{
    project: IProject,
    prepHealthData: Array<any>
}>();

const activeTab = ref('overview');
const genderFilter = ref<string[]>([]);
const facilityFilter = ref<string[]>([]);
const clientProfileFilter = ref<string[]>([]);
const showFilters = ref(false);

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
        href: '/packages/project/' + props.project.project_id + '/delivery-indicators-1',
    },
    {
        title: 'Prep Health Analysis',
        href: ''
    }
];


// Enhanced data with calculated fields
const enhancedData = computed(() => {
    return props.prepHealthData.map(patient => {
        // Calculate age from date of birth
        let age = null;
        if (patient.date_of_birth) {
            const birthDate = new Date(patient.date_of_birth);
            const today = new Date();
            age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
        }
        
        // Assign age group
        let ageGroup = 'Unknown';
        if (age !== null) {
            if (age <= 18) ageGroup = '≤18 years';
            else if (age <= 35) ageGroup = '19-35 years';
            else if (age <= 50) ageGroup = '36-50 years';
            else ageGroup = '51+ years';
        }
        
        return {
            ...patient,
            age,
            ageGroup
        };
    });
});

// Filtered data
const filteredData = computed(() => {
    return enhancedData.value.filter(patient => {
        const genderMatch = genderFilter.value.length === 0 || 
                          genderFilter.value.includes(patient.gender);
        const facilityMatch = facilityFilter.value.length === 0 ||
                          facilityFilter.value.includes(patient.health_facility);
        const clientProfileMatch = clientProfileFilter.value.length === 0 ||
                          clientProfileFilter.value.includes(patient.client_profile);
        
        return genderMatch && facilityMatch && clientProfileMatch;
    });
});

// PrEP Metrics Calculations
const totalPatients = computed(() => filteredData.value.length);

// 1. Screening metrics
const screenedPatients = computed(() => 
    filteredData.value.filter(p => p.prepr_clinical_eligible !== null)
);
const eligiblePatients = computed(() => 
    screenedPatients.value.filter(p => p.prepr_clinical_eligible === '1')
);
const notEligiblePatients = computed(() => 
    screenedPatients.value.filter(p => p.prepr_clinical_eligible === '0')
);

// 2. Initiation metrics
const initiatedPatients = computed(() => 
    filteredData.value.filter(p => p.prepr_date !== null)
);

// 3. Continuation metrics
const continuationPatients = computed(() => 
    initiatedPatients.value.filter(p => p.prep_visit_date !== null)
);
const continuationRate = computed(() => 
    initiatedPatients.value.length > 0 
        ? Math.round((continuationPatients.value.length / initiatedPatients.value.length) * 100) 
        : 0
);

// Data for charts
const screeningData = computed(() => ({
    labels: ['Eligible', 'Not Eligible'],
    series: [eligiblePatients.value.length, notEligiblePatients.value.length]
}));

const initiationData = computed(() => ({
    labels: ['Initiated', 'Not Initiated'],
    series: [
        initiatedPatients.value.length,
        eligiblePatients.value.length - initiatedPatients.value.length
    ]
}));

const continuationData = computed(() => ({
    categories: ['Initiated', 'Continued'],
    series: [{
        name: 'Patients',
        data: [
            initiatedPatients.value.length, 
            continuationPatients.value.length
        ]
    }]
}));

// Client profile distribution
const clientProfileData = computed(() => {
    const profiles = [...new Set(filteredData.value.map(p => p.client_profile || 'Unknown'))];
    const counts = profiles.map(profile => 
        filteredData.value.filter(p => p.client_profile === profile).length
    );
    
    return {
        labels: profiles,
        series: counts
    };
});

// Facility distribution
const facilityData = computed(() => {
    const facilities = [...new Set(filteredData.value.map(p => p.health_facility || 'Unknown'))];
    const counts = facilities.map(facility => 
        filteredData.value.filter(p => p.health_facility === facility).length
    );
    
    return {
        labels: facilities,
        series: counts
    };
});

// Toggle filters
const toggleFilter = (filterArray: Ref<string[]>, value: string) => {
    const index = filterArray.value.indexOf(value);
    if (index > -1) {
        filterArray.value.splice(index, 1);
    } else {
        filterArray.value.push(value);
    }
};

const clearFilters = () => {
    genderFilter.value = [];
    facilityFilter.value = [];
    clientProfileFilter.value = [];
};

// Get unique values for filters
const uniqueGenders = computed(() => [...new Set(props.prepHealthData.map(p => p.gender))].filter(Boolean));
const uniqueFacilities = computed(() => [...new Set(props.prepHealthData.map(p => p.health_facility))].filter(Boolean));
const uniqueClientProfiles = computed(() => [...new Set(props.prepHealthData.map(p => p.client_profile))].filter(Boolean));
</script>

<template>
    <Head title="PrEP Health Analysis" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4">
                <Heading as="h1" class="text-2xl font-bold text-gray-900 dark:text-white">
                    PrEP Health Analysis
                </Heading>
                <p class="text-gray-600 dark:text-gray-400">
                    Comprehensive analysis of PrEP screening, initiation, and continuation for {{ project.app_title }} patients
                </p>
            </div>

            <!-- Filter Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <button @click="showFilters = !showFilters" 
                            class="flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600">
                            <Filter class="w-4 h-4" />
                            Filters
                            <ChevronDown class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showFilters }" />
                        </button>
                    </div>
                    
                    <div v-if="genderFilter.length > 0 || facilityFilter.length > 0 || clientProfileFilter.length > 0">
                        <button @click="clearFilters" 
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 flex items-center gap-1">
                            Clear all filters
                        </button>
                    </div>
                </div>

                <div v-if="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Gender Filter -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</h3>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="gender in uniqueGenders" :key="gender"
                                @click="toggleFilter(genderFilter, gender)"
                                class="px-3 py-1 text-xs rounded-full border transition-colors" :class="{
                                    'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300': genderFilter.includes(gender),
                                    'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700': !genderFilter.includes(gender)
                                }">
                                {{ gender }}
                            </button>
                        </div>
                    </div>

                    <!-- Facility Filter -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Health Facility</h3>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="facility in uniqueFacilities" 
                                :key="facility" 
                                @click="toggleFilter(facilityFilter, facility)"
                                class="px-3 py-1 text-xs rounded-full border transition-colors" :class="{
                                    'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300': facilityFilter.includes(facility),
                                    'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700': !facilityFilter.includes(facility)
                                }">
                                {{ facility }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Client Profile Filter -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client Profile</h3>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="profile in uniqueClientProfiles" 
                                :key="profile" 
                                @click="toggleFilter(clientProfileFilter, profile)"
                                class="px-3 py-1 text-xs rounded-full border transition-colors" :class="{
                                    'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300': clientProfileFilter.includes(profile),
                                    'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700': !clientProfileFilter.includes(profile)
                                }">
                                {{ profile }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Patients Card -->
                <Card class="hover:shadow-lg transition-shadow border border-blue-100 dark:border-blue-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Patients</CardTitle>
                        <Users class="w-5 h-5 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ totalPatients }}</div>
                        <p class="text-xs text-gray-500 mt-1">Patients in PrEP program</p>
                    </CardContent>
                </Card>

                <!-- Screened Patients Card -->
                <Card class="hover:shadow-lg transition-shadow border border-purple-100 dark:border-purple-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Screened</CardTitle>
                        <ClipboardList class="w-5 h-5 text-purple-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">{{ screenedPatients.length }}</div>
                        <p class="text-xs text-gray-500 mt-1">Assessed for eligibility</p>
                    </CardContent>
                </Card>

                <!-- Initiated Patients Card -->
                <Card class="hover:shadow-lg transition-shadow border border-green-100 dark:border-green-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Initiated</CardTitle>
                        <Syringe class="w-5 h-5 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ initiatedPatients.length }}</div>
                        <p class="text-xs text-gray-500 mt-1">Started PrEP treatment</p>
                    </CardContent>
                </Card>

                <!-- Continuation Rate Card -->
                <Card class="hover:shadow-lg transition-shadow border border-amber-100 dark:border-amber-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Continuation Rate</CardTitle>
                        <Percent class="w-5 h-5 text-amber-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-amber-600">{{ continuationRate }}%</div>
                        <p class="text-xs text-gray-500 mt-1">Patients continuing PrEP</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Detailed Metrics Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Screening Breakdown -->
                <Card class="p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <Stethoscope class="w-5 h-5 text-indigo-600" />
                        <h3 class="font-semibold text-gray-800 dark:text-white">Screening Breakdown</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <div class="text-lg font-bold text-green-600">{{ eligiblePatients.length }}</div>
                            <p class="text-sm text-green-800 dark:text-green-200">Eligible for PrEP</p>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                            <div class="text-lg font-bold text-red-600">{{ notEligiblePatients.length }}</div>
                            <p class="text-sm text-red-800 dark:text-red-200">Not Eligible</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <PieChart 
                            v-if="screeningData.series.some(s => s > 0)"
                            :series="screeningData.series"
                            :labels="screeningData.labels"
                            height="200px" />
                        <div v-else class="h-32 flex items-center justify-center text-gray-500">
                            No screening data available
                        </div>
                    </div>
                </Card>

                <!-- Initiation Breakdown -->
                <Card class="p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <Syringe class="w-5 h-5 text-indigo-600" />
                        <h3 class="font-semibold text-gray-800 dark:text-white">Initiation Status</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                            <div class="text-lg font-bold text-blue-600">{{ initiatedPatients.length }}</div>
                            <p class="text-sm text-blue-800 dark:text-blue-200">Initiated PrEP</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="text-lg font-bold text-gray-600">
                                {{ eligiblePatients.length - initiatedPatients.length }}
                            </div>
                            <p class="text-sm text-gray-800 dark:text-gray-200">Eligible but Not Initiated</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <PieChart 
                            v-if="initiationData.series.some(s => s > 0)"
                            :series="initiationData.series"
                            :labels="initiationData.labels"
                            height="200px" />
                        <div v-else class="h-32 flex items-center justify-center text-gray-500">
                            No initiation data available
                        </div>
                    </div>
                </Card>

                <!-- Continuation Breakdown -->
                <Card class="p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <HeartPulse class="w-5 h-5 text-indigo-600" />
                        <h3 class="font-semibold text-gray-800 dark:text-white">Continuation Status</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-lg">
                            <div class="text-lg font-bold text-teal-600">{{ continuationPatients.length }}</div>
                            <p class="text-sm text-teal-800 dark:text-teal-200">Continuing PrEP</p>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">
                            <div class="text-lg font-bold text-amber-600">
                                {{ initiatedPatients.length - continuationPatients.length }}
                            </div>
                            <p class="text-sm text-amber-800 dark:text-amber-200">Initiated but Not Continuing</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <BarChart 
                            v-if="initiatedPatients.length > 0"
                            :series="continuationData.series"
                            :categories="continuationData.categories"
                            :colors="['#0ea5e9']"
                            height="200px" />
                        <div v-else class="h-32 flex items-center justify-center text-gray-500">
                            No continuation data available
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Distribution Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <!-- Client Profile Distribution -->
                <Card class="p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <UserCheck class="w-5 h-5 text-indigo-600" />
                        <h3 class="font-semibold text-gray-800 dark:text-white">Client Profile Distribution</h3>
                    </div>
                    <PieChart 
                        v-if="clientProfileData.labels.length > 0 && clientProfileData.series.some(s => s > 0)"
                        :series="clientProfileData.series"
                        :labels="clientProfileData.labels"
                        height="300px" />
                    <div v-else class="h-64 flex items-center justify-center text-gray-500">
                        No client profile data available
                    </div>
                </Card>

                <!-- Facility Distribution -->
                <Card class="p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <BookUser class="w-5 h-5 text-indigo-600" />
                        <h3 class="font-semibold text-gray-800 dark:text-white">Facility Distribution</h3>
                    </div>
                    <BarChart 
                        v-if="facilityData.labels.length > 0 && facilityData.series.some(s => s > 0)"
                        :series="[{ name: 'Patients', data: facilityData.series }]"
                        :categories="facilityData.labels"
                        :colors="['#8b5cf6']"
                        height="300px"
                        horizontal />
                    <div v-else class="h-64 flex items-center justify-center text-gray-500">
                        No facility data available
                    </div>
                </Card>
            </div>

            <!-- Patient Details Table -->
            <Card class="p-4 mt-6">
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Patient Details</h3>
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facility</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Profile</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Screened</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Eligible</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Initiated</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Follow-up</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="patient in filteredData" :key="patient.record"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ patient.record }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ patient.health_facility || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ patient.gender || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ patient.client_profile || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <span v-if="patient.prepr_clinical_eligible !== null" 
                                            class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            Yes
                                        </span>
                                        <span v-else class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            No
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <span v-if="patient.prepr_clinical_eligible === '1'" 
                                            class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            Yes
                                        </span>
                                        <span v-else-if="patient.prepr_clinical_eligible === '0'" 
                                            class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                            No
                                        </span>
                                        <span v-else class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            N/A
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <span v-if="patient.prepr_date" 
                                            class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ new Date(patient.prepr_date).toLocaleDateString() }}
                                        </span>
                                        <span v-else class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Not Initiated
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <span v-if="patient.prep_visit_date" 
                                            class="px-2 py-1 text-xs rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300">
                                            {{ new Date(patient.prep_visit_date).toLocaleDateString() }}
                                        </span>
                                        <span v-else class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            No Follow-up
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.card-hover {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

@media (max-width: 640px) {
    .metric-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-grid {
        grid-template-columns: 1fr;
    }
}
</style>