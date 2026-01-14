<script setup lang="ts">
import { ref, Ref, computed } from 'vue';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    format?: (value: any) => string;
    class?: string;
}

interface PaginationData {
    items: any[];
    page: number;
    perPage: number;
    total: number;
    loading?: boolean;
}

const props = defineProps<{
    columns: Column[];
    data: PaginationData;
    title?: string;
    description?: string;
}>();

const emit = defineEmits<{
    paginate: [page: number];
    sort: [key: string, direction: 'asc' | 'desc'];
    refresh: [];
}>();

const currentSortKey = ref<string | null>(null);
const currentSortDirection = ref<'asc' | 'desc'>('asc');

const totalPages = computed(() => {
    return Math.ceil(props.data.total / props.data.perPage);
});

const pageRange = computed(() => {
    const current = props.data.page;
    const total = totalPages.value;
    const delta = 2;
    const range: number[] = [];
    const rangeWithDots: (number | string)[] = [];
    let l: number | null = null;

    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
            range.push(i);
        }
    }

    range.forEach((i) => {
        if (l !== null) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push('...');
            }
        }
        rangeWithDots.push(i);
        l = i;
    });

    return rangeWithDots;
});

const goToPage = (page: number | string) => {
    if (typeof page === 'number' && page !== props.data.page && page >= 1 && page <= totalPages.value) {
        emit('paginate', page);
    }
};

const previousPage = () => {
    if (props.data.page > 1) {
        goToPage(props.data.page - 1);
    }
};

const nextPage = () => {
    if (props.data.page < totalPages.value) {
        goToPage(props.data.page + 1);
    }
};

const handleSort = (key: string) => {
    if (currentSortKey.value === key) {
        currentSortDirection.value = currentSortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortKey.value = key;
        currentSortDirection.value = 'asc';
    }
    emit('sort', key, currentSortDirection.value);
};

const formatValue = (column: Column, value: any) => {
    if (column.format) {
        return column.format(value);
    }
    if (value === null || value === undefined) {
        return '-';
    }
    return value;
};

const getStatusClass = (value: string) => {
    switch (value) {
        case 'Late':
            return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400';
        case 'On Time':
            return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400';
        case 'Early':
            return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400';
        default:
            return 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400';
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div v-if="title" class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-lg">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ title }}</h3>
                    <p v-if="description" class="text-slate-600 dark:text-slate-300">{{ description }}</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="bg-white dark:bg-slate-700 rounded-xl px-4 py-2 border border-slate-200 dark:border-slate-600">
                        <div class="text-sm text-slate-500 dark:text-slate-400">Total Records</div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ data.total }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div v-if="$slots.controls" class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-lg">
            <slot name="controls" :emit="emit" />
        </div>

        <!-- Table Container -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden min-h-[600px]">
            <!-- Loading Overlay -->
            <div v-if="data.loading" class="absolute inset-0 bg-white/50 dark:bg-slate-800/50 flex items-center justify-center z-10 rounded-2xl">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-8 h-8 border-4 border-slate-200 dark:border-slate-700 border-t-blue-500 dark:border-t-blue-400 rounded-full animate-spin"></div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Loading data...</p>
                </div>
            </div>

            <!-- Table Header -->
            <div class="px-4 lg:px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white">Data</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Page {{ data.page }} of {{ totalPages }} • Showing {{ data.items.length }} of {{ data.total }} records
                        </p>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
                <table class="w-full min-w-full">
                    <thead class="sticky top-0 bg-slate-50 dark:bg-slate-900/50 z-10">
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th 
                                v-for="column in columns" 
                                :key="column.key"
                                class="px-4 lg:px-6 py-4 text-left whitespace-nowrap"
                            >
                                <div class="flex items-center gap-2 cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition-colors" @click="column.sortable && handleSort(column.key)">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                        {{ column.label }}
                                    </span>
                                    <span v-if="column.sortable && currentSortKey === column.key" class="text-xs">
                                        {{ currentSortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                    <span v-else-if="column.sortable" class="text-xs text-slate-400">⇅</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(row, index) in data.items" 
                            :key="index"
                            class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                        >
                            <td 
                                v-for="column in columns" 
                                :key="`${index}-${column.key}`"
                                class="px-4 lg:px-6 py-3"
                                :class="column.class"
                            >
                                <!-- Status Badge -->
                                <span v-if="column.key === 'status'" :class="getStatusClass(row[column.key])">
                                    {{ row[column.key] }}
                                </span>
                                <!-- Regular Cell -->
                                <span v-else class="text-slate-700 dark:text-slate-300">
                                    {{ formatValue(column, row[column.key]) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="data.items.length === 0">
                            <td :colspan="columns.length" class="px-4 lg:px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="font-medium">No data available</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="px-4 lg:px-6 py-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <!-- Pagination Info -->
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Showing <span class="font-semibold">{{ (data.page - 1) * data.perPage + 1 }}</span> to 
                        <span class="font-semibold">{{ Math.min(data.page * data.perPage, data.total) }}</span> of 
                        <span class="font-semibold">{{ data.total }}</span> results
                    </div>

                    <!-- Pagination Controls -->
                    <div class="flex items-center gap-1">
                        <!-- Previous Button -->
                        <button 
                            @click="previousPage"
                            :disabled="data.page === 1 || data.loading"
                            class="p-2 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
                            title="Previous page"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <!-- Page Numbers -->
                        <div class="flex items-center gap-1">
                            <button 
                                v-for="(page, index) in pageRange" 
                                :key="index"
                                @click="typeof page === 'number' && goToPage(page)"
                                :disabled="page === '...' || data.loading"
                                :class="[
                                    'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                                    page === data.page
                                        ? 'bg-blue-500 text-white'
                                        : page === '...'
                                        ? 'text-slate-500 cursor-default'
                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'
                                ]"
                            >
                                {{ page }}
                            </button>
                        </div>

                        <!-- Next Button -->
                        <button 
                            @click="nextPage"
                            :disabled="data.page === totalPages || data.loading"
                            class="p-2 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
                            title="Next page"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
table {
    border-collapse: separate;
    border-spacing: 0;
}

thead tr {
    background: transparent;
}

tbody tr:last-child {
    border-bottom: none;
}
</style>
