<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import numberWithSpaces from '@/Utilities/numberWithSpaces';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS for AG Grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme for AG Grid

const props = defineProps<
    {
        upcomingVisits: any
    }
>()

const columnDefUpComings = ref([
    { headerName: 'Record', field: 'record', sortable: true, filter: true },
    { headerName: 'Event', field: 'event', sortable: true, filter: true },
    { headerName: 'Review Date', field: 'ncd_visit_date', sortable: true, filter: true },
    { headerName: 'Next Review Date', field: 'ncd_next_review', sortable: true, filter: true },
    { headerName: 'Time To Review', field: 'days_difference', sortable: true, filter: true, cellClass: "text-orange-500" },
    { headerName: 'Status', field: 'status', sortable: true, filter: true, cellClass: "text-green-500" }
]);


const filteredRecordCount = ref(0);
const agGrid = ref(null);
// Method to handle grid ready event
const onGridReady = (params) => {
    agGrid.value = params.api; // Store the grid API
    params.api.sizeColumnsToFit(); // Adjust column sizes
    updateFilteredRecordCount();

    // Listen for filter changes
    params.api.addEventListener('filterChanged', updateFilteredRecordCount);
};

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

</script>
<template>
    <div class="flex gap-5">
        <SecondaryButton @click="downloadCsv()">
            Download CSV
        </SecondaryButton>
    </div>
    <div class="py-5">
        <strong>Number of Filtered Records: <span class="text-xl text-green-400">{{ filteredRecordCount }}</span></strong>
    </div>
    <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
        <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefUpComings" :rowData="upcomingVisits" :pagination="true"
            paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }" @grid-ready="onGridReady" />
    </div>
</template>
<style scoped>
.ag-theme-quartz {
    height: 100%;
    width: 100%;
}
</style>