<script setup lang="ts">
import { ref, Ref, computed, watch } from 'vue';
import CustomPaginationTable from '@/components/tables/CustomPaginationTable.vue';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    format?: (value: any) => string;
    class?: string;
}

const props = defineProps<{
    allVisits: any;
    page?: number;
    perPage?: number;
    total?: number;
    loading?: boolean;
}>();

const emit = defineEmits<{
    paginate: [page: number];
}>();

const searchQuery = ref('');
const currentPage = ref(props.page || 1);
const itemsPerPage = ref(props.perPage || 100);

const columns: Column[] = [
    { key: 'record', label: 'Record ID', sortable: true, class: 'font-medium' },
    { key: 'event', label: 'Event', sortable: true },
    { key: 'proposed_dates', label: 'Proposed Date', sortable: true, format: (v) => v || '-' },
    { key: 'actual_dates', label: 'Actual Date', sortable: true, format: (v) => v || '-' },
    { key: 'days_difference', label: 'Days Diff', sortable: true },
    { key: 'status', label: 'Status', sortable: true }
];

// Transform raw data into flat rows
const transformData = () => {
    const data: any[] = [];
    if (!props.allVisits) return data;

    Object.keys(props.allVisits).forEach(recordKey => {
        const record = props.allVisits[recordKey];
        for (let i = 0; i < record.event_id.length; i++) {
            data.push({
                record: recordKey,
                event: record.event[i] || '-',
                visit_dates: record.visit_dates[i] || '-',
                proposed_dates: formatDate(record.proposed_dates[i]) || '-',
                actual_dates: formatDate(record.actual_dates[i]) || '-',
                days_difference: record.days_difference[i] || '-',
                human_readable: record.human_readable[i] || '-',
                status: record.status[i] || '-'
            });
        }
    });
    return data;
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return null;
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch {
        return dateString;
    }
};

// Reactive rows
const rows = ref<any[]>([]);

// Watch for data changes
watch(() => props.allVisits, (newData) => {
    if (newData && Object.keys(newData).length > 0) {
        console.log('AllVisits data received, transforming...', Object.keys(newData).length);
        rows.value = transformData();
        console.log('Transformed rows:', rows.value.length);
    } else {
        rows.value = [];
    }
}, { deep: true, immediate: true });

// Watch for prop changes in pagination
watch(() => props.page, (newPage) => {
    if (newPage) currentPage.value = newPage;
});

watch(() => props.perPage, (newPerPage) => {
    if (newPerPage) itemsPerPage.value = newPerPage;
});

// Computed pagination data for table
const paginationData = computed(() => ({
    items: rows.value,
    page: currentPage.value,
    perPage: itemsPerPage.value,
    total: props.total || rows.value.length,
    loading: props.loading || false
}));

// Stats
const onTimeCount = computed(() => rows.value.filter(r => r.status === 'On Time').length);
const lateCount = computed(() => rows.value.filter(r => r.status === 'Late').length);
const earlyCount = computed(() => rows.value.filter(r => r.status === 'Early').length);

// Handle pagination
const handlePaginate = (page: number) => {
    currentPage.value = page;
    emit('paginate', page);
};

// Handle refresh
const handleRefresh = () => {
    rows.value = transformData();
};

// Download CSV
const downloadCsv = () => {
    const csvContent = [
        // Headers
        columns.map(c => c.label).join(','),
        // Rows
        ...rows.value.map(row => 
            columns.map(col => {
                const value = row[col.key];
                // Escape quotes and wrap in quotes if contains comma
                const escaped = String(value || '').replace(/"/g, '""');
                return escaped.includes(',') ? `"${escaped}"` : escaped;
            }).join(',')
        )
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `all-visits-${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// Search filter
const filteredRows = computed(() => {
    if (!searchQuery.value) return rows.value;
    const query = searchQuery.value.toLowerCase();
    return rows.value.filter(row => 
        Object.values(row).some(val => 
            String(val).toLowerCase().includes(query)
        )
    );
});
</script>

<template>
    <CustomPaginationTable 
        :columns="columns"
        :data="paginationData"
        title="All Visits Overview"
        description="View and analyze all patient visits with status tracking and filtering capabilities."
        @paginate="handlePaginate"
    >
        <template #controls>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Search Bar -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search records, events, or status..."
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-all duration-200"
                    >
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <button 
                        @click="handleRefresh"
                        class="px-4 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-medium transition-all duration-200 hover:shadow-md flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                    
                    <button 
                        @click="downloadCsv"
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl font-medium transition-all duration-200 hover:shadow-lg hover:scale-105 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-r from-emerald-50 to-green-50 dark:from-slate-800/50 dark:to-slate-900/50 p-4 rounded-xl border border-emerald-100 dark:border-slate-700">
                    <div class="text-sm text-emerald-600 dark:text-emerald-400 mb-1">On Time Visits</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ onTimeCount }}</div>
                </div>
                <div class="bg-gradient-to-r from-red-50 to-rose-50 dark:from-slate-800/50 dark:to-slate-900/50 p-4 rounded-xl border border-red-100 dark:border-slate-700">
                    <div class="text-sm text-red-600 dark:text-red-400 mb-1">Late Visits</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ lateCount }}</div>
                </div>
                <div class="bg-gradient-to-r from-blue-50 to-sky-50 dark:from-slate-800/50 dark:to-slate-900/50 p-4 rounded-xl border border-blue-100 dark:border-slate-700">
                    <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">Early Visits</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ earlyCount }}</div>
                </div>
            </div>
        </template>
    </CustomPaginationTable>
</template>

<style scoped>
</style>
