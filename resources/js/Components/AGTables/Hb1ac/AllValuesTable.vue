<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import numberWithSpaces from '@/Utilities/numberWithSpaces';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS for AG Grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme for AG Grid

const props = defineProps<
    {
        allResults: any
    }
>()

const rowData = ref([]);
const filteredRecordCount = ref(0);

const transformData = () => {
    const transformed = [];
    for (const key in props.allResults) {
        if (props.allResults[key]) {
            for (const visit of props.allResults[key]) {
                transformed.push({ record_id: key, ...visit });
            }
        }
    }
    return transformed;
};


const columnDefs = ref([
    { headerName: 'Record ID', field: 'record_id', sortable: true, filter: true, width: 150 },
    { headerName: 'Event', field: 'event', sortable: true, filter: true },
    { headerName: 'Health Facility', field: 'health_facility', sortable: true, filter: true },
    { headerName: 'Age', field: 'age', sortable: true, filter: true },
    { headerName: 'Gender', field: 'gender', sortable: true, filter: true },
    { headerName: 'HbA1c', field: 'hb1ac', sortable: true, filter: true },
    { headerName: 'Visit Date', field: 'visit_date', sortable: true, filter: true }
]);

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

onMounted(() => {
    rowData.value = transformData();
});

</script>
<template>
    <div class="flex gap-5">
        <div class="flex gap-5 text-3xl">
            <div>
                <span class="text-green-400">{{ numberWithSpaces(Object.keys(allResults).length) }} </span>
                <span v-if="Object.keys(allResults).length == 1">
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

    <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
        <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefs" :rowData="rowData" :pagination="true"
            paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }" @grid-ready="onGridReady" />
    </div>
</template>
<style scoped>
.ag-theme-quartz {
    height: 100%;
    width: 100%;
}

/* Add custom styles for AG Grid */
.text-red-500 {
    color: red;
}

.text-sky-400 {
    color: skyblue;
}

.text-green-500 {
    color: green;
}
</style>