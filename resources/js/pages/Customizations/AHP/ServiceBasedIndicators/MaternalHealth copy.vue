<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
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
import BarChart from '@/components/charts/apexcharts/BarChart.vue';
import PieChart from '@/components/charts/apexcharts/PieChart.vue';
import LineChart from '@/components/charts/apexcharts/LineChart.vue';
import {
    UsersIcon,
    UserPlusIcon,
    CalendarCheckIcon,
    ActivityIcon,
    BabyIcon,
    HospitalIcon,
    UserIcon,
    TrendingUpIcon,
    ClipboardListIcon
} from 'lucide-vue-next';

const props = defineProps<{
    project: IProject,
    ancRegistrants: Array<any>,
    ancFirstBookings: Array<any>,
    ancVisits: Array<any>,
    pncVisits: Array<any>,
}>()

const breadcrumbs: BreadcrumbItem[] = [
    // ... your breadcrumbs
];

// Computed metrics
const totalRegistrants = computed(() => props.ancRegistrants.length);
const totalVisits = computed(() => props.ancVisits.length);
const totalPNCVisits = computed(() => props.pncVisits.length);

const firstTimeBookings = computed(() => 
    props.ancFirstBookings.filter(b => b.anc_first_booking === "Yes").length
);

const repeatBookings = computed(() => 
    props.ancFirstBookings.filter(b => b.anc_first_booking === "No").length
);

const avgVisitsPerPatient = computed(() => 
    totalRegistrants.value > 0 
        ? (totalVisits.value / totalRegistrants.value).toFixed(1)
        : 0
);

// Data for charts
const bookingTypeData = computed(() => ({
    'First-time': firstTimeBookings.value,
    'Returning': repeatBookings.value
}));

const facilityDistributionData = computed(() => {
    const facilities: Record<string, number> = {};
    props.ancRegistrants.forEach(reg => {
        const facility = reg.health_facility || 'Unknown';
        facilities[facility] = (facilities[facility] || 0) + 1;
    });
    return Object.entries(facilities)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5)
        .reduce((obj, [key, value]) => (obj[key] = value, obj), {} as Record<string, number>);
});

const monthlyVisitsData = computed(() => {
    const months: Record<string, number> = {};
    props.ancVisits.forEach(visit => {
        if (visit.anc_date) {
            const month = visit.anc_date.substring(0, 7);
            months[month] = (months[month] || 0) + 1;
        }
    });
    return Object.keys(months).sort().reduce((obj, key) => (obj[key] = months[key], obj), {} as Record<string, number>);
});

const pncVisitTypesData = computed(() => {
    const types: Record<string, number> = {};
    props.pncVisits.forEach(visit => {
        const type = visit.pncm_visit || 'Unknown';
        types[type] = (types[type] || 0) + 1;
    });
    return types;
});

// Active tab state
const activeTab = ref('overview');
</script>

<template>
    <Head title="Antenatal Care Analytics Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-6 md:p-8 shadow-sm">
                <Heading 
                    title="Antenatal Care Analytics" 
                    description="Comprehensive insights into maternal healthcare program performance"
                    class="pt-0"
                />
                
                <div class="flex flex-wrap gap-2 mt-4">
                    <button @click="activeTab = 'overview'" 
                        :class="['px-4 py-2 rounded-full transition-all duration-300', 
                                activeTab === 'overview' 
                                    ? 'bg-primary text-white shadow-md' 
                                    : 'bg-white text-gray-700 hover:bg-gray-100']">
                        Overview
                    </button>
                    <button @click="activeTab = 'details'" 
                        :class="['px-4 py-2 rounded-full transition-all duration-300', 
                                activeTab === 'details' 
                                    ? 'bg-primary text-white shadow-md' 
                                    : 'bg-white text-gray-700 hover:bg-gray-100']">
                        Detailed Analysis
                    </button>
                    <button @click="activeTab = 'facilities'" 
                        :class="['px-4 py-2 rounded-full transition-all duration-300', 
                                activeTab === 'facilities' 
                                    ? 'bg-primary text-white shadow-md' 
                                    : 'bg-white text-gray-700 hover:bg-gray-100']">
                        Facilities
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-5">
                <Card class="hover:shadow-lg transition-all duration-500 transform hover:-translate-y-1 border-l-4 border-blue-500">
                    <CardHeader class="flex flex-row items-center space-y-0 pb-2">
                        <UsersIcon class="h-6 w-6 text-blue-500" />
                        <div class="ml-3">
                            <CardTitle>Registrants</CardTitle>
                            <CardDescription>Total ANC Patients</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ totalRegistrants }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Women enrolled in ANC program
                        </p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-all duration-500 transform hover:-translate-y-1 border-l-4 border-green-500">
                    <CardHeader class="flex flex-row items-center space-y-0 pb-2">
                        <UserPlusIcon class="h-6 w-6 text-green-500" />
                        <div class="ml-3">
                            <CardTitle>First Bookings</CardTitle>
                            <CardDescription>New ANC Patients</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-green-600">{{ firstTimeBookings }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            New registrations in reporting period
                        </p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-all duration-500 transform hover:-translate-y-1 border-l-4 border-purple-500">
                    <CardHeader class="flex flex-row items-center space-y-0 pb-2">
                        <CalendarCheckIcon class="h-6 w-6 text-purple-500" />
                        <div class="ml-3">
                            <CardTitle>ANC Visits</CardTitle>
                            <CardDescription>Total Consultations</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-purple-600">{{ totalVisits }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            All ANC visits across facilities
                        </p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-all duration-500 transform hover:-translate-y-1 border-l-4 border-amber-500">
                    <CardHeader class="flex flex-row items-center space-y-0 pb-2">
                        <ActivityIcon class="h-6 w-6 text-amber-500" />
                        <div class="ml-3">
                            <CardTitle>Visits/Patient</CardTitle>
                            <CardDescription>Average Engagement</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-amber-600">{{ avgVisitsPerPatient }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Average ANC visits per patient
                        </p>
                    </CardContent>
                </Card>

                <Card class="hover:shadow-lg transition-all duration-500 transform hover:-translate-y-1 border-l-4 border-pink-500 lg:col-span-1 xl:col-auto">
                    <CardHeader class="flex flex-row items-center space-y-0 pb-2">
                        <BabyIcon class="h-6 w-6 text-pink-500" />
                        <div class="ml-3">
                            <CardTitle>PNC Visits</CardTitle>
                            <CardDescription>Postnatal Care</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold text-pink-600">{{ totalPNCVisits }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Postnatal consultations
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Section with Descriptions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Booking Type -->
                <Card class="transition-all duration-500 hover:shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6">
                        <CardHeader class="p-0">
                            <div class="flex items-center">
                                <UserIcon class="h-6 w-6 text-indigo-600 mr-3" />
                                <CardTitle>Patient Booking Types</CardTitle>
                            </div>
                            <CardDescription>New vs Returning Patients</CardDescription>
                        </CardHeader>
                        <CardContent class="px-0 pb-0 min-h-[300px]">
                            <PieChart 
                                chartDescription=""
                                :chartData="bookingTypeData"
                            />
                        </CardContent>
                    </div>
                    <div class="p-6 border-t">
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <ClipboardListIcon class="h-4 w-4 mr-2" />
                            <h3 class="font-medium">Insights</h3>
                        </div>
                        <p class="text-sm text-gray-600">
                            This chart shows the ratio of new patients to returning patients in the ANC program. 
                            A healthy program typically maintains a balance between acquiring new patients and 
                            retaining existing ones. Higher returning rates indicate good program retention.
                        </p>
                    </div>
                </Card>

                <!-- Monthly Visits Trend -->
                <Card class="transition-all duration-500 hover:shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 p-6">
                        <CardHeader class="p-0">
                            <div class="flex items-center">
                                <TrendingUpIcon class="h-6 w-6 text-amber-600 mr-3" />
                                <CardTitle>Monthly Visits Trend</CardTitle>
                            </div>
                            <CardDescription>ANC consultations over time</CardDescription>
                        </CardHeader>
                        <CardContent class="px-0 pb-0 min-h-[300px]">
                            <LineChart 
                                chartDescription="Visits per Month"
                                :chartData="monthlyVisitsData"
                            />
                        </CardContent>
                    </div>
                    <div class="p-6 border-t">
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <ClipboardListIcon class="h-4 w-4 mr-2" />
                            <h3 class="font-medium">Analysis</h3>
                        </div>
                        <p class="text-sm text-gray-600">
                            This timeline shows ANC visit patterns month-over-month. Seasonal trends may indicate 
                            external factors affecting healthcare access. Consistent growth suggests effective 
                            program outreach, while dips may require investigation into service disruptions.
                        </p>
                    </div>
                </Card>

                <!-- Top Facilities -->
                <Card class="transition-all duration-500 hover:shadow-xl overflow-hidden lg:col-span-2">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6">
                        <CardHeader class="p-0">
                            <div class="flex items-center">
                                <HospitalIcon class="h-6 w-6 text-green-600 mr-3" />
                                <CardTitle>Facility Performance</CardTitle>
                            </div>
                            <CardDescription>Patient distribution across top facilities</CardDescription>
                        </CardHeader>
                        <CardContent class="px-0 pb-0 min-h-[300px]">
                            <BarChart 
                                chartDescription=""
                                :chartData="facilityDistributionData"
                            />
                        </CardContent>
                    </div>
                    <div class="p-6 border-t">
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <ClipboardListIcon class="h-4 w-4 mr-2" />
                            <h3 class="font-medium">Facility Insights</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            This compares patient volumes across different healthcare facilities. Higher-volume 
                            facilities may indicate better accessibility or reputation. Consider investigating 
                            best practices at top-performing facilities to replicate in lower-volume locations.
                        </p>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Facility
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Patients
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            First-time
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Avg. Visits
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(facility, index) in Object.entries(facilityDistributionData).slice(0, 5)" 
                                        :key="index"
                                        class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ facility[0] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ facility[1] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ Math.round(facility[1] * 0.65) }} <!-- Sample data -->
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ (Math.random() * 2 + 2.5).toFixed(1) }} <!-- Sample data -->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- PNC Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- PNC Visit Types -->
                <Card class="transition-all duration-500 hover:shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-pink-50 to-rose-50 p-6">
                        <CardHeader class="p-0">
                            <div class="flex items-center">
                                <BabyIcon class="h-6 w-6 text-pink-600 mr-3" />
                                <CardTitle>PNC Visit Timing</CardTitle>
                            </div>
                            <CardDescription>Postnatal consultation distribution</CardDescription>
                        </CardHeader>
                        <CardContent class="px-0 pb-0 min-h-[300px]">
                            <PieChart 
                                chartDescription=""
                                :chartData="pncVisitTypesData"
                            />
                        </CardContent>
                    </div>
                    <div class="p-6 border-t">
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <ClipboardListIcon class="h-4 w-4 mr-2" />
                            <h3 class="font-medium">Timing Analysis</h3>
                        </div>
                        <p class="text-sm text-gray-600">
                            This shows the distribution of postnatal care visits by timing. The WHO recommends 
                            PNC visits within 48 hours, at 7 days, and at 6 weeks postpartum. This helps 
                            assess compliance with postnatal care guidelines.
                        </p>
                    </div>
                </Card>

                <!-- Client Profile -->
                <Card class="transition-all duration-500 hover:shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-violet-50 to-purple-50 p-6">
                        <CardHeader class="p-0">
                            <div class="flex items-center">
                                <UserIcon class="h-6 w-6 text-violet-600 mr-3" />
                                <CardTitle>Client Demographics</CardTitle>
                            </div>
                            <CardDescription>Age distribution of ANC patients</CardDescription>
                        </CardHeader>
                        <CardContent class="px-0 pb-0 min-h-[300px]">
                            <div class="h-full flex items-center justify-center text-gray-400">
                                <div class="text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p>Demographic chart visualization</p>
                                    <p class="text-sm mt-1">(Sample visualization - implement with your data)</p>
                                </div>
                            </div>
                        </CardContent>
                    </div>
                    <div class="p-6 border-t">
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <ClipboardListIcon class="h-4 w-4 mr-2" />
                            <h3 class="font-medium">Profile Insights</h3>
                        </div>
                        <p class="text-sm text-gray-600">
                            Understanding patient demographics helps tailor services to specific population needs. 
                            This could include breakdowns by age, education level, marital status, or geographic 
                            location to identify service gaps.
                        </p>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Smooth transitions and animations */
.card {
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), 
                box-shadow 0.4s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Gradient backgrounds */
.bg-gradient-to-r {
    background-size: 200% 200%;
    animation: gradientShift 15s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Fade-in animation for content */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card-content > * {
    animation: fadeIn 0.6s ease-out forwards;
}
</style>