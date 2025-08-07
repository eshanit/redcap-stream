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
    BabyIcon, CalendarIcon, UserIcon, ClipboardListIcon, 
    BarChartIcon, PieChartIcon, FilterIcon 
} from 'lucide-vue-next';


const props = defineProps<{
    project: IProject,
    ancRegistrants: Array<any>,
    ancFirstBookings: Array<any>,
    ancVisits: Array<any>,
    pncVisits: Array<any>,
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
        href: '/packages/project/' + props.project.project_id + '/delivery-indicators-1',
    },
    {
        title: 'Martenal Health',
        href: ''
    }
];

// Define age groups in months
const AGE_GROUPS = {
    '≤2 months': [0, 2],
    '3-12 months': [3, 12],
    '13-24 months': [13, 24],
    '25-59 months': [25, 59],
    '5-9 years': [60, 119],
    '10-14 years': [120, 179],
    '15-19 years': [180, 239],
    '20-24 years': [240, 299],
    '25-29 years': [300, 359],
    '30-34 years': [360, 419],
    '35-39 years': [420, 479],
    '40-44 years': [480, 539],
    '45-49 years': [540, 599],
    '50+ years': [600, null],
};

// Helper to calculate age in months
const calculateAgeInMonths = (birthDate: string, referenceDate: string) => {
    const birth = new Date(birthDate);
    const reference = new Date(referenceDate);
    const diffMonths = (reference.getFullYear() - birth.getFullYear()) * 12 + 
                      (reference.getMonth() - birth.getMonth());
    return diffMonths;
};

// Assign age group
const assignAgeGroup = (ageInMonths: number) => {
    for (const [group, [min, max]] of Object.entries(AGE_GROUPS)) {
        if (max === null) {
            if (ageInMonths >= min) return group;
        } else {
            if (ageInMonths >= min && ageInMonths <= max) return group;
        }
    }
    return 'Unknown';
};

// Compute age distributions
const registrantAgeDistribution = computed(() => {
    const distribution: Record<string, number> = {};
    
    props.ancRegistrants.forEach(reg => {
        if (reg.date_of_birth && reg.ancr_date) {
            const ageMonths = calculateAgeInMonths(reg.date_of_birth, reg.ancr_date);
            const group = assignAgeGroup(ageMonths);
            distribution[group] = (distribution[group] || 0) + 1;
        }
    });
    
    return distribution;
});

const visitAgeDistribution = computed(() => {
    const distribution: Record<string, number> = {};
    
    props.ancVisits.forEach(visit => {
        if (visit.date_of_birth && visit.anc_date) {
            const ageMonths = calculateAgeInMonths(visit.date_of_birth, visit.anc_date);
            const group = assignAgeGroup(ageMonths);
            distribution[group] = (distribution[group] || 0) + 1;
        }
    });
    
    return distribution;
});

const pncAgeDistribution = computed(() => {
    const distribution: Record<string, number> = {};
    
    props.pncVisits.forEach(visit => {
        if (visit.date_of_birth && visit.pncm_visit_date) {
            const ageMonths = calculateAgeInMonths(visit.date_of_birth, visit.pncm_visit_date);
            const group = assignAgeGroup(ageMonths);
            distribution[group] = (distribution[group] || 0) + 1;
        }
    });
    
    return distribution;
});

// Prepare data for charts
const ageGroupChartData = computed(() => ({
    registrants: registrantAgeDistribution.value,
    visits: visitAgeDistribution.value,
    pnc: pncAgeDistribution.value
}));

// Active tab for age analysis
const activeAgeTab = ref('registrants');

// Filtered age groups
const filteredAgeGroups = ref<string[]>([]);

// Toggle age group filter
const toggleAgeGroupFilter = (group: string) => {
    if (filteredAgeGroups.value.includes(group)) {
        filteredAgeGroups.value = filteredAgeGroups.value.filter(g => g !== group);
    } else {
        filteredAgeGroups.value = [...filteredAgeGroups.value, group];
    }
};

// Clear all filters
const clearFilters = () => {
    filteredAgeGroups.value = [];
};
</script>

<template>
    <Head title="Age Group Analysis" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- ... existing header and metrics cards ... -->

            <!-- Age Group Analysis Section -->
            <Card class="border border-blue-100 rounded-xl overflow-hidden">
                <CardHeader class="bg-blue-50 p-6 border-b">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <CardTitle class="flex items-center gap-3">
                                <UserIcon class="w-6 h-6 text-blue-600" />
                                Age Group Analysis
                            </CardTitle>
                            <CardDescription>
                                Distribution of patients across different age cohorts
                            </CardDescription>
                        </div>
                        
                        <div class="flex gap-2">
                            <button 
                                @click="activeAgeTab = 'registrants'"
                                :class="['px-4 py-2 rounded-lg flex items-center gap-2 transition-all',
                                    activeAgeTab === 'registrants' 
                                        ? 'bg-blue-600 text-white shadow-md' 
                                        : 'bg-white text-gray-700 hover:bg-gray-100']"
                            >
                                <ClipboardListIcon class="w-4 h-4" />
                                Registrants
                            </button>
                            <button 
                                @click="activeAgeTab = 'visits'"
                                :class="['px-4 py-2 rounded-lg flex items-center gap-2 transition-all',
                                    activeAgeTab === 'visits' 
                                        ? 'bg-blue-600 text-white shadow-md' 
                                        : 'bg-white text-gray-700 hover:bg-gray-100']"
                            >
                                <CalendarIcon class="w-4 h-4" />
                                ANC Visits
                            </button>
                            <button 
                                @click="activeAgeTab = 'pnc'"
                                :class="['px-4 py-2 rounded-lg flex items-center gap-2 transition-all',
                                    activeAgeTab === 'pnc' 
                                        ? 'bg-blue-600 text-white shadow-md' 
                                        : 'bg-white text-gray-700 hover:bg-gray-100']"
                            >
                                <BabyIcon class="w-4 h-4" />
                                PNC Visits
                            </button>
                        </div>
                    </div>
                </CardHeader>
                
                <CardContent class="p-0">
                    <!-- Filter Section -->
                    <div class="p-6 bg-gray-50 border-b flex flex-wrap items-center gap-3">
                        <div class="flex items-center text-sm font-medium text-gray-700">
                            <FilterIcon class="w-4 h-4 mr-2" />
                            Filter Age Groups:
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="(_, group) in AGE_GROUPS"
                                :key="group"
                                @click="toggleAgeGroupFilter(group)"
                                :class="[
                                    'px-3 py-1 rounded-full text-sm transition-all',
                                    filteredAgeGroups.includes(group)
                                        ? 'bg-blue-600 text-white shadow-inner'
                                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                                ]"
                            >
                                {{ group }}
                            </button>
                            
                            <button
                                @click="clearFilters"
                                class="px-3 py-1 rounded-full text-sm bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </div>
                    <!-- <pre>
                        {{  ageGroupChartData}}
                    </pre> -->
                    <!-- Visualization Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
                        <!-- Bar Chart -->
                        <div class="bg-white rounded-lg p-4 border">
                            <div class="flex items-center gap-3 mb-6">
                                <BarChartIcon class="w-6 h-6 text-blue-500" />
                                <h3 class="text-lg font-semibold">Age Group Distribution</h3>
                            </div>
                            
                            <BarChart 
                                v-if="activeAgeTab"
                                chartDescription="Patient distribution by age group" 
                                :chartData="filteredAgeGroups.length > 0 ? Object.fromEntries(Object.entries(ageGroupChartData[activeAgeTab]).filter(([group]) => filteredAgeGroups.includes(group))): ageGroupChartData[activeAgeTab]"
                            />
                            
                            <div class="mt-6 text-sm text-gray-600 bg-blue-50 p-4 rounded-lg">
                                <p class="font-medium mb-2">Interpretation:</p>
                                <p>
                                    This chart shows the distribution of {{ 
                                        activeAgeTab === 'registrants' ? 'ANC registrants' : 
                                        activeAgeTab === 'visits' ? 'ANC visits' : 'PNC visits'
                                    }} across different age groups. The highest bars indicate the most prevalent age groups 
                                    in your patient population. This helps identify which demographics require more focused 
                                    healthcare services and outreach programs.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Pie Chart -->
                        <div class="bg-white rounded-lg p-4 border">
                            <div class="flex items-center gap-3 mb-6">
                                <PieChartIcon class="w-6 h-6 text-green-500" />
                                <h3 class="text-lg font-semibold">Age Group Proportions</h3>
                            </div>
                            
                            <PieChart 
                                v-if="activeAgeTab"
                                chartDescription="Percentage distribution"
                                :chartData="filteredAgeGroups.length > 0 ? Object.fromEntries(Object.entries(ageGroupChartData[activeAgeTab]).filter(([group]) => filteredAgeGroups.includes(group))): ageGroupChartData[activeAgeTab]"
                            />
                            
                            <div class="mt-6 text-sm text-gray-600 bg-green-50 p-4 rounded-lg">
                                <p class="font-medium mb-2">Key Insights:</p>
                                <p>
                                    The pie chart visualizes the proportional distribution of patients across age groups. 
                                    Larger segments represent dominant demographics. In maternal healthcare, we typically 
                                    expect the highest concentration in the 20-34 year age range. Significant proportions 
                                    outside this range may indicate special population needs or data collection anomalies.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data Table -->
                    <div class="px-6 pb-6">
                        <div class="bg-white rounded-lg border overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b">
                                <h3 class="font-semibold flex items-center gap-2">
                                    <ClipboardListIcon class="w-5 h-5" />
                                    Detailed Age Group Data
                                </h3>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                Age Group
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                Registrants
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                ANC Visits
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                PNC Visits
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                % of Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr 
                                            v-for="[group, counts] in Object.entries({
                                                ...AGE_GROUPS,
                                                'Unknown': [null, null]
                                            })"
                                            :key="group"
                                            class="hover:bg-blue-50 transition-colors"
                                            :class="{ 'bg-blue-50': filteredAgeGroups.includes(group) }"
                                        >
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="{
                                                        'bg-blue-100 text-blue-800': group === '20-24 years' || group === '25-29 years',
                                                        'bg-green-100 text-green-800': group === '15-19 years' || group === '30-34 years',
                                                        'bg-yellow-100 text-yellow-800': group.startsWith('≤') || group.startsWith('3-'),
                                                        'bg-red-100 text-red-800': group === '50+ years' || group === '45-49 years',
                                                        'bg-gray-100 text-gray-800': group === 'Unknown'
                                                    }"
                                                >
                                                    {{ group }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ registrantAgeDistribution[group] || 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ visitAgeDistribution[group] || 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ pncAgeDistribution[group] || 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">
                                                {{ activeAgeTab === 'registrants' ? registrantAgeDistribution[group] ? ((registrantAgeDistribution[group] / Object.values(registrantAgeDistribution).reduce((a, b) => a + b, 0) * 100).toFixed(1)) : '0.0' : activeAgeTab === 'visits' ? visitAgeDistribution[group]  ? ((visitAgeDistribution[group] / Object.values(visitAgeDistribution).reduce((a, b) => a + b, 0) * 100).toFixed(1)) : '0.0' : pncAgeDistribution[group] ? ((pncAgeDistribution[group] / Object.values(pncAgeDistribution).reduce((a, b) => a + b, 0)) * 100).toFixed(1) : '0.0' }}%
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 font-semibold">
                                        <tr>
                                            <td class="px-6 py-3 text-sm text-gray-900">Total</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">{{ Object.values(registrantAgeDistribution).reduce((a, b) => a + b, 0) }}</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">{{ Object.values(visitAgeDistribution).reduce((a, b) => a + b, 0) }}</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">{{ Object.values(pncAgeDistribution).reduce((a, b) => a + b, 0) }}</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        Analysis Notes
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p class="mb-2">
                                            • The 20-34 year age range typically accounts for 60-80% of maternal healthcare patients
                                        </p>
                                        <p class="mb-2">
                                            • Significant numbers in ≤24 months may indicate data collection errors
                                        </p>
                                        <p>
                                            • High percentages in 15-19 years suggest teen pregnancy programs are needed
                                        </p>
                                    </div>
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
/* Animation for tab switching */
.tab-content {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

/* Hover effects for table rows */
tr {
    transition: background-color 0.2s ease;
}

/* Smooth transitions for filter buttons */
button {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Responsive table */
@media (max-width: 768px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        overflow-x: auto;
    }
}
</style>