<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import CustomPaginationTable from '@/components/tables/CustomPaginationTable.vue';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    format?: (value: any) => string;
    class?: string;
}

const props = defineProps<{
    missedReviews: any;
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
    { key: 'visit_dates', label: 'Visit Dates', sortable: true, format: (v) => v || '-' },
    { key: 'proposed_dates', label: 'Proposed Dates', sortable: true, format: (v) => v || '-' },
    { key: 'actual_dates', label: 'Actual Dates', sortable: true, format: (v) => v || '-' },
    { key: 'days_difference', label: 'Days Difference', sortable: true },
    { key: 'human_readable', label: 'Human Readable', sortable: true, format: (v) => v || '-' },
    { key: 'status', label: 'Status', sortable: true }
];

// Transform data into flat rows
const transformData = () => {
    const data: any[] = [];
    if (!props.missedReviews) return data;

    Object.entries(props.missedReviews).forEach(([recordKey, recordData]: [string, any]) => {
        data.push({
            record: recordKey,
            ...recordData
        });
    });
    return data;
};

// Reactive rows
const rows = ref<any[]>([]);

// Watch for data changes
watch(() => props.missedReviews, (newData) => {
    if (newData && Object.keys(newData).length > 0) {
        rows.value = transformData();
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

// Handle pagination
const handlePaginate = (page: number) => {
    currentPage.value = page;
    emit('paginate', page);
};

// Download CSV
const downloadCsv = () => {
    const csvContent = [
        columns.map(c => c.label).join(','),
        ...rows.value.map(row => 
            columns.map(col => {
                const value = row[col.key];
                const escaped = String(value || '').replace(/"/g, '""');
                return escaped.includes(',') ? `"${escaped}"` : escaped;
            }).join(',')
        )
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `missed-reviews-${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <CustomPaginationTable 
        :columns="columns"
        :data="paginationData"
        title="Missed Appointments"
        description="View appointments that were missed or not completed by patients."
        @paginate="handlePaginate"
    >
        <template #controls>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search records or events..."
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-all duration-200"
                    >
                </div>

                <button 
                    @click="downloadCsv"
                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl font-medium transition-all duration-200 hover:shadow-lg flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </button>
            </div>
        </template>
    </CustomPaginationTable>
</template>
