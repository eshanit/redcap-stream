<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import BarChart from '@/components/charts/apexcharts/ahp/BarChart.vue';
import PieChart from '@/components/charts/apexcharts/ahp/PieChart.vue';
import {
    UserCheck, UserX, Percent, Users, Syringe, Pill, PillBottle,
    Filter, CircleDot, Baby, HeartHandshake ,ChevronDown, BarChart2, PieChart as PieChartIcon
} from 'lucide-vue-next';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

const props = defineProps<{
    project: IProject,
    stiScreened: Array<any>,
    stiTreated: Array<any>,  // New prop for treated cases
    contraceptionMethods: Array<any> 
}>();

const activeTab = ref('screening');
const genderFilter = ref<string[]>([]);
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
        title: 'Sexual and Reproductive Health',
        href: ''
    }
];

// Computed properties
// const filteredData = computed(() => {
//     return props.stiScreened.filter(patient => {
//         const genderMatch = genderFilter.value.length === 0 || genderFilter.value.includes(patient.gender);
//         const profileMatch = clientProfileFilter.value.length === 0 || 
//                             clientProfileFilter.value.includes(patient.client_profile);
//         return genderMatch && profileMatch;
//     });
// });

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
    'Unknown': [null, null]
};

// Helper to calculate age in months
const calculateAgeInMonths = (birthDate: string, visitDate: string) => {
    if (!birthDate || !visitDate) return null;
    const birth = new Date(birthDate);
    const visit = new Date(visitDate);
    const diffMonths = (visit.getFullYear() - birth.getFullYear()) * 12 +
        (visit.getMonth() - birth.getMonth());
    return diffMonths;
};

// Assign age group
// Assign age group
const assignAgeGroup = (ageInMonths: number | null) => {
    if (ageInMonths === null) return 'Unknown';

    for (const [group, [min, max]] of Object.entries(AGE_GROUPS)) {
        // Skip the 'Unknown' group for calculation
        if (group === 'Unknown') continue;

        if (max === null) {
            if (ageInMonths >= min) return group;
        } else {
            if (ageInMonths >= min && ageInMonths <= max) return group;
        }
    }
    return 'Unknown';
};



// Filtered data with visit date
const filteredData = computed(() => {
    return enhancedData.value.filter(patient => {
        const genderMatch = genderFilter.value.length === 0 || genderFilter.value.includes(patient.gender);
        const profileMatch = clientProfileFilter.value.length === 0 ||
            clientProfileFilter.value.includes(patient.client_profile);
        return genderMatch && profileMatch;
    });
});


const totalPatients = computed(() => filteredData.value.length);
const totalScreened = computed(() => filteredData.value.filter(p => p.sti_screened).length);
const totalNotScreened = computed(() => totalPatients.value - totalScreened.value);
const screenedPercentage = computed(() =>
    totalPatients.value > 0 ? Math.round((totalScreened.value / totalPatients.value) * 100) : 0
);

const genderData = computed(() => {
    const genders = [...new Set(filteredData.value.map(p => p.gender || 'Unknown'))];
    const screenedByGender = genders.map(gender =>
        filteredData.value.filter(p => p.gender === gender && p.sti_screened).length
    );
    const notScreenedByGender = genders.map(gender =>
        filteredData.value.filter(p => p.gender === gender && !p.sti_screened).length
    );

    return {
        categories: genders,
        series: [
            { name: 'Screened', data: screenedByGender },
            { name: 'Not Screened', data: notScreenedByGender }
        ]
    };
});

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

const facilityData = computed(() => {
    const facilities = [...new Set(filteredData.value.map(p => p.health_facility || 'Unknown'))];
    const screenedByFacility = facilities.map(facility =>
        filteredData.value.filter(p => p.health_facility === facility && p.sti_screened).length
    );
    const notScreenedByFacility = facilities.map(facility =>
        filteredData.value.filter(p => p.health_facility === facility && !p.sti_screened).length
    );

    return {
        categories: facilities,
        series: [
            { name: 'Screened', data: screenedByFacility },
            { name: 'Not Screened', data: notScreenedByFacility }
        ]
    };
});

//

// New date utilities
const formatDate = (dateString: string) => {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString();
};

const getMonthYear = (dateString: string) => {
    if (!dateString) return 'Unknown';
    const date = new Date(dateString);
    return `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
};



const enhancedData = computed(() => {
    return props.stiScreened.map(patient => {
        const visitDate = patient.anc_date || patient.art_review_date || patient.sti_date || null;
        const ageInMonths = patient.date_of_birth && visitDate ?
            calculateAgeInMonths(patient.date_of_birth, visitDate) : null;
        const ageGroup = assignAgeGroup(ageInMonths);

        return {
            ...patient,
            visitDate,
            visitType: patient.anc_date ? 'ANC' : patient.art_review_date ? 'ART' : patient.sti_date ? 'STI' : 'Unknown',
            ageInMonths,
            ageGroup
        };
    });
});



// Time-based screening analysis
const screeningTrendData = computed(() => {
    const monthlyData: Record<string, { screened: number, notScreened: number }> = {};

    filteredData.value.forEach(patient => {
        if (!patient.visitDate) return;

        const monthYear = getMonthYear(patient.visitDate);
        if (!monthlyData[monthYear]) {
            monthlyData[monthYear] = { screened: 0, notScreened: 0 };
        }

        if (patient.sti_screened) {
            monthlyData[monthYear].screened++;
        } else {
            monthlyData[monthYear].notScreened++;
        }
    });

    // Convert to chart data
    const categories = Object.keys(monthlyData).sort();
    return {
        categories,
        series: [
            {
                name: 'Screened',
                data: categories.map(date => monthlyData[date].screened)
            },
            {
                name: 'Not Screened',
                data: categories.map(date => monthlyData[date].notScreened)
            }
        ]
    };
});

// Visit type distribution
const visitTypeData = computed(() => {
    const types: Record<string, number> = {};

    filteredData.value.forEach(patient => {
        const type = patient.visitType;
        types[type] = (types[type] || 0) + 1;
    });

    return {
        labels: Object.keys(types),
        series: Object.values(types)
    };
});


//

const toggleGenderFilter = (gender: string) => {
    const index = genderFilter.value.indexOf(gender);
    if (index > -1) {
        genderFilter.value.splice(index, 1);
    } else {
        genderFilter.value.push(gender);
    }
};

const toggleProfileFilter = (profile: string) => {
    const index = clientProfileFilter.value.indexOf(profile);
    if (index > -1) {
        clientProfileFilter.value.splice(index, 1);
    } else {
        clientProfileFilter.value.push(profile);
    }
};

const clearFilters = () => {
    genderFilter.value = [];
    clientProfileFilter.value = [];
};



// Age group screening rates
const ageGroupRateData = computed(() => {
    const rates: Record<string, number> = {};

    Object.keys(AGE_GROUPS).forEach(group => {
        const groupPatients = filteredData.value.filter(p => p.ageGroup === group);
        if (groupPatients.length > 0) {
            const screenedCount = groupPatients.filter(p => p.sti_screened).length;
            rates[group] = Math.round((screenedCount / groupPatients.length) * 100);
        } else {
            rates[group] = 0;
        }
    });

    return {
        categories: Object.keys(rates),
        series: Object.values(rates)
    };
});

const ageGroupAnalysis = computed(() => {
    const groups = Object.keys(AGE_GROUPS);
    const result = {
        distribution: {
            labels: [] as string[],
            series: [] as number[]
        },
        screening: {
            categories: [] as string[],
            series: [
                { name: 'Screened', data: [] as number[] },
                { name: 'Not Screened', data: [] as number[] }
            ]
        },
        rates: {
            categories: [] as string[],
            series: [] as number[]
        }
    };

    groups.forEach(group => {
        const groupPatients = filteredData.value.filter(p => p.ageGroup === group);
        const screenedCount = groupPatients.filter(p => p.sti_screened).length;
        const notScreenedCount = groupPatients.length - screenedCount;

        result.distribution.labels.push(group);
        result.distribution.series.push(groupPatients.length);

        result.screening.categories.push(group);
        result.screening.series[0].data.push(screenedCount);
        result.screening.series[1].data.push(notScreenedCount);

        result.rates.categories.push(group);
        result.rates.series.push(groupPatients.length > 0 ?
            Math.round((screenedCount / groupPatients.length) * 100) : 0);
    });

    return result;
});

// ====== TREATMENT ANALYSIS ======

const enhancedTreatmentData = computed(() => {
    return props.stiTreated.map(patient => {
        const visitDate = patient.anc_date || patient.art_review_date || null;
        const ageInMonths = patient.date_of_birth && visitDate ?
            calculateAgeInMonths(patient.date_of_birth, visitDate) : null;
        const ageGroup = assignAgeGroup(ageInMonths);

        return {
            ...patient,
            visitDate,
            visitType: patient.anc_date ? 'ANC' : patient.art_review_date ? 'ART' : 'Unknown',
            ageInMonths,
            ageGroup
        };
    });
});

// Treatment filtered data
const filteredTreatmentData = computed(() => {
    return enhancedTreatmentData.value.filter(patient => {
        const genderMatch = genderFilter.value.length === 0 || genderFilter.value.includes(patient.gender);
        const profileMatch = clientProfileFilter.value.length === 0 ||
            clientProfileFilter.value.includes(patient.client_profile);
        return genderMatch && profileMatch;
    });
});

// Treatment metrics
const totalPatientsTreated = computed(() => filteredTreatmentData.value.length);
const totalTreated = computed(() => filteredTreatmentData.value.filter(p => p.sti_treated).length);
const totalNotTreated = computed(() => totalPatientsTreated.value - totalTreated.value);
const treatedPercentage = computed(() =>
    totalPatientsTreated.value > 0 ? Math.round((totalTreated.value / totalPatientsTreated.value) * 100) : 0
);

// Treatment by gender
const genderTreatmentData = computed(() => {
    const genders = [...new Set(filteredTreatmentData.value.map(p => p.gender || 'Unknown'))];
    const treatedByGender = genders.map(gender =>
        filteredTreatmentData.value.filter(p => p.gender === gender && p.sti_treated).length
    );
    const notTreatedByGender = genders.map(gender =>
        filteredTreatmentData.value.filter(p => p.gender === gender && !p.sti_treated).length
    );

    return {
        categories: genders,
        series: [
            { name: 'Treated', data: treatedByGender },
            { name: 'Not Treated', data: notTreatedByGender }
        ]
    };
});

// Treatment trend over time
const treatmentTrendData = computed(() => {
    const monthlyData: Record<string, { treated: number, notTreated: number }> = {};

    filteredTreatmentData.value.forEach(patient => {
        if (!patient.visitDate) return;

        const monthYear = getMonthYear(patient.visitDate);
        if (!monthlyData[monthYear]) {
            monthlyData[monthYear] = { treated: 0, notTreated: 0 };
        }

        if (patient.sti_treated) {
            monthlyData[monthYear].treated++;
        } else {
            monthlyData[monthYear].notTreated++;
        }
    });

    const categories = Object.keys(monthlyData).sort();
    return {
        categories,
        series: [
            { name: 'Treated', data: categories.map(date => monthlyData[date].treated) },
            { name: 'Not Treated', data: categories.map(date => monthlyData[date].notTreated) }
        ]
    };
});

// Treatment by facility
const facilityTreatmentData = computed(() => {
    const facilities = [...new Set(filteredTreatmentData.value.map(p => p.health_facility || 'Unknown'))];
    const treatedByFacility = facilities.map(facility =>
        filteredTreatmentData.value.filter(p => p.health_facility === facility && p.sti_treated).length
    );
    const notTreatedByFacility = facilities.map(facility =>
        filteredTreatmentData.value.filter(p => p.health_facility === facility && !p.sti_treated).length
    );

    return {
        categories: facilities,
        series: [
            { name: 'Treated', data: treatedByFacility },
            { name: 'Not Treated', data: notTreatedByFacility }
        ]
    };
});

// Treatment by age group
const ageGroupTreatmentData = computed(() => {
    const groups = Object.keys(AGE_GROUPS);
    const treatedByAge = groups.map(group =>
        filteredTreatmentData.value.filter(p => p.ageGroup === group && p.sti_treated).length
    );
    const notTreatedByAge = groups.map(group =>
        filteredTreatmentData.value.filter(p => p.ageGroup === group && !p.sti_treated).length
    );

    return {
        categories: groups,
        series: [
            { name: 'Treated', data: treatedByAge },
            { name: 'Not Treated', data: notTreatedByAge }
        ]
    };
});

// ====== CONTRACEPTION ANALYSIS ======
const enhancedContraceptionData = computed(() => {
    return props.contraceptionMethods.map(patient => {
        const visitDate = patient.fp_date || null;
        const ageInMonths = patient.date_of_birth && visitDate ? 
            calculateAgeInMonths(patient.date_of_birth, visitDate) : null;
        const ageGroup = assignAgeGroup(ageInMonths);
        
        // Handle multi-select contraception methods
        let methods = [];
        if (patient.contraceptions) {
            if (Array.isArray(patient.contraceptions)) {
                methods = patient.contraceptions;
            } else if (typeof patient.contraceptions === 'string' && patient.contraceptions.includes(',')) {
                methods = patient.contraceptions.split(',').map(m => m.trim());
            } else {
                methods = [patient.contraceptions];
            }
        }
        
        const wantsContraception = patient.fp_want_contra === '1';

        return {
            ...patient,
            visitDate,
            ageInMonths,
            ageGroup,
            contraceptionMethods: methods,
            wantsContraception,
            hasMethod: methods.length > 0
        };
    });
});

// Filtered contraception data
const filteredContraceptionData = computed(() => {
    return enhancedContraceptionData.value.filter(patient => {
        const genderMatch = genderFilter.value.length === 0 || genderFilter.value.includes(patient.gender);
        const profileMatch = clientProfileFilter.value.length === 0 ||
            clientProfileFilter.value.includes(patient.client_profile);
        return genderMatch && profileMatch;
    });
});

// Contraception metrics
const totalConsultations = computed(() => filteredContraceptionData.value.length);
const totalWantContraception = computed(() => filteredContraceptionData.value.filter(p => p.wantsContraception).length);
const totalAdopted = computed(() => filteredContraceptionData.value.filter(p => p.hasMethod).length);
const adoptionRate = computed(() => 
    totalWantContraception.value > 0 ? Math.round((totalAdopted.value / totalWantContraception.value) * 100) : 0
);

// Method distribution
const methodDistribution = computed(() => {
    const methodCounts: Record<string, number> = {};
    
    filteredContraceptionData.value.forEach(patient => {
        patient.contraceptionMethods.forEach(method => {
            if (method) {
                methodCounts[method] = (methodCounts[method] || 0) + 1;
            }
        });
    });
    
    return {
        labels: Object.keys(methodCounts),
        series: Object.values(methodCounts)
    };
});

// Contraception by gender
const contraceptionByGender = computed(() => {
    const genders = [...new Set(filteredContraceptionData.value.map(p => p.gender || 'Unknown'))];
    const wantByGender = genders.map(gender =>
        filteredContraceptionData.value.filter(p => p.gender === gender && p.wantsContraception).length
    );
    const adoptedByGender = genders.map(gender =>
        filteredContraceptionData.value.filter(p => p.gender === gender && p.hasMethod).length
    );

    return {
        categories: genders,
        series: [
            { name: 'Want Contraception', data: wantByGender },
            { name: 'Adopted Method', data: adoptedByGender }
        ]
    };
});

// Contraception trend
const contraceptionTrend = computed(() => {
    const monthlyData: Record<string, { consultations: number, adopted: number }> = {};

    filteredContraceptionData.value.forEach(patient => {
        if (!patient.visitDate) return;

        const monthYear = getMonthYear(patient.visitDate);
        if (!monthlyData[monthYear]) {
            monthlyData[monthYear] = { consultations: 0, adopted: 0 };
        }
        
        monthlyData[monthYear].consultations++;
        if (patient.hasMethod) {
            monthlyData[monthYear].adopted++;
        }
    });

    const categories = Object.keys(monthlyData).sort();
    return {
        categories,
        series: [
            { name: 'Consultations', data: categories.map(date => monthlyData[date].consultations) },
            { name: 'Adopted Methods', data: categories.map(date => monthlyData[date].adopted) }
        ]
    };
});


</script>

<template>

    <Head title="STI Screening Analysis" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4">
                <Heading as="h1" class="text-2xl font-bold text-gray-900 dark:text-white">
                    STI Analysis Dashboard
                </Heading>
                <p class="text-gray-600 dark:text-gray-400">
                    Analysis of STI screening and treatment for {{ project.app_title }} patients
                </p>
            </div>

            <!--Tab Navigation-->

            <Tabs v-model="activeTab" class="w-full">
                <TabsList class="grid w-full grid-cols-3">
                    <TabsTrigger value="screening">
                        <UserCheck class="w-4 h-4 mr-2" /> Screening Analysis
                    </TabsTrigger>
                    <TabsTrigger value="treatment">
                        <Syringe class="w-4 h-4 mr-2" /> Treatment Analysis
                    </TabsTrigger>
                    <TabsTrigger value="contraception">
                        <Pill class="w-4 h-4 mr-2" /> Contraception Analysis   
                    </TabsTrigger>
                </TabsList>

                <!-- SCREENING TAB -->
                <TabsContent value="screening">
                    <!-- ... existing screening content ... -->
                    <!-- Filter Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex justify-between items-center">
                            <button @click="showFilters = !showFilters"
                                class="flex items-center gap-2 text-sm font-medium">
                                <FilterIcon class="w-4 h-4" />
                                Filters
                                <ChevronDown class="w-4 h-4 transition-transform"
                                    :class="{ 'rotate-180': showFilters }" />
                            </button>
                            <button v-if="genderFilter.length > 0 || clientProfileFilter.length > 0"
                                @click="clearFilters"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                Clear all filters
                            </button>
                        </div>

                        <div v-if="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Gender Filter -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</h3>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="gender in ['Female', 'Male']" :key="gender"
                                        @click="toggleGenderFilter(gender)"
                                        class="px-3 py-1 text-xs rounded-full border transition-colors" :class="{
                                            'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300': genderFilter.includes(gender),
                                            'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700': !genderFilter.includes(gender)
                                        }">
                                        {{ gender }}
                                    </button>
                                </div>
                            </div>

                            <!-- Client Profile Filter -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client Profile
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="profile in ['General Population', 'Key Populations', 'Priority Populations']"
                                        :key="profile" @click="toggleProfileFilter(profile)"
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
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Total Patients</CardTitle>
                                <Users class="w-5 h-5 text-gray-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ totalPatients }}</div>
                                <p class="text-xs text-gray-500 mt-1">All screened patients</p>
                            </CardContent>
                        </Card>

                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Screened</CardTitle>
                                <UserCheck class="w-5 h-5 text-green-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-green-600">{{ totalScreened }}</div>
                                <p class="text-xs text-gray-500 mt-1">Completed STI screening</p>
                            </CardContent>
                        </Card>

                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Not Screened</CardTitle>
                                <UserX class="w-5 h-5 text-red-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-red-600">{{ totalNotScreened }}</div>
                                <p class="text-xs text-gray-500 mt-1">Pending STI screening</p>
                            </CardContent>
                        </Card>

                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Screened %</CardTitle>
                                <Percent class="w-5 h-5 text-blue-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-blue-600">{{ screenedPercentage }}%</div>
                                <p class="text-xs text-gray-500 mt-1">Completion rate</p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- New Charts Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Screening Trend Over Time -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Screening Trend Over Time</h3>
                            </div>
                            <BarChart v-if="screeningTrendData.categories.length" :series="screeningTrendData.series"
                                :categories="screeningTrendData.categories" :colors="['#10b981', '#ef4444']"
                                height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No time-based data available
                            </div>
                        </Card>

                        <!-- Visit Type Distribution -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <PieChartIcon class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Visit Type Distribution</h3>
                            </div>
                            <PieChart v-if="visitTypeData.labels.length" :series="visitTypeData.series"
                                :labels="visitTypeData.labels" :colors="['#3B82F6', '#8B5CF6', '#6366F1']"
                                height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No visit type data available
                            </div>
                        </Card>
                    </div>
                    <!-- Charts Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Gender Distribution -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Screening by Gender</h3>
                            </div>
                            <BarChart v-if="genderData.categories.length" :series="genderData.series"
                                :categories="genderData.categories" :colors="['#10b981', '#ef4444']" height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No gender data available
                            </div>
                        </Card>

                        <!-- Client Profile Distribution -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <PieChartIcon class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Client Profile Distribution</h3>
                            </div>
                            <PieChart v-if="clientProfileData.labels.length" :series="clientProfileData.series"
                                :labels="clientProfileData.labels" height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No client profile data available
                            </div>
                        </Card>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Screening by Age Group</h3>
                            </div>
                            <BarChart v-if="ageGroupAnalysis.screening.categories.length"
                                :series="ageGroupAnalysis.screening.series"
                                :categories="ageGroupAnalysis.screening.categories" :colors="['#10b981', '#ef4444']"
                                height="600px" horizontal />
                            <div v-else class="h-80 flex items-center justify-center text-gray-500">
                                No age group data available
                            </div>
                        </Card>

                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Age Group Screening Rates -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Screening Rate by Age Group</h3>
                            </div>
                            <BarChart v-if="ageGroupAnalysis.rates.categories.length"
                                :series="[{ name: 'Screening Rate', data: ageGroupAnalysis.rates.series }]"
                                :categories="ageGroupAnalysis.rates.categories" :colors="['#3B82F6']" height="500px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No screening rate data available
                            </div>
                        </Card>

                        <!-- Age Group Distribution -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <PieChartIcon class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Age Group Distribution</h3>
                            </div>
                            <!-- Age Group Distribution -->
                            <PieChart v-if="ageGroupAnalysis.distribution.labels.length"
                                :series="ageGroupAnalysis.distribution.series"
                                :labels="ageGroupAnalysis.distribution.labels" height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No age distribution data available
                            </div>
                        </Card>
                    </div>


                    <!-- Facility Performance -->
                    <Card class="p-4">
                        <div class="flex items-center gap-2 mb-4">
                            <BarChart2 class="w-5 h-5 text-indigo-600" />
                            <h3 class="font-semibold text-gray-800 dark:text-white">Facility Performance</h3>
                        </div>
                        <BarChart v-if="facilityData.categories.length" :series="facilityData.series"
                            :categories="facilityData.categories" :colors="['#10b981', '#ef4444']" height="350px"
                            horizontal />
                        <div v-else class="h-80 flex items-center justify-center text-gray-500">
                            No facility data available
                        </div>
                    </Card>

                    <Card class="p-4">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Patient Screening Details</h3>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Record ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Visit Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Age Group</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Visit Type</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Health Facility</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Gender</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Client Profile</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="patient in filteredData" :key="patient.record"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ patient.record }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(patient.visitDate) || 'N/A' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.ageGroup }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ patient.visitType }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.health_facility || 'N/A' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.gender || 'N/A' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.client_profile || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800': patient.sti_screened,
                                                    'bg-red-100 text-red-800': !patient.sti_screened
                                                }">
                                                {{ patient.sti_screened ? 'Screened' : 'Not Screened' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </TabsContent>

                <!-- TREATMENT TAB -->
                <TabsContent value="treatment">
                    <!-- Filter Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mt-4">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                            <div class="flex justify-between items-center">
                                <button @click="showFilters = !showFilters"
                                    class="flex items-center gap-2 text-sm font-medium">
                                    <FilterIcon class="w-4 h-4" />
                                    Filters
                                    <ChevronDown class="w-4 h-4 transition-transform"
                                        :class="{ 'rotate-180': showFilters }" />
                                </button>
                                <button v-if="genderFilter.length > 0 || clientProfileFilter.length > 0"
                                    @click="clearFilters"
                                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    Clear all filters
                                </button>
                            </div>

                            <div v-if="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Gender Filter -->
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</h3>
                                    <div class="flex flex-wrap gap-2">
                                        <button v-for="gender in ['Female', 'Male']" :key="gender"
                                            @click="toggleGenderFilter(gender)"
                                            class="px-3 py-1 text-xs rounded-full border transition-colors" :class="{
                                                'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300': genderFilter.includes(gender),
                                                'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700': !genderFilter.includes(gender)
                                            }">
                                            {{ gender }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Client Profile Filter -->
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client Profile
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="profile in ['General Population', 'Key Populations', 'Priority Populations']"
                                            :key="profile" @click="toggleProfileFilter(profile)"
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

                    </div>

                    <!-- Treatment Metrics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Patients</CardTitle>
                                <Users class="w-5 h-5 text-gray-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ totalPatientsTreated }}</div>
                                <p class="text-xs text-gray-500 mt-1">Patients requiring treatment</p>
                            </CardContent>
                        </Card>

                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Treated</CardTitle>
                                <PillBottle class="w-5 h-5 text-green-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-green-600">{{ totalTreated }}</div>
                                <p class="text-xs text-gray-500 mt-1">Completed STI treatment</p>
                            </CardContent>
                        </Card>

                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Not Treated</CardTitle>
                                <Pill class="w-5 h-5 text-red-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-red-600">{{ totalNotTreated }}</div>
                                <p class="text-xs text-gray-500 mt-1">Pending STI treatment</p>
                            </CardContent>
                        </Card>

                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Treated %</CardTitle>
                                <Percent class="w-5 h-5 text-blue-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-blue-600">{{ treatedPercentage }}%</div>
                                <p class="text-xs text-gray-500 mt-1">Treatment completion rate</p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Treatment Charts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                        <!-- Treatment Trend Over Time -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Treatment Trend Over Time</h3>
                            </div>
                            <BarChart v-if="treatmentTrendData.categories.length" :series="treatmentTrendData.series"
                                :categories="treatmentTrendData.categories" :colors="['#10b981', '#ef4444']"
                                height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No time-based data available
                            </div>
                        </Card>

                        <!-- Treatment by Gender -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Treatment by Gender</h3>
                            </div>
                            <BarChart v-if="genderTreatmentData.categories.length" :series="genderTreatmentData.series"
                                :categories="genderTreatmentData.categories" :colors="['#10b981', '#ef4444']"
                                height="300px" />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No gender data available
                            </div>
                        </Card>
                    </div>

                    <!-- Age Group Treatment -->
                    <div class="grid grid-cols-1 gap-6 mt-6">
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Treatment by Age Group</h3>
                            </div>
                            <BarChart v-if="ageGroupTreatmentData.categories.length"
                                :series="ageGroupTreatmentData.series" :categories="ageGroupTreatmentData.categories"
                                :colors="['#10b981', '#ef4444']" height="600px" horizontal />
                            <div v-else class="h-80 flex items-center justify-center text-gray-500">
                                No age group data available
                            </div>
                        </Card>
                    </div>

                    <!-- Facility Treatment Performance -->
                    <Card class="p-4 mt-6">
                        <div class="flex items-center gap-2 mb-4">
                            <BarChart2 class="w-5 h-5 text-indigo-600" />
                            <h3 class="font-semibold text-gray-800 dark:text-white">Facility Treatment Performance</h3>
                        </div>
                        <BarChart v-if="facilityTreatmentData.categories.length" :series="facilityTreatmentData.series"
                            :categories="facilityTreatmentData.categories" :colors="['#10b981', '#ef4444']"
                            height="350px" horizontal />
                        <div v-else class="h-80 flex items-center justify-center text-gray-500">
                            No facility data available
                        </div>
                    </Card>

                    <!-- Treatment Details Table -->
                    <Card class="p-4 mt-6">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Patient Treatment Details</h3>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Record ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Visit Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Age Group</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Visit Type</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Health Facility</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Gender</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="patient in filteredTreatmentData" :key="patient.record"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ patient.record }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(patient.visitDate) || 'N/A' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.ageGroup }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ patient.visitType }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.health_facility || 'N/A' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.gender || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800': patient.sti_treated,
                                                    'bg-red-100 text-red-800': !patient.sti_treated
                                                }">
                                                {{ patient.sti_treated ? 'Treated' : 'Not Treated' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </TabsContent>

                <TabsContent value="contraception">
                    <!-- Filter Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mt-4">
                        <div class="flex justify-between items-center">
                            <button @click="showFilters = !showFilters" class="flex items-center gap-2 text-sm font-medium">
                                <FilterIcon class="w-4 h-4" />
                                Filters
                                <ChevronDown class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showFilters }" />
                            </button>
                            <button v-if="genderFilter.length > 0 || clientProfileFilter.length > 0" @click="clearFilters"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                Clear all filters
                            </button>
                        </div>

                        <div v-if="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Gender Filter -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</h3>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="gender in ['Female', 'Male']" :key="gender"
                                        @click="toggleGenderFilter(gender)"
                                        class="px-3 py-1 text-xs rounded-full border transition-colors" :class="{
                                            'bg-blue-100 border-blue-500 text-blue-700 dark:bg-blue-900/30 dark:border-blue-600 dark:text-blue-300': genderFilter.includes(gender),
                                            'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700': !genderFilter.includes(gender)
                                        }">
                                        {{ gender }}
                                    </button>
                                </div>
                            </div>

                            <!-- Client Profile Filter -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client Profile</h3>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="profile in ['General Population', 'Key Populations', 'Priority Populations']"
                                        :key="profile" @click="toggleProfileFilter(profile)"
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

                    <!-- Contraception Metrics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Consultations</CardTitle>
                                <Users class="w-5 h-5 text-gray-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ totalConsultations }}</div>
                                <p class="text-xs text-gray-500 mt-1">Family planning consultations</p>
                            </CardContent>
                        </Card>

                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Want Contraception</CardTitle>
                                <CircleDot class="w-5 h-5 text-blue-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-blue-600">{{ totalWantContraception }}</div>
                                <p class="text-xs text-gray-500 mt-1">Expressed desire for contraception</p>
                            </CardContent>
                        </Card>

                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Adopted Methods</CardTitle>
                                <Baby class="w-5 h-5 text-green-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-green-600">{{ totalAdopted }}</div>
                                <p class="text-xs text-gray-500 mt-1">Received contraception method</p>
                            </CardContent>
                        </Card>

                        <Card class="hover:shadow-md transition-shadow">
                            <CardHeader class="flex flex-row items-center justify-between pb-2">
                                <CardTitle class="text-sm font-medium">Adoption Rate</CardTitle>
                                <Percent class="w-5 h-5 text-purple-500" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold text-purple-600">{{ adoptionRate }}%</div>
                                <p class="text-xs text-gray-500 mt-1">Of those who want contraception</p>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Contraception Charts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                        <!-- Method Distribution -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <PieChartIcon class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Contraception Methods Distribution</h3>
                            </div>
                            <PieChart 
                                v-if="methodDistribution.labels.length" 
                                :series="methodDistribution.series"
                                :labels="methodDistribution.labels" 
                                height="300px" 
                            />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No method data available
                            </div>
                        </Card>

                        <!-- Contraception by Gender -->
                        <Card class="p-4">
                            <div class="flex items-center gap-2 mb-4">
                                <BarChart2 class="w-5 h-5 text-indigo-600" />
                                <h3 class="font-semibold text-gray-800 dark:text-white">Contraception by Gender</h3>
                            </div>
                            <BarChart 
                                v-if="contraceptionByGender.categories.length" 
                                :series="contraceptionByGender.series"
                                :categories="contraceptionByGender.categories" 
                                :colors="['#3B82F6', '#10b981']" 
                                height="300px" 
                            />
                            <div v-else class="h-64 flex items-center justify-center text-gray-500">
                                No gender data available
                            </div>
                        </Card>
                    </div>

                    <!-- Trend Chart -->
                    <Card class="p-4 mt-6">
                        <div class="flex items-center gap-2 mb-4">
                            <BarChart2 class="w-5 h-5 text-indigo-600" />
                            <h3 class="font-semibold text-gray-800 dark:text-white">Contraception Trend Over Time</h3>
                        </div>
                        <BarChart 
                            v-if="contraceptionTrend.categories.length" 
                            :series="contraceptionTrend.series"
                            :categories="contraceptionTrend.categories" 
                            :colors="['#8B5CF6', '#10b981']" 
                            height="300px" 
                        />
                        <div v-else class="h-64 flex items-center justify-center text-gray-500">
                            No time-based data available
                        </div>
                    </Card>

                    <!-- Contraception Details Table -->
                    <Card class="p-4 mt-6">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Contraception Consultations</h3>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultation Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Group</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Health Facility</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wants Contraception</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Methods</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="patient in filteredContraceptionData" :key="patient.record" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ patient.record }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(patient.visitDate) || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.ageGroup }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.health_facility || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ patient.gender || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-blue-100 text-blue-800': patient.wantsContraception,
                                                    'bg-gray-100 text-gray-800': !patient.wantsContraception
                                                }"
                                            >
                                                {{ patient.wantsContraception ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-wrap gap-1">
                                                <span 
                                                    v-for="(method, index) in patient.contraceptionMethods" 
                                                    :key="index"
                                                    class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300"
                                                >
                                                    {{ method }}
                                                </span>
                                                <span v-if="patient.contraceptionMethods.length === 0" class="text-xs text-gray-500">
                                                    None
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </TabsContent>

            </Tabs>

        </div>
    </AppLayout>
</template>

<style scoped>
.tab-content {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

tr {
    transition: background-color 0.2s ease;
}

button {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

@media (max-width: 768px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        overflow-x: auto;
    }
}

@media (max-width: 768px) {
    .grid-cols-3 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}
</style>