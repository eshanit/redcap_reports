<script setup lang="ts">
import { defineProps, computed, ref, nextTick } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import "ag-grid-community/styles/ag-grid.css"; 
import "ag-grid-community/styles/ag-theme-quartz.css"; 

const props = defineProps<{
    records: Array<{ record: string; values: Record<string, Record<string, string>> }>
}>();

// Flatten the records into a suitable format for AG Grid
const rowData = computed(() => {
    const data = [];

    props.records.forEach(record => {
        const { record: recordId, values } = record;

        // Flatten each value set
        for (const [eventType, eventValues] of Object.entries(values)) {
            for (const [eventId, eventValue] of Object.entries(eventValues)) {
                data.push({
                    record: recordId,
                    eventType: eventType,
                    eventId: eventId,
                    eventValue: eventValue,
                });
            }
        }
    });

    return data;
});

// Define column definitions for AG Grid
const columnDefs = [
    { headerName: "Record", field: "record", minWidth: 150 },
    { headerName: "Event Type", field: "eventType", minWidth: 150 },
    { headerName: "Event ID", field: "eventId", minWidth: 150 },
    { headerName: "Event Value", field: "eventValue", minWidth: 150 },
];

// Reference to AG Grid
const agGrid = ref(null);

// Method to handle grid ready event
const onGridReady = async (params) => {
    console.log('AG Grid2 reference:', agGrid.value);
    agGrid.value = params.api; // Store the grid API
    // Use nextTick to ensure the grid is rendered before sizing columns
    await nextTick();
    params.api.sizeColumnsToFit(); // Adjust column sizes
};

</script>

<template>
    <pre>
        {{ columnDefs }}
    </pre>
    <div class="ag-theme-quartz" style="height: 800px; width: 100%;" v-if="rowData">
        <AgGridVue
            ref="agGrid"
            :columnDefs="columnDefs"
            :rowData="rowData"
            :defaultColDef="{ flex: 1, minWidth: 100 }"
            @grid-ready="onGridReady"
        />
    </div>
</template>

<style scoped>
.ag-theme-quartz {
    height: 100%;
    width: 100%;
}
</style>