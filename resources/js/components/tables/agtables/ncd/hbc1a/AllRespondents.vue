<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref, Ref, onMounted } from 'vue';
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
    allResults: any[];
}>();


// Define column definitions
const columnDefs = ref();

columnDefs.value = [
    { headerName: 'Record ID', field: 'record', sortable: true, filter: true, width: 150, innerHeight: 20 },
    { headerName: 'Instance/Visit', field: 'event', sortable: true, filter: true },
    { headerName: 'Health Facility', field: 'ncd_health_facility', sortable: true, filter: true },
    { headerName: 'Age', field: 'ncd_age', sortable: true, filter: true },
    { headerName: 'Gender', field: 'ncd_gender', sortable: true, filter: true },
    { headerName: 'HbA1c', field: 'ncd_hb1ac', sortable: true, filter: true },
    { headerName: 'Visit Date', field: 'ncd_visit_date', sortable: true, filter: true },

]

const viewRecord = (record: string) => {
    router.visit('/items/' + record + '/edit', { method: 'get' });
    // console.log(record);
    //emit('record', record);
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
    <div class="flex justify-between py-8">
        <div>
            <Button @click="downloadCsv()">
                Download CSV
            </Button>
        </div>
        <div class="font-semibold">
            <span> Filtered Results:  </span> <span class="text-orange-500 text-3xl"> {{ filteredRecordCount }}</span>
        </div>

    </div>
    <!-- <pre>
        {{ props.allResults }}
    </pre> -->
    <div class="rounded-lg grid-container">
        <ag-grid-vue :theme="themeQuartz" style="width: 100%; height: 1000px;" @grid-ready="onGridReady"
            :rowData="props.allResults" :columnDefs="columnDefs" :defaultColDef="{ flex: 1, minWidth: 100 }"
            :pagination="true" :paginationPageSize="paginationPageSize"
            :paginationPageSizeSelector="paginationPageSizeSelector" :headerHeight="40" :rowHeight="40"></ag-grid-vue>
    </div>
</template>
<style scoped>
/* Custom header styles */
:deep(.ag-theme-quartz) {
    --ag-header-background-color: #f0f0f0;
    /* Light gray color */
    --ag-header-foreground-color: #000000;
    /* Text color */
}

/* Adjust header height if needed (usually handled by the headerHeight prop) */
:deep(.ag-theme-quartz .ag-header) {
    height: 25px;
    min-height: 25px;
}
</style>