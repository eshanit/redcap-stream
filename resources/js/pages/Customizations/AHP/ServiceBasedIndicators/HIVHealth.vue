<script setup lang="ts">
import { ref, Ref,computed } from 'vue';
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
    Activity, HeartPulse, BookUser, Baby, Syringe, Stethoscope, ClipboardList, TestTube2
} from 'lucide-vue-next';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const props = defineProps<{
    project: IProject,
    hivHealthData: Array<any>
}>();

const activeTab = ref('overview');
const genderFilter = ref<string[]>([]);
const facilityFilter = ref<string[]>([]);
const serviceTypeFilter = ref<string[]>([]);
const testStatusFilter = ref<string[]>(['Tested']);
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
        title: 'HIV Testing',
        href: ''
    }
];


// Enhanced data with calculated fields
const enhancedData = computed(() => {
    return props.hivHealthData.map(patient => {
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
        
        // Determine service type
        let serviceType = 'Other';
        let visitDate = null;
        let tested = false;
        let testResult = null;
        
        if (patient.anc_date || patient.anc_hiv_test_results !== null) {
            serviceType = 'ANC';
            visitDate = patient.anc_date;
        } else if (patient.pncm_visit_date || patient.pncm_hiv_tested === '1') {
            serviceType = 'PNCM';
            visitDate = patient.pncm_visit_date;
        } else if (patient.pncb_visit_date || patient.pncb_hiv_test_done === '1') {
            serviceType = 'PNCB';
            visitDate = patient.pncb_visit_date;
        } else if (patient.prep_visit_date || patient.prep_hiv_test === '1') {
            serviceType = 'PrEP';
            visitDate = patient.prep_visit_date;
        } else if (patient.sti_date || patient.sti_hiv_test === '1') {
            serviceType = 'STI';
            visitDate = patient.sti_date;
        }
        
        return {
            ...patient,
            age,
            ageGroup,
            serviceType,
            visitDate
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
        const serviceMatch = serviceTypeFilter.value.length === 0 ||
                          serviceTypeFilter.value.includes(patient.serviceType);
        const statusMatch = testStatusFilter.value.length === 0 ||
                          (testStatusFilter.value.includes('Tested') || patient.hiv_tested) ||
                          (testStatusFilter.value.includes('Not Tested') || !patient.hiv_tested);
        
        return genderMatch && facilityMatch && serviceMatch && statusMatch;
    });
});

// Metrics
const totalPatients = computed(() => filteredData.value.length);
const totalTested = computed(() => filteredData.value.filter(p => p.hiv_tested).length);
const totalNotTested = computed(() => totalPatients.value - totalTested.value);
const testedPercentage = computed(() => 
    totalPatients.value > 0 ? Math.round((totalTested.value / totalPatients.value) * 100) : 0
);

// Gender distribution
const genderData = computed(() => {
    const genders = [...new Set(filteredData.value.map(p => p.gender || 'Unknown'))];
    const testedByGender = genders.map(gender => 
        filteredData.value.filter(p => p.gender === gender && p.hiv_tested).length
    );
    const notTestedByGender = genders.map(gender => 
        filteredData.value.filter(p => p.gender === gender && !p.hiv_tested).length
    );

    return {
        categories: genders,
        series: [
            { name: 'Tested', data: testedByGender },
            { name: 'Not Tested', data: notTestedByGender }
        ]
    };
});

// Service type distribution
const serviceTypeData = computed(() => {
    const services = [...new Set(filteredData.value.map(p => p.serviceType || 'Unknown'))];
    const counts = services.map(service => 
        filteredData.value.filter(p => p.serviceType === service).length
    );
    
    return {
        labels: services,
        series: counts
    };
});

// Facility distribution
const facilityData = computed(() => {
    const facilities = [...new Set(filteredData.value.map(p => p.health_facility || 'Unknown'))];
    const testedByFacility = facilities.map(facility => 
        filteredData.value.filter(p => p.health_facility === facility && p.hiv_tested).length
    );
    const notTestedByFacility = facilities.map(facility => 
        filteredData.value.filter(p => p.health_facility === facility && !p.hiv_tested).length
    );

    return {
        categories: facilities,
        series: [
            { name: 'Tested', data: testedByFacility },
            { name: 'Not Tested', data: notTestedByFacility }
        ]
    };
});

// Test results distribution
const testResultsData = computed(() => {
    const results = filteredData.value
        .filter(p => p.anc_hiv_test_results !== null)
        .map(p => p.anc_hiv_test_results);
    
    const positive = results.filter(r => r === 'P').length;
    const negative = results.filter(r => r === 'N').length;
    const indeterminate = results.filter(r => r === null).length;
    
    return {
        labels: ['Positive', 'Negative', 'Indeterminate'],
        series: [positive, negative, indeterminate]
    };
});

// Service point breakdown
const servicePointData = computed(() => {
    const services = ['ANC', 'PNCM', 'PNCB', 'PrEP', 'STI'];
    const testedCounts = services.map(service => 
        filteredData.value.filter(p => p.serviceType === service && p.hiv_tested).length
    );
    const notTestedCounts = services.map(service => 
        filteredData.value.filter(p => p.serviceType === service && !p.hiv_tested).length
    );

    return {
        categories: services,
        series: [
            { name: 'Tested', data: testedCounts },
            { name: 'Not Tested', data: notTestedCounts }
        ]
    };
});

// Time trend data
const timeTrendData = computed(() => {
    const monthlyData: Record<string, { tested: number, notTested: number }> = {};
    
    filteredData.value.forEach(patient => {
        if (!patient.visitDate) return;
        
        const month = patient.visitDate.slice(0, 7); // YYYY-MM format
        if (!monthlyData[month]) {
            monthlyData[month] = { tested: 0, notTested: 0 };
        }
        
        if (patient.hiv_tested) {
            monthlyData[month].tested++;
        } else {
            monthlyData[month].notTested++;
        }
    });
    
    const categories = Object.keys(monthlyData).sort();
    return {
        categories,
        series: [
            { name: 'Tested', data: categories.map(m => monthlyData[m].tested) },
            { name: 'Not Tested', data: categories.map(m => monthlyData[m].notTested) }
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
    serviceTypeFilter.value = [];
    testStatusFilter.value = ['Tested'];
};

// Get unique values for filters
const uniqueGenders = computed(() => [...new Set(props.hivHealthData.map(p => p.gender))].filter(Boolean));
const uniqueFacilities = computed(() => [...new Set(props.hivHealthData.map(p => p.health_facility))].filter(Boolean));
const uniqueServiceTypes = computed(() => [...new Set(enhancedData.value.map(p => p.serviceType))].filter(Boolean));
</script>

<template>
    <Head title="HIV Testing Analysis" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4">
                <Heading as="h1" class="text-2xl font-bold text-gray-900 dark:text-white">
                    HIV Testing Analysis
                </Heading>
                <p class="text-gray-600 dark:text-gray-400">
                    Comprehensive analysis of HIV testing across service points for {{ project.app_title }} patients
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
                    
                    <div v-if="genderFilter.length > 0 || facilityFilter.length > 0 || serviceTypeFilter.length > 0 || testStatusFilter.length > 0">
                        <button @click="clearFilters" 
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 flex items-center gap-1">
                            Clear all filters
                        </button>
                    </div>
                </div>

                <div v-if="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                    
                    <!-- Service Type Filter -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service Type</h3>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="service in uniqueServiceTypes" 
                                :key="service" 
                                @click="toggleFilter(serviceTypeFilter, service)"
                                class="px-3 py-1 text-xs rounded-full border transition-colors" :class="{
                                    'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300': serviceTypeFilter.includes(service),
                                    'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700': !serviceTypeFilter.includes(service)
                                }">
                                {{ service }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Test Status -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Test Status</h3>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="status in ['Tested', 'Not Tested']" 
                                :key="status" 
                                @click="toggleFilter(testStatusFilter, status)"
                                class="px-3 py-1 text-xs rounded-full border transition-colors" :class="{
                                    'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300': testStatusFilter.includes(status),
                                    'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700': !testStatusFilter.includes(status)
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
                        <p class="text-xs text-gray-500 mt-1">Patients requiring testing</p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-shadow border border-green-100 dark:border-green-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Tested</CardTitle>
                        <UserCheck class="w-5 h-5 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ totalTested }}</div>
                        <p class="text-xs text-gray-500 mt-1">Completed testing</p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-shadow border border-amber-100 dark:border-amber-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Not Tested</CardTitle>
                        <UserX class="w-5 h-5 text-amber-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-amber-600">{{ totalNotTested }}</div>
                        <p class="text-xs text-gray-500 mt-1">Pending testing</p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-shadow border border-purple-100 dark:border-purple-900/50">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Testing Rate</CardTitle>
                        <Percent class="w-5 h-5 text-purple-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">{{ testedPercentage }}%</div>
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
                    <TabsTrigger value="services">
                        <TestTube2 class="w-4 h-4 mr-2" /> Service Points
                    </TabsTrigger>
                    <TabsTrigger value="details">
                        <BookUser class="w-4 h-4 mr-2" /> Details
                    </TabsTrigger>
                </TabsList>

                <!-- Overview Tab -->
                <TabsContent value="overview">
                    <!-- Testing Trend -->
                    <div class="mt-6">
                        <div class="insight-box bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-4">
                            <h3 class="font-semibold text-blue-800 mb-2">About the Testing Trend</h3>
                            <p class="text-sm text-blue-700">
                                This chart shows the monthly trend of HIV testing completion. The "Tested" bar represents patients 
                                who received an HIV test during their visit, while "Not Tested" shows those who did not. Data is 
                                grouped by month based on the visit date. The chart helps identify testing patterns over time.
                            </p>
                        </div>
                        
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Testing Trend Over Time</h3>
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
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                        <!-- Gender Distribution -->
                        <div>
                            <div class="insight-box bg-purple-50 border-l-4 border-purple-500 p-4 rounded mb-4">
                                <h3 class="font-semibold text-purple-800 mb-2">About Gender Distribution</h3>
                                <p class="text-sm text-purple-700">
                                    This pie chart shows the distribution of HIV testing by gender. Each slice represents the 
                                    proportion of tested patients for that gender. The chart helps identify any gender disparities 
                                    in testing rates. Data is calculated based on the gender recorded for each patient visit.
                                </p>
                            </div>
                            
                            <Card class="p-4 h-full">
                                <div class="flex items-center gap-2 mb-4">
                                    <PieChartIcon class="w-5 h-5 text-indigo-600" />
                                    <h3 class="font-semibold text-gray-800 dark:text-white">Testing by Gender</h3>
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

                        <!-- Facility Performance -->
                        <div>
                            <div class="insight-box bg-green-50 border-l-4 border-green-500 p-4 rounded mb-4">
                                <h3 class="font-semibold text-green-800 mb-2">About Facility Performance</h3>
                                <p class="text-sm text-green-700">
                                    This chart compares HIV testing rates across different health facilities. For each facility, 
                                    the blue bar shows tested patients and the red bar shows untested patients. The chart helps 
                                    identify high-performing facilities and those needing improvement. Data is grouped by the 
                                    facility where the service was provided.
                                </p>
                            </div>
                            
                            <Card class="p-4 h-full">
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
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                        <!-- Service Type Distribution -->
                        <div>
                            <div class="insight-box bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded mb-4">
                                <h3 class="font-semibold text-yellow-800 mb-2">About Service Types</h3>
                                <p class="text-sm text-yellow-700">
                                    This chart shows the distribution of HIV testing across different service points. Each slice 
                                    represents the proportion of tests conducted in that service category (ANC, PNCM, PNCB, PrEP, 
                                    or STI). The chart helps identify which services are contributing most to HIV testing efforts.
                                </p>
                            </div>
                            
                            <Card class="p-4 h-full">
                                <div class="flex items-center gap-2 mb-4">
                                    <TestTube2 class="w-5 h-5 text-indigo-600" />
                                    <h3 class="font-semibold text-gray-800 dark:text-white">Service Type Distribution</h3>
                                </div>
                                <!-- <pre>
                                    {{ serviceTypeData }}
                                </pre> -->
                                <PieChart v-if="serviceTypeData.labels.length" 
                                    :series="serviceTypeData.series"
                                    :labels="serviceTypeData.labels" 
                                    height="300px" />
                                <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                    No service type data available
                                </div>
                            </Card>
                        </div>
                    </div>
                </TabsContent>

                <!-- Service Points Tab -->
                <TabsContent value="services">
                    <!-- Service Point Breakdown -->
                    <div class="mt-6">
                        <div class="insight-box bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded mb-4">
                            <h3 class="font-semibold text-indigo-800 mb-2">About Service Point Testing</h3>
                            <p class="text-sm text-indigo-700">
                                This chart compares HIV testing rates across different service points. For each service category 
                                (ANC, PNCM, PNCB, PrEP, STI), the blue bar shows tested patients and the red bar shows untested 
                                patients. The chart helps identify which service points have the highest testing rates and which 
                                need improvement.
                            </p>
                        </div>
                        
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Testing by Service Point</h3>
                            </div>
                            <BarChart v-if="servicePointData.categories.length" 
                                :series="servicePointData.series"
                                :categories="servicePointData.categories" 
                                :colors="['#10b981', '#ef4444']"
                                height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No service point data available
                            </div>
                        </Card>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                        <!-- ANC Results -->
                        <div>
                            <div class="insight-box bg-pink-50 border-l-4 border-pink-500 p-4 rounded mb-4">
                                <h3 class="font-semibold text-pink-800 mb-2">About ANC Results</h3>
                                <p class="text-sm text-pink-700">
                                    This chart shows the results distribution for HIV tests conducted during Antenatal Care (ANC) 
                                    visits. Results are categorized as Positive, Negative, or Indeterminate. The chart helps 
                                    monitor HIV prevalence among pregnant women seeking antenatal care.
                                </p>
                            </div>
                            
                            <Card class="p-4 h-full">
                                <div class="flex items-center gap-2 mb-4">
                                    <Stethoscope class="w-5 h-5 text-indigo-600" />
                                    <h3 class="font-semibold text-gray-800 dark:text-white">ANC Test Results</h3>
                                </div>
                                <PieChart v-if="testResultsData.labels.length" 
                                    :series="testResultsData.series"
                                    :labels="testResultsData.labels" 
                                    height="300px" />
                                <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                    No ANC results available
                                </div>
                            </Card>
                        </div>

                        <!-- PNCM Testing -->
                        <div>
                            <div class="insight-box bg-teal-50 border-l-4 border-teal-500 p-4 rounded mb-4">
                                <h3 class="font-semibold text-teal-800 mb-2">About PNCM Testing</h3>
                                <p class="text-sm text-teal-700">
                                    This section shows HIV testing status for Postnatal Care - Mothers (PNCM). "Tested" indicates 
                                    mothers who received an HIV test during postnatal care. "Not Tested" shows those who did not, 
                                    and "Not Applicable" indicates cases where testing wasn't appropriate. Data is based on the 
                                    'pncm_hiv_tested' field values.
                                </p>
                            </div>
                            
                            <Card class="p-4 h-full">
                                <div class="flex items-center gap-2 mb-4">
                                    <Baby class="w-5 h-5 text-indigo-600" />
                                    <h3 class="font-semibold text-gray-800 dark:text-white">PNCM Testing Status</h3>
                                </div>
                                <div v-if="filteredData.length > 0" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-300">Tested (Yes)</span>
                                        <span class="font-medium">
                                            {{ filteredData.filter(p => p.pncm_hiv_tested === '1').length }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-300">Not Tested (No)</span>
                                        <span class="font-medium">
                                            {{ filteredData.filter(p => p.pncm_hiv_tested === '0').length }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-300">Not Applicable</span>
                                        <span class="font-medium">
                                            {{ filteredData.filter(p => p.pncm_hiv_tested === '2').length }}
                                        </span>
                                    </div>
                                </div>
                                <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                    No PNCM data available
                                </div>
                            </Card>
                        </div>
                    </div>
                </TabsContent>

                <!-- Details Tab -->
                <TabsContent value="details">
                    <div class="mt-6">
                        <div class="insight-box bg-gray-50 border-l-4 border-gray-500 p-4 rounded mb-4">
                            <h3 class="font-semibold text-gray-800 mb-2">About Patient Details</h3>
                            <p class="text-sm text-gray-700">
                                This table shows detailed information for each patient visit. You can see the service type, testing 
                                status, and results. The status is determined based on whether an HIV test was completed during 
                                the visit. Results vary by service type: ANC shows test outcomes (Positive/Negative), while other 
                                services show whether testing occurred.
                            </p>
                        </div>
                        
                        <Card class="p-4">
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Patient Testing Details</h3>
                                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                   
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visit Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facility</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="patient in filteredData" :key="`${patient.record}-${patient.visit}`"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ patient.record }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ patient.visitDate?.slice(0, 10) || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ patient.ageGroup }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ patient.health_facility || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                    {{ patient.serviceType }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full font-semibold"
                                                    :class="{
                                                        'bg-green-100 text-green-800': patient.hiv_tested,
                                                        'bg-amber-100 text-amber-800': !patient.hiv_tested
                                                    }">
                                                    {{ patient.hiv_tested ? 'Tested' : 'Not Tested' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <span v-if="patient.anc_hiv_test_results" 
                                                    class="px-2 py-1 text-xs rounded-full" 
                                                    :class="{
                                                        'bg-red-100 text-red-800': patient.anc_hiv_test_results === 'Positive',
                                                        'bg-green-100 text-green-800': patient.anc_hiv_test_results === 'Negative',
                                                        'bg-yellow-100 text-yellow-800': patient.anc_hiv_test_results === 'Indeterminate'
                                                    }">
                                                    {{ patient.anc_hiv_test_results }}
                                                </span>
                                                <span v-else-if="patient.serviceType === 'PNCM'">
                                                    {{ patient.pncm_hiv_tested === '1' ? 'Tested' : 
                                                       patient.pncm_hiv_tested === '0' ? 'Not Tested' : 
                                                       patient.pncm_hiv_tested === '2' ? 'N/A' : 'N/A' }}
                                                </span>
                                                <span v-else-if="patient.serviceType === 'PNCB'">
                                                    {{ patient.pncb_hiv_test_done === '1' ? 'Tested' : 
                                                       patient.pncb_hiv_test_done === '0' ? 'Not Tested' : 'N/A' }}
                                                </span>
                                                <span v-else-if="patient.serviceType === 'PrEP'">
                                                    {{ patient.prep_hiv_test === '1' ? 'Tested' : 
                                                       patient.prep_hiv_test === '0' ? 'Not Tested' : 'N/A' }}
                                                </span>
                                                <span v-else-if="patient.serviceType === 'STI'">
                                                    {{ patient.sti_hiv_test === '1' ? 'Tested' : 
                                                       patient.sti_hiv_test === '0' ? 'Not Tested' : 'N/A' }}
                                                </span>
                                                <span v-else class="text-gray-500 text-xs">N/A</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </Card>
                    </div>
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
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
}
</style>