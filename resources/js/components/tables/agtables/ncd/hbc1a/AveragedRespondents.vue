<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref, Ref, onMounted, h } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import { AgGridVue } from 'ag-grid-vue3';
import {
    AllCommunityModule,
    ModuleRegistry,
    themeAlpine,
    themeBalham,
    themeMaterial,
    themeQuartz,
} from "ag-grid-community";



ModuleRegistry.registerModules([AllCommunityModule]);

const props = defineProps<{
    tableData: any[];
}>();

const emit = defineEmits<{
  (event: 'record', id: string): void;
}>();

//
// Custom button renderer component
const components = {
    agGridButtonRenderer: (params: any) => {
        const onClick = () => params.onClick(params);
        return h(Button, { 
            onClick,
            class: 'text-xs py-1 px-4'
        }, () => 'Trend');
    }
};

// Define column definitions
const columnDefs = ref();

columnDefs.value = [
    { headerName: 'Record ID', field: 'record', sortable: true, filter: true, width: 150, innerHeight:20 },
    { headerName: 'Health Facility', field: 'health_facility', sortable: true, filter: true },
    { headerName: 'Age', field: 'age', sortable: true, filter: true },
    { headerName: 'Gender', field: 'gender', sortable: true, filter: true },
    { headerName: 'Minimum HbA1c', field: 'min_hba1c', sortable: true, filter: true },
    { headerName: 'Maximum HbA1c', field: 'max_hba1c', sortable: true, filter: true },
    { headerName: 'Mean HbA1c', field: 'mean', sortable: true, filter: true },
    { headerName: 'Standard Deviation', field: 'std_dev', sortable: true, filter: true },
    { headerName: 'Number of Visits', field: 'count', sortable: true, filter: true },
    {
        headerName: "View",
        cellRenderer: (params: { data: { record: string; }; }) => {
            const button = document.createElement('button');
            button.className = "inline-flex items-center justify-center px-4 py-1 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-orange-500 border border-transparent rounded-md hover:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 cursor-pointer";
            button.innerText = "Trend";
            button.onclick = () => viewRecord(params.data.record);
            return button;
        },
    }
]

const viewRecord = (record: string) => {
     emit('record', record);
};


// Transform statistics into row data
const rowData = ref([]);

const agGrid: Ref<any> = ref(null);
// Method to handle grid ready event
const onGridReady = (params: any) => {
    agGrid.value = params.api; // Store the grid API
    params.api.sizeColumnsToFit(); // Adjust column sizes
    updateFilteredRecordCount();

    // Listen for filter changes
    params.api.addEventListener('filterChanged', updateFilteredRecordCount);
};

const filteredRecordCount = ref(0);

const updateFilteredRecordCount = () => {
    console.log('agrid', agGrid.value)
    if (agGrid.value) {
        filteredRecordCount.value = agGrid.value.getDisplayedRowCount();
    }
};

const downloadCsv = () => {

    const gridApi = agGrid.value;
    console.log('AG Grid reference:', agGrid.value);
    if (gridApi) {
        gridApi.exportDataAsCsv();
    } else {
        console.error("AG Grid API is not available.");
    }
};



onMounted(() => {
});

const paginationPageSize = ref(100);
const paginationPageSizeSelector = ref<number[] | boolean>([10, 25, 50]);


</script>
<template>
    <div class="py-5">
        <Button @click="downloadCsv()">
            Download CSV
        </Button>
    </div>
    <div class="rounded-lg grid-container">
        <ag-grid-vue 
            :theme="themeQuartz"
            style="width: 100%; height: 1000px;" 
            @grid-ready="onGridReady" 
            :rowData="props.tableData"
            :columnDefs="columnDefs" 
            :defaultColDef="{ flex: 1, minWidth: 100 }" 
            :pagination="true"
            :paginationPageSize="paginationPageSize"
            :paginationPageSizeSelector="paginationPageSizeSelector"
            :headerHeight="40"
            :rowHeight="40"
        ></ag-grid-vue>
    </div>
</template>
<style scoped>
/* Custom header styles */
:deep(.ag-theme-quartz) {
    --ag-header-background-color: #f3f4f6; /* bg-gray-100 */
    --ag-header-foreground-color: #1f2937; /* gray-800 text */
    --ag-header-column-separator-color: #d1d5db; /* gray-300 */
    --ag-header-cell-hover-background-color: #e5e7eb; /* gray-200 hover */
    --ag-row-height: 40px;
}

/* Adjust header height if needed (usually handled by the headerHeight prop) */
:deep(.ag-theme-quartz .ag-header) {
    height: 25px;
    min-height: 25px;
}

</style>