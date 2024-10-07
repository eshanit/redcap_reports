<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import numberWithSpaces from '@/Utilities/numberWithSpaces';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS for AG Grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme for AG Grid

const props = defineProps<
    {
        statisticsResults: object
    }
>()

// Define column definitions
const columnDefs = ref([
    { headerName: 'Record ID', field: 'recordId', sortable: true, filter: true },
    { headerName: 'Mean', field: 'mean', sortable: true, filter: true },
    { headerName: 'Max', field: 'max', sortable: true, filter: true },
    { headerName: 'Min', field: 'min', sortable: true, filter: true },
    { headerName: 'Count', field: 'count', sortable: true, filter: true },
    { headerName: 'Standard Deviation', field: 'standard_deviation', sortable: true, filter: true }
]);


// Transform statistics into row data
const rowData = ref([]);

const agGrid = ref(null);
// Method to handle grid ready event
const onGridReady = (params) => {
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
    rowData.value = Object.entries(props.statisticsResults).map(([recordId, stats]) => ({
        recordId,
        ...stats,
    }));
});

</script>
<template>
    <div class="flex gap-5">
        <div class="flex gap-5 text-3xl">
            <div>
                <span class="text-green-400">{{ numberWithSpaces(Object.keys(statisticsResults).length) }} </span>
                <span v-if="Object.keys(statisticsResults).length == 1">
                    Record
                </span>
                <span v-else>
                    Records
                </span>
            </div>
        </div>
        <SecondaryButton @click="downloadCsv()">
            Download CSV
        </SecondaryButton>
    </div>
    <div class="py-5">
        <strong>Number of Filtered Records: <span class="text-2xl text-orange-500">{{ filteredRecordCount }}</span></strong>
    </div>
    <div class="">
        <div class="ag-theme-quartz" style="height: 1000px; width: 100%;">
            <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefs" :rowData="rowData" :pagination="true"
                paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }" @grid-ready="onGridReady" />
        </div>
    </div>
</template>
<style scoped>
.ag-theme-quartz {
    height: 100%;
    width: 100%;
}
</style>