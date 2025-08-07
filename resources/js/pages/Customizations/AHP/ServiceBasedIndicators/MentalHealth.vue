<script setup lang="ts">
import { ref, Ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import BarChart from '@/components/charts/apexcharts/ahp/BarChart.vue';
import PieChart from '@/components/charts/apexcharts/ahp/PieChart.vue';
import {
    Filter, UserCheck, UserX, Percent, Users, ChevronDown, BarChart2, PieChart as PieChartIcon,
    BrainCircuit, Activity, Stethoscope, Calendar, HeartPulse, BookUser, ClipboardList
} from 'lucide-vue-next';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const props = defineProps<{
    project: IProject,
    mentalHealthData: Array<any>
}>();

const activeTab = ref('overview');
const genderFilter = ref<string[]>([]);
const facilityFilter = ref<string[]>([]);
const screeningStatusFilter = ref<string[]>(['Screened']);
const showFilters = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: props.project.app_title,
         href: '/packages/project/' + props.project.project_id + '/dashboard',
    },
    {
        title: 'Service Based Indicators',
        href: '/packages/project/' + props.project.project_id + '/delivery-indicators-1',
    },
    {
        title: 'Mental Health',
        href: ''
    }
];

// Enhanced data with calculated fields
const enhancedData = computed(() => {
    return props.mentalHealthData.map(patient => {
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
            ageGroup,
            screened: patient.mental_health_screened
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
        const statusMatch = screeningStatusFilter.value.length === 0 ||
                          (screeningStatusFilter.value.includes('Screened') && patient.screened) ||
                          (screeningStatusFilter.value.includes('Not Screened') && !patient.screened);
        
        return genderMatch && facilityMatch && statusMatch;
    });
});

// Metrics
const totalPatients = computed(() => filteredData.value.length);
const totalScreened = computed(() => filteredData.value.filter(p => p.screened).length);
const totalNotScreened = computed(() => totalPatients.value - totalScreened.value);
const screenedPercentage = computed(() => 
    totalPatients.value > 0 ? Math.round((totalScreened.value / totalPatients.value) * 100) : 0
);

// Gender distribution
const genderData = computed(() => {
    const genders = [...new Set(filteredData.value.map(p => p.gender || 'Unknown'))];
    const screenedByGender = genders.map(gender => 
        filteredData.value.filter(p => p.gender === gender && p.screened).length
    );
    const notScreenedByGender = genders.map(gender => 
        filteredData.value.filter(p => p.gender === gender && !p.screened).length
    );

    return {
        categories: genders,
        series: [
            { name: 'Screened', data: screenedByGender },
            { name: 'Not Screened', data: notScreenedByGender }
        ]
    };
});

// Facility distribution
const facilityData = computed(() => {
    const facilities = [...new Set(filteredData.value.map(p => p.health_facility || 'Unknown'))];
    const screenedByFacility = facilities.map(facility => 
        filteredData.value.filter(p => p.health_facility === facility && p.screened).length
    );
    const notScreenedByFacility = facilities.map(facility => 
        filteredData.value.filter(p => p.health_facility === facility && !p.screened).length
    );

    return {
        categories: facilities,
        series: [
            { name: 'Screened', data: screenedByFacility },
            { name: 'Not Screened', data: notScreenedByFacility }
        ]
    };
});

// Results distribution
const resultsData = computed(() => {
    const results = [...new Set(filteredData.value.map(p => p.mentalResult || 'No Result'))];
    const counts = results.map(result => 
        filteredData.value.filter(p => p.mentalResult === result).length
    );
    
    return {
        labels: results,
        series: counts
    };
});

// Management distribution
const managementData = computed(() => {
    const management = [...new Set(filteredData.value.map(p => p.mentalResultManagement || 'Not Managed'))];
    const counts = management.map(item => 
        filteredData.value.filter(p => p.mentalResultManagement === item).length
    );
    
    return {
        labels: management,
        series: counts
    };
});

// Age group screening rates
const ageGroupData = computed(() => {
    const groups = ['≤18 years', '19-35 years', '36-50 years', '51+ years', 'Unknown'];
    const screenedByAge = groups.map(group => 
        filteredData.value.filter(p => p.ageGroup === group && p.screened).length
    );
    const notScreenedByAge = groups.map(group => 
        filteredData.value.filter(p => p.ageGroup === group && !p.screened).length
    );

    return {
        categories: groups,
        series: [
            { name: 'Screened', data: screenedByAge },
            { name: 'Not Screened', data: notScreenedByAge }
        ]
    };
});

// Time trend data
const timeTrendData = computed(() => {
    const monthlyData: Record<string, { screened: number, notScreened: number }> = {};
    
    filteredData.value.forEach(patient => {
        if (!patient.art_review_date) return;
        
        const month = patient.art_review_date.slice(0, 7); // YYYY-MM format
        if (!monthlyData[month]) {
            monthlyData[month] = { screened: 0, notScreened: 0 };
        }
        
        if (patient.screened) {
            monthlyData[month].screened++;
        } else {
            monthlyData[month].notScreened++;
        }
    });
    
    const categories = Object.keys(monthlyData).sort();
    return {
        categories,
        series: [
            { name: 'Screened', data: categories.map(m => monthlyData[m].screened) },
            { name: 'Not Screened', data: categories.map(m => monthlyData[m].notScreened) }
        ]
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
    screeningStatusFilter.value = ['Screened'];
};

// Get unique values for filters
const uniqueGenders = computed(() => [...new Set(props.mentalHealthData.map(p => p.gender))].filter(Boolean));
const uniqueFacilities = computed(() => [...new Set(props.mentalHealthData.map(p => p.health_facility))].filter(Boolean));
</script>

<template>
    <Head title="Mental Health Analysis" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4">
                <Heading as="h1" class="text-2xl font-bold text-gray-900 dark:text-white">
                    Mental Health Screening Analysis
                </Heading>
                <p class="text-gray-600 dark:text-gray-400">
                    Comprehensive analysis of mental health screenings for {{ project.app_title }} patients
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
                    
                    <div v-if="genderFilter.length > 0 || facilityFilter.length > 0 || screeningStatusFilter.length > 0">
                        <button @click="clearFilters" 
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 flex items-center gap-1">
                            Clear all filters
                        </button>
                    </div>
                </div>

                <div v-if="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    
                    <!-- Screening Status -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Screening Status</h3>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="status in ['Screened', 'Not Screened']" 
                                :key="status" 
                                @click="toggleFilter(screeningStatusFilter, status)"
                                class="px-3 py-1 text-xs rounded-full border transition-colors" :class="{
                                    'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300': screeningStatusFilter.includes(status),
                                    'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700': !screeningStatusFilter.includes(status)
                                }">
                                {{ status }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card class="hover:shadow-lg transition-shadow border border-blue-100 dark:border-blue-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Total Patients</CardTitle>
                        <Users class="w-5 h-5 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ totalPatients }}</div>
                        <p class="text-xs text-gray-500 mt-1">Patients requiring screening</p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-shadow border border-green-100 dark:border-green-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Screened</CardTitle>
                        <UserCheck class="w-5 h-5 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ totalScreened }}</div>
                        <p class="text-xs text-gray-500 mt-1">Completed screening</p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-shadow border border-amber-100 dark:border-amber-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Not Screened</CardTitle>
                        <UserX class="w-5 h-5 text-amber-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-amber-600">{{ totalNotScreened }}</div>
                        <p class="text-xs text-gray-500 mt-1">Pending screening</p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-shadow border border-purple-100 dark:border-purple-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Screening Rate</CardTitle>
                        <Percent class="w-5 h-5 text-purple-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">{{ screenedPercentage }}%</div>
                        <p class="text-xs text-gray-500 mt-1">Completion rate</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Tabs Navigation -->
            <Tabs v-model="activeTab" class="w-full">
                <TabsList class="grid w-full grid-cols-3">
                    <TabsTrigger value="overview">
                        <Activity class="w-4 h-4 mr-2" /> Overview
                    </TabsTrigger>
                    <TabsTrigger value="results">
                        <HeartPulse class="w-4 h-4 mr-2" /> Results
                    </TabsTrigger>
                    <TabsTrigger value="details">
                        <BookUser class="w-4 h-4 mr-2" /> Details
                    </TabsTrigger>
                </TabsList>

                <!-- Overview Tab -->
                <TabsContent value="overview">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                        <!-- Screening Trend -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Screening Trend Over Time</h3>
                            </div>
                            <BarChart v-if="timeTrendData.categories.length" 
                                :series="timeTrendData.series"
                                :categories="timeTrendData.categories" 
                                :colors="['#10b981', '#ef4444']"
                                height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No time-based data available
                            </div>
                        </Card>

                        <!-- Gender Distribution -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <PieChartIcon class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Screening by Gender</h3>
                            </div>
                            <PieChart v-if="genderData.categories.length" 
                                :series="genderData.series[0].data"
                                :labels="genderData.categories" 
                                height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No gender data available
                            </div>
                        </Card>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                        <!-- Facility Performance -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <ClipboardList class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Facility Performance</h3>
                            </div>
                            <BarChart v-if="facilityData.categories.length" 
                                :series="facilityData.series"
                                :categories="facilityData.categories" 
                                :colors="['#10b981', '#ef4444']"
                                height="300px" horizontal />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No facility data available
                            </div>
                        </Card>

                        <!-- Age Group Screening -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BrainCircuit class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Screening by Age Group</h3>
                            </div>
                            <BarChart v-if="ageGroupData.categories.length" 
                                :series="ageGroupData.series"
                                :categories="ageGroupData.categories" 
                                :colors="['#10b981', '#ef4444']"
                                height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No age group data available
                            </div>
                        </Card>
                    </div>
                </TabsContent>

                <!-- Results Tab -->
                <TabsContent value="results">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                        <!-- Screening Results -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <HeartPulse class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Screening Results</h3>
                            </div>
                            <PieChart v-if="resultsData.labels.length" 
                                :series="resultsData.series"
                                :labels="resultsData.labels" 
                                height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No results data available
                            </div>
                        </Card>

                        <!-- Management Approaches -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <Stethoscope class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Management Approaches</h3>
                            </div>
                            <BarChart v-if="managementData.labels.length" 
                                :series="[{ name: 'Patients', data: managementData.series }]" 
                                :categories="managementData.labels" 
                                :colors="['#8b5cf6']"
                                height="300px" horizontal />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No management data available
                            </div>
                        </Card>
                    </div>
                </TabsContent>

                <!-- Details Tab -->
                <TabsContent value="details">
                    <Card class="mt-6">
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Patient Screening Details</h3>
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visit Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facility</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Management</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="patient in filteredData" :key="patient.record"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ patient.record }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ patient.art_review_date?.slice(0, 10) || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ patient.ageGroup }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ patient.health_facility || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ patient.gender || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full font-semibold"
                                                    :class="{
                                                        'bg-green-100 text-green-800': patient.screened,
                                                        'bg-amber-100 text-amber-800': !patient.screened
                                                    }">
                                                    {{ patient.screened ? 'Screened' : 'Not Screened' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <span v-if="patient.mentalResult" 
                                                    class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    {{ patient.mentalResult }}
                                                </span>
                                                <span v-else class="text-gray-500 text-xs">N/A</span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <span v-if="patient.mentalResultManagement" 
                                                    class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                    {{ patient.mentalResultManagement }}
                                                </span>
                                                <span v-else class="text-gray-500 text-xs">N/A</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </Card>
                </TabsContent>
            </Tabs>
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

.table-row {
    transition: background-color 0.2s ease;
}

@media (max-width: 640px) {
    .metric-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs-list {
        flex-direction: column;
    }
}
</style>