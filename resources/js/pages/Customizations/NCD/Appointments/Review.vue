<script setup lang="ts">
import { ref, computed, reactive, watch, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import AllVisits from '@/components/tables/agtables/ncd/appointments/Review/AllVisits.vue';
import LatestVisits from '@/components/tables/agtables/ncd/appointments/Review/LatestVisits.vue';
import UpComingVisits from '@/components/tables/agtables/ncd/appointments/Review/UpComingVisits.vue';
import MissedReviews from '@/components/tables/agtables/ncd/appointments/Review/MissedReviews.vue';
import { AgGridVue } from 'ag-grid-vue3';
import { GridApi } from 'ag-grid-community';
import "ag-grid-community/styles/ag-grid.css";
import "ag-grid-community/styles/ag-theme-quartz.css";
import transformObjectToArray from '@/utilities/transformObjectToArray';
import numberWithSpaces from '@/utilities/numberWithSpaces';
import VueApexCharts from 'vue3-apexcharts';

const ApexChart = VueApexCharts;

interface EventData {
    proposed_appointment_date: string | null;
    actual_visit_date: string | null;
    status: string;
    days_difference: number;
}

interface AnalyticsItem {
    recordId: string;
    late: number;
    notLate: number;
    onTime: number;
    dash: number;
}

const activeTab = ref('allVisitList');
const loading = ref(false);

// State for lazy-loaded data
const allVisitsData = reactive({
    items: {} as any,
    page: 1,
    perPage: 100,
    total: 0,
    loaded: false,
    loading: false
});

const latestVisitsData = reactive({
    items: {} as any,
    page: 1,
    perPage: 100,
    total: 0,
    loaded: false,
    loading: false
});

const upcomingData = reactive({
    items: [] as any[],
    page: 1,
    perPage: 100,
    total: 0,
    loaded: false,
    loading: false
});

const missedData = reactive({
    items: [] as any[],
    page: 1,
    perPage: 100,
    total: 0,
    loaded: false,
    loading: false
});

const analyticsData = reactive({
    items: [] as AnalyticsItem[],
    page: 1,
    perPage: 100,
    total: 0,
    loaded: false,
    loading: false
});

const setActiveTab = async (tab: any) => {
    activeTab.value = tab;

    // Load data on-demand when tab is accessed
    if (tab === 'allVisitList' && !allVisitsData.loaded) {
        await loadAllVisits();
    } else if (tab === 'latestVisitList' && !latestVisitsData.loaded) {
        await loadLatestVisits();
    } else if (tab === 'futureVisitList' && !upcomingData.loaded) {
        await loadUpcoming();
    } else if (tab === 'defaulters' && !missedData.loaded) {
        await loadMissed();
    } else if (tab === 'visitAnalytics' && !analyticsData.loaded) {
        await loadAnalytics();
    }
};

const loadAllVisits = async (page: number = 1) => {
    if (allVisitsData.loading) return;
    allVisitsData.loading = true;
    try {
        const response = await fetch(`/api/project/${props.project.project_id}/appointment_reviews/all_visits?page=${page}`);
        const result = await response.json();

        // Data comes as an object keyed by record ID - merge pages if loading more
        if (page === 1) {
            allVisitsData.items = result.data;
        } else {
            allVisitsData.items = { ...allVisitsData.items, ...result.data };
        }

        allVisitsData.page = result.pagination.page;
        allVisitsData.total = result.pagination.total;
        allVisitsData.loaded = true;

        console.log('All visits loaded:', Object.keys(allVisitsData.items).length, 'records');
    } catch (error) {
        console.error('Error loading all visits:', error);
        allVisitsData.items = {};
    } finally {
        allVisitsData.loading = false;
    }
};

const loadLatestVisits = async (page: number = 1) => {
    if (latestVisitsData.loading) return;
    latestVisitsData.loading = true;
    try {
        const response = await fetch(`/api/project/${props.project.project_id}/appointment_reviews/latest_visits?page=${page}`);
        const result = await response.json();

        // Data comes as an object keyed by record ID
        if (page === 1) {
            latestVisitsData.items = result.data;
        } else {
            latestVisitsData.items = { ...latestVisitsData.items, ...result.data };
        }

        latestVisitsData.page = result.pagination.page;
        latestVisitsData.total = result.pagination.total;
        latestVisitsData.loaded = true;

        console.log('Latest visits loaded:', Object.keys(latestVisitsData.items).length, 'records');
    } catch (error) {
        console.error('Error loading latest visits:', error);
        latestVisitsData.items = {};
    } finally {
        latestVisitsData.loading = false;
    }
};

const loadUpcoming = async (page: number = 1) => {
    if (upcomingData.loading) return;
    upcomingData.loading = true;
    try {
        const response = await fetch(`/api/project/${props.project.project_id}/appointment_reviews/upcoming?page=${page}`);
        const result = await response.json();

        // Convert object to array with record property
        const newItems = Object.entries(result.data).map(([key, value]: any) => ({
            record: key,
            ...value
        }));

        if (page === 1) {
            upcomingData.items = newItems;
        } else {
            upcomingData.items = [...upcomingData.items, ...newItems];
        }

        upcomingData.page = result.pagination.page;
        upcomingData.total = result.pagination.total;
        upcomingData.loaded = true;

        console.log('Upcoming appointments loaded:', upcomingData.items.length, 'records');
    } catch (error) {
        console.error('Error loading upcoming appointments:', error);
        upcomingData.items = [];
    } finally {
        upcomingData.loading = false;
    }
};

const loadMissed = async (page: number = 1) => {
    if (missedData.loading) return;
    missedData.loading = true;
    try {
        const response = await fetch(`/api/project/${props.project.project_id}/appointment_reviews/missed?page=${page}`);
        const result = await response.json();

        // Convert object to array with key property
        const newItems = Object.entries(result.data).map(([key, value]: any) => ({
            key,
            ...value
        }));

        if (page === 1) {
            missedData.items = newItems;
        } else {
            missedData.items = [...missedData.items, ...newItems];
        }

        missedData.page = result.pagination.page;
        missedData.total = result.pagination.total;
        missedData.loaded = true;

        console.log('Missed appointments loaded:', missedData.items.length, 'records');
    } catch (error) {
        console.error('Error loading missed appointments:', error);
        missedData.items = [];
    } finally {
        missedData.loading = false;
    }
};

const loadAnalytics = async (page: number = 1) => {
    if (analyticsData.loading) return;
    analyticsData.loading = true;
    try {
        const response = await fetch(`/api/project/${props.project.project_id}/appointment_reviews/analytics?page=${page}`);
        const result = await response.json();

        // Convert object to array format for ag-grid
        const newItems = Object.entries(result.data).map(([key, record]: any) => ({
            recordId: key,
            late: record['Late Visits'] || 0,
            notLate: record['Early Visits'] || 0,
            onTime: record['On Time Visits'] || 0,
            dash: record['No Data'] || 0
        }));

        if (page === 1) {
            analyticsData.items = newItems;
        } else {
            analyticsData.items = [...analyticsData.items, ...newItems];
        }

        analyticsData.page = result.pagination.page;
        analyticsData.total = result.pagination.total;
        analyticsData.loaded = true;

        console.log('Analytics loaded:', analyticsData.items.length, 'records');
    } catch (error) {
        console.error('Error loading analytics:', error);
        analyticsData.items = [];
    } finally {
        analyticsData.loading = false;
    }
};

const props = defineProps<{
    project: IProject
    statusDistribution: any
    upcomingCount: number
    defaultersCount: number
}>();

// Load default tab data on component mount
onMounted(async () => {
    await loadAllVisits();
    allVisitsData.loaded = true;
});

const packageRoot = computed(() => {
    return props.project.project_id == 39 ? 'ncd_pplus' : 'ncd';
});

const countDefaulterStatus = computed(() => {
    const counts: Record<string, number> = {};
    missedData.items.forEach((item: any) => {
        const status = item.statusDefault;
        if (status) {
            counts[status] = (counts[status] || 0) + 1;
        }
    });
    return counts;
});

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
        title: 'Appointments Review',
        href: ''
    }
];

const handleBackClick = () => {
    router.get(`/packages/project/${props.project.project_id}/dashboard`);
};

const recordTrend = ref();

const columnDefsAnalytics = [
    { headerName: 'Record ID', field: 'recordId', sortable: true, filter: true },
    { headerName: 'Late Visits', field: 'late', sortable: true, filter: true, cellClass: "text-red-500" },
    { headerName: 'Not Late Visits', field: 'notLate', sortable: true, filter: true, cellClass: "text-green-500" },
    { headerName: 'On Time Visits', field: 'onTime', sortable: true, filter: true, cellClass: "text-sky-500" },
    { headerName: 'No Data', field: 'dash', sortable: true, filter: true },
    {
        headerName: "View",
        cellRenderer: (params: any) => {
            const button = document.createElement('button');
            button.className = "inline-flex items-center justify-center px-4 py-1 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-orange-500 border border-transparent rounded-md hover:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2";
            button.innerText = "Trend";
            button.onclick = () => viewRecord(params.data.recordId);
            return button;
        },
    }
];

// AG Grid configuration with virtual scrolling
const defaultColDef = computed(() => ({
    flex: 1,
    minWidth: 100,
    resizable: true,
}));

const viewRecord = (record: string) => {
    // Placeholder for trend view - implement as needed
    recordTrend.value = {
        record: record,
        trend: [{ status: [], date: [] }]
    };
};

const chartData = computed(() => transformObjectToArray(props.statusDistribution));
const chartSeries = computed(() => chartData.value.map((item: any) => item.value));
const chartOptions = computed(() => ({
    labels: chartData.value.map((item: any) => item.name),
    title: {
        text: 'Respondent Visits Distribution'
    }
}));

const agGrid = ref<GridApi | null>(null);

const downloadCsv = () => {
    const gridApi = agGrid.value;
    if (gridApi) {
        gridApi.exportDataAsCsv();
    } else {
        console.error("AG Grid API is not available.");
    }
};

const onGridReady = (params: { api: GridApi }) => {
    agGrid.value = params.api;
    params.api.sizeColumnsToFit();
};
</script>

<template>

    <Head :title="`${project.app_title} - Appointments Review`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-slate-900 dark:to-slate-800 p-4 lg:p-6">
            <div class="max-w-screen-2xl mx-auto">
                <!-- Header with Back Button -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
                    <div>
                        <div
                            class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-sm font-medium mb-4">
                            Appointment Analytics
                        </div>

                        <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white leading-tight">
                            Appointments Review Dashboard
                            <span class="block text-xl text-slate-600 dark:text-slate-300 font-normal mt-2">
                                {{ project.app_title }}
                            </span>
                        </h1>
                        <p class="text-lg text-slate-600 dark:text-slate-300 mt-3 max-w-3xl">
                            Comprehensive analysis of appointment adherence, visit patterns, and patient follow-up
                            trends across all facilities.
                        </p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button @click="handleBackClick"
                            class="px-6 py-2.5 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Dashboard
                        </button>

                        <div
                            class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                                <span class="text-2xl">✅</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ numberWithSpaces(statusDistribution['On Time'] || 0) }}
                                </div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">On Time Visits</div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl flex items-center justify-center">
                                <span class="text-2xl">⚠️</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ numberWithSpaces(statusDistribution['Late'] || 0) }}
                                </div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">Late Visits</div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center">
                                <span class="text-2xl">📅</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ upcomingCount }}
                                </div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">Upcoming Appointments</div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-orange-500 to-yellow-500 rounded-xl flex items-center justify-center">
                                <span class="text-2xl">⏰</span>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-slate-900 dark:text-white">
                                    {{ defaultersCount }}
                                </div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">Missed Reviews</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 mb-8">
                    <div class="flex flex-wrap gap-2 mb-8">
                        <button @click="setActiveTab('allVisitList')" :class="[
                            'px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2',
                            activeTab === 'allVisitList'
                                ? 'bg-blue-500 text-white shadow-lg transform scale-105'
                                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        ]">
                            <span>📋</span>
                            All Visits List
                        </button>

                        <button @click="setActiveTab('latestVisitList')" :class="[
                            'px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2',
                            activeTab === 'latestVisitList'
                                ? 'bg-blue-500 text-white shadow-lg transform scale-105'
                                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        ]">
                            <span>🕐</span>
                            Last Visit List
                        </button>

                        <button @click="setActiveTab('futureVisitList')" :class="[
                            'px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2',
                            activeTab === 'futureVisitList'
                                ? 'bg-blue-500 text-white shadow-lg transform scale-105'
                                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        ]">
                            <span>📈</span>
                            Upcoming Reviews
                        </button>

                        <button @click="setActiveTab('defaulters')" :class="[
                            'px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2',
                            activeTab === 'defaulters'
                                ? 'bg-blue-500 text-white shadow-lg transform scale-105'
                                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        ]">
                            <span>🚨</span>
                            Missed Reviews
                        </button>

                        <button @click="setActiveTab('visitAnalytics')" :class="[
                            'px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2',
                            activeTab === 'visitAnalytics'
                                ? 'bg-blue-500 text-white shadow-lg transform scale-105'
                                : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        ]">
                            <span>📊</span>
                            Visit Analytics
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="mt-6">
                        <!-- All Visits Tab -->
                        <div v-if="activeTab === 'allVisitList'" class="space-y-6">
                            <div
                                class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-800/50 dark:to-slate-900/50 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">All Visits Overview
                                </h3>
                                <p class="text-slate-600 dark:text-slate-300">
                                    This tab shows a list of <span class="font-bold text-blue-500">ALL</span> visits for
                                    each respondent and their visit status,
                                    indicating whether they were <span class="text-blue-500">Late</span>, <span
                                        class="text-green-500">Early</span>, or
                                    <span class="text-sky-400">On Time</span> for their appointments.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                                <div class="xl:col-span-3">
                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                        <AllVisits :all-visits="allVisitsData.items" :page="allVisitsData.page"
                                            :per-page="allVisitsData.perPage" :total="allVisitsData.total"
                                            :loading="allVisitsData.loading" @paginate="loadAllVisits" />
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                        <ApexChart type="pie" :options="chartOptions" :series="chartSeries" />
                                    </div>

                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                        <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Visit Status
                                            Summary</h4>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                                    <span class="text-slate-700 dark:text-slate-300">On Time</span>
                                                </div>
                                                <span class="font-bold text-slate-900 dark:text-white">{{
                                                    numberWithSpaces(statusDistribution['On Time']) }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                                                    <span class="text-slate-700 dark:text-slate-300">Late</span>
                                                </div>
                                                <span class="font-bold text-slate-900 dark:text-white">{{
                                                    numberWithSpaces(statusDistribution.Late) }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 bg-amber-500 rounded-full"></span>
                                                    <span class="text-slate-700 dark:text-slate-300">Early</span>
                                                </div>
                                                <span class="font-bold text-slate-900 dark:text-white">{{
                                                    numberWithSpaces(statusDistribution['Early']) }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 bg-slate-400 rounded-full"></span>
                                                    <span class="text-slate-700 dark:text-slate-300">No Data</span>
                                                </div>
                                                <span class="font-bold text-slate-900 dark:text-white">{{
                                                    numberWithSpaces(statusDistribution['-']) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Latest Visits Tab -->
                        <div v-if="activeTab === 'latestVisitList'" class="space-y-6">
                            <div
                                class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-800/50 dark:to-slate-900/50 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Latest Visits Overview
                                </h3>
                                <p class="text-slate-600 dark:text-slate-300">
                                    This tab shows the <span class="font-bold text-blue-500">LAST</span> visit for each
                                    respondent and its status,
                                    indicating whether they were <span class="text-blue-500">Late</span>, <span
                                        class="text-green-500">Early</span>, or
                                    <span class="text-sky-400">On Time</span> for their appointment.
                                </p>
                            </div>

                            <div
                                class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                <LatestVisits :latest-visits="latestVisitsData.items" :page="latestVisitsData.page"
                                    :per-page="latestVisitsData.perPage" :total="latestVisitsData.total"
                                    :loading="latestVisitsData.loading" @paginate="loadLatestVisits" />
                            </div>
                        </div>

                        <!-- Upcoming Visits Tab -->
                        <div v-if="activeTab === 'futureVisitList'" class="space-y-6">
                            <div
                                class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-800/50 dark:to-slate-900/50 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Upcoming Reviews</h3>
                                <p class="text-slate-600 dark:text-slate-300">
                                    This tab shows a list of <span class="font-bold text-blue-500">Upcoming</span>
                                    visits for each respondent
                                    with their respective <span class="font-bold text-blue-500">Proposed Visit
                                        Date</span>.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                                <div class="xl:col-span-3">
                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                        <UpComingVisits :upcoming-visits="upcomingData.items" :page="upcomingData.page"
                                            :per-page="upcomingData.perPage" :total="upcomingData.total"
                                            :loading="upcomingData.loading" @paginate="loadUpcoming" />
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div
                                        class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-8 shadow-lg">
                                        <div class="text-center">
                                            <div class="text-5xl font-bold text-white mb-2">{{ upcomingData.total }}
                                            </div>
                                            <div class="text-white/90 text-lg">Upcoming Appointments</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Missed Reviews Tab -->
                        <div v-if="activeTab === 'defaulters'" class="space-y-6">
                            <div
                                class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-800/50 dark:to-slate-900/50 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Missed Reviews
                                    Analysis</h3>
                                <p class="text-slate-600 dark:text-slate-300">
                                    This tab shows a list of <span class="font-bold text-blue-500">Defaulted</span>
                                    reviews with their
                                    respective appointment dates. <span class="text-orange-500">NS</span> indicates No
                                    Show.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                                <div class="xl:col-span-3">
                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                        <MissedReviews :missed-reviews="missedData.items" :page="missedData.page"
                                            :per-page="missedData.perPage" :total="missedData.total"
                                            :loading="missedData.loading" @paginate="loadMissed" />
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div class="bg-gradient-to-r from-rose-500 to-pink-500 rounded-xl p-8 shadow-lg">
                                        <div class="text-center">
                                            <div class="text-5xl font-bold text-white mb-2">{{ missedData.total }}</div>
                                            <div class="text-white/90 text-lg">Defaulted + Missed Appointments</div>
                                        </div>
                                    </div>

                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-slate-200 dark:border-slate-700">
                                        <div class="space-y-4">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 bg-teal-500 rounded-full"></span>
                                                    <span class="text-slate-700 dark:text-slate-300">Missed
                                                        Appointments</span>
                                                </div>
                                                <span class="text-2xl font-bold text-teal-500">{{
                                                    numberWithSpaces(countDefaulterStatus['Missed Appointment'])
                                                    }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 bg-orange-500 rounded-full"></span>
                                                    <span class="text-slate-700 dark:text-slate-300">Defaulters</span>
                                                </div>
                                                <span class="text-2xl font-bold text-orange-500">{{
                                                    numberWithSpaces(countDefaulterStatus['Defaulted']) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visit Analytics Tab -->
                        <div v-if="activeTab === 'visitAnalytics'" class="space-y-6">
                            <div class="flex justify-between items-center">
                                <div
                                    class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-slate-800/50 dark:to-slate-900/50 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Visit Analytics
                                    </h3>
                                    <p class="text-slate-600 dark:text-slate-300">
                                        This tab shows statistics for each record including visits that were
                                        <span class="font-bold text-blue-500">Defaulted, Not Defaulted, On Time</span>,
                                        and those with
                                        <span class="font-bold text-blue-500">No Data</span>. Click the Trend button to
                                        view individual record trends.
                                    </p>
                                </div>

                                <button @click="downloadCsv()"
                                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Download CSV
                                </button>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                                <div class="xl:col-span-3">
                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-lg border border-slate-200 dark:border-slate-700 h-[800px]">
                                        <div class="ag-theme-quartz h-full">
                                            <AgGridVue class="ag-theme-quartz h-full" :columnDefs="columnDefsAnalytics"
                                                :rowData="analyticsData.items" :pagination="true"
                                                :paginationPageSize="50" :defaultColDef="defaultColDef"
                                                :suppressPaginationPanel="false" @grid-ready="onGridReady" />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div
                                        class="bg-white dark:bg-slate-800 rounded-xl p-8 shadow-lg border border-slate-200 dark:border-slate-700 h-[800px] overflow-y-auto">
                                        <div v-if="recordTrend">
                                            <div class="mb-6">
                                                <h4 class="text-xl font-bold text-slate-900 dark:text-white">
                                                    Trend Analysis for <span class="text-green-400">{{
                                                        recordTrend.record }}</span>
                                                </h4>
                                            </div>
                                            <div class="space-y-4">
                                                <div v-for="(status, index) in recordTrend.trend" :key="index">
                                                    <div v-for="(statusDate, dateIndex) in status.date" :key="dateIndex"
                                                        class="flex justify-between items-center py-3 border-b border-slate-200 dark:border-slate-700">
                                                        <div class="text-slate-700 dark:text-slate-300">
                                                            {{ statusDate || '-' }}
                                                        </div>
                                                        <div>
                                                            <span v-if="status.status[dateIndex] === 'Late'"
                                                                class="px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-medium">
                                                                {{ status.status[dateIndex] }}
                                                            </span>
                                                            <span v-else-if="status.status[dateIndex] === 'Early'"
                                                                class="px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium">
                                                                {{ status.status[dateIndex] }}
                                                            </span>
                                                            <span v-else-if="status.status[dateIndex] === 'On Time'"
                                                                class="px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-sm font-medium">
                                                                {{ status.status[dateIndex] }}
                                                            </span>
                                                            <span v-else class="text-slate-400">-</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else
                                            class="flex flex-col items-center justify-center h-full text-center">
                                            <div
                                                class="w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No Record
                                                Selected</h4>
                                            <p class="text-slate-600 dark:text-slate-300">
                                                Click on the <span class="font-bold text-orange-500">Trend</span> button
                                                in the analytics table
                                                to view the review trend of a specific respondent.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                    <p>© {{ new Date().getFullYear() }} Redcap Stream • Appointments Review Dashboard • {{
                        project.app_title }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.ag-theme-quartz {
    height: 100%;
    width: 100%;
}

/* Custom text colors without theme() function */
:deep(.text-red-500) {
    color: #ef4444;
}

:deep(.text-sky-400) {
    color: #38bdf8;
}

:deep(.text-green-500) {
    color: #22c55e;
}
</style>