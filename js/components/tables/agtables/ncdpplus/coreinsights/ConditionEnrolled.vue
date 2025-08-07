<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref, Ref, onMounted, computed } from 'vue';
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
    indicator: string
}>();


// Define column definitions
const columnDefs = ref();

const indicatorHeader = computed(() => {

    let columHeader = { headerName: 'Enrollment Date', field: 'initial_enrollment', sortable: true, filter: true }

    if (props.indicator == 'active') {
        columHeader = { headerName: 'Status', field: 'status', sortable: true, filter: true }
    } else if (props.indicator == 'threeMonthsDeath') {
        columHeader = { headerName: 'Date of Death', field: 'latest_outcome_date', sortable: true, filter: true }
    }


    return columHeader

})

columnDefs.value = [
    { headerName: 'Record ID', field: 'record', sortable: true, filter: true, width: 150, innerHeight: 20 },
    { headerName: 'Health Facility', field: 'facility', sortable: true, filter: true },
    { headerName: 'Age', field: 'age', sortable: true, filter: true },
    { headerName: 'Gender', field: 'real_gender', sortable: true, filter: true },
    indicatorHeader.value,
    // {
    //     headerName: "View",
    //     cellRenderer: (params: { data: { record: string; }; }) => {
    //         const button = document.createElement('button');
    //         button.className = "inline-flex items-center justify-center px-4 py-1 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-orange-500 border border-transparent rounded-md hover:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 cursor-pointer";
    //         button.innerText = "View";
    //         button.onclick = () => viewRecord(params.data.record);
    //         return button;
    //     },
    // }

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
    <div class=" grid grid-cols-2 gap-5 py-5">
        <div>
            <Button class="cursor-pointer" @click="downloadCsv()">
                Download CSV
            </Button>
        </div>

        <div>
            Filtered records: <span class="text-xl text-orange-500 font-semibold">{{ filteredRecordCount }}</span>
        </div>
    </div>
    <!-- <pre>
        {{ props.allResults }}
    </pre> -->
    <div class="border rounded-lg grid-container">
        <ag-grid-vue :theme="themeQuartz" style="width: 100%; height: 500px;" @grid-ready="onGridReady"
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