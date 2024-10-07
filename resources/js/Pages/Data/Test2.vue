<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { defineProps, computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import WarningOutlineButton from '@/Components/WarningOutlineButton.vue';
import { AgGridVue } from 'ag-grid-vue3';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS for AG Grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme for AG Grid
import groupBy from 'lodash/groupBy';
import { debounce } from 'lodash';
import numberWithSpaces from '@/Utilities/numberWithSpaces';

const strictIntersectionMode = ref(true);

interface Event {
    id: number;
    name: string;
}

///
const activeTab = ref('oneRowOneRecord');

const setActiveTab = (tab) => {
    activeTab.value = tab;
};

const tabClass = (tab) => {
    return activeTab.value === tab ? 'bg-blue-500' : 'bg-gray-500';
};

///

const props = defineProps<{
    records: any,
    'filters': any,
    'project_id': string,
    'field_name': string,
    'value': any,
    'project': any,
    'pagination': any,
}>();

const data = groupBy(props.records, 'record');

// Group data by field names
const groupedFields = computed(() => {
    const fieldMap: Record<string, { name: string; eventIds: number[]; eventNames: string[] }> = {};
    for (const records of Object.values(data)) {
        records.forEach(record => {
            if (!fieldMap[record.field_name]) {
                fieldMap[record.field_name] = { name: record.field_name, eventIds: [], eventNames: [] };
            }
            if (!fieldMap[record.field_name].eventIds.includes(record.event_id)) {
                fieldMap[record.field_name].eventIds.push(record.event_id);
            }
            if (!fieldMap[record.field_name].eventNames.includes(record.event)) {
                fieldMap[record.field_name].eventNames.push(record.event);
            }
        });
    }
    return Object.values(fieldMap);
});



const records = computed(() => {
    return Object.entries(data).map(([key, records]) => {
        const values: Record<string, Record<number, string | undefined>> = {};
        records.forEach(record => {
            if (!values[record.field_name]) {
                values[record.field_name] = {};
            }
            values[record.field_name][record.event_id] = record.value;
        });
        return { record: key, values };
    });
});


const filterSimilarKeys = computed(() => {
    // Create an array to hold the transformed records
    const result = [];

    // Iterate through each record
    records.value.forEach(item => {
        const newItem = { record: item.record, values: {} };

        // Get the field names
        const fields = Object.keys(item.values);

        // Find common keys in values
        const commonKeys = Object.keys(item.values[fields[0]]).filter(key =>
            fields.every(field => item.values[field].hasOwnProperty(key))
        );

        // If there are common keys, add them to the new item
        if (commonKeys.length > 0) {
            fields.forEach(field => {
                newItem.values[field] = {};
                commonKeys.forEach(key => {
                    newItem.values[field][key] = item.values[field][key];
                });
            });
            result.push(newItem);
        }
    });

    return result;
})

const loadData = computed(() => {
    return strictIntersectionMode.value ? filterSimilarKeys.value : records.value;
});

const colors = ['green', 'orange', 'cyan', 'purple', 'zinc', 'sky', 'rose']

// Define column groups
const columnDefs = computed(() => {
    const columns = [
        { headerName: "Record", field: "record", minWidth: 150, pinned: 'left' }
    ];

    const fieldGroups = {}; // To hold fields by their group

    groupedFields.value.forEach((field, index) => {
        // Create a group for this field
        if (!fieldGroups[field.name]) {
            fieldGroups[field.name] = {
                headerName: field.name,
                sortable: true,
                filter: true, 
                children: [] // This will hold the columns for this group
            };
        }

        // Add event columns to the group
        field.eventNames.forEach(eventName => {
            fieldGroups[field.name].children.push({
                headerName: eventName,
                field: `${field.name}_${eventName}`,
                sortable: true,
                filter: true, 
                minWidth: 150,
                cellClass: `bg-${colors[index]}-100`
            });
        });
    });

    // Convert the fieldGroups object into an array
    Object.values(fieldGroups).forEach((group: any) => {
        columns.push(group); // Add the group to the main columns array
    });

    return columns;
});


const rowData = computed(() => {
    const recordsData = loadData.value.map(record => {
        const row = { record: record.record };
        groupedFields.value.forEach(field => {
            field.eventIds.forEach((eventId, index) => {
                const eventName = field.eventNames[index];
                row[`${field.name}_${eventName}`] = record.values[field.name]?.[eventId] || '-';
            });
        });
        return row;
    });
    return recordsData.length > 0 ? recordsData : [];
});

// Function to toggle the intersection mode
const toggleMode = debounce(() => {
    strictIntersectionMode.value = !strictIntersectionMode.value;
}, 300);


//

// Reference to AG Grid
const agGrid = ref(null);

// Method to download CSV
const downloadCsv = () => {

    const gridApi = agGrid.value;
    console.log('AG Grid reference:', agGrid.value);
    if (gridApi) {
        gridApi.exportDataAsCsv();
    } else {
        console.error("AG Grid API is not available.");
    }
};

// Method to handle grid ready event
const onGridReady = (params) => {
    agGrid.value = params.api; // Store the grid API
    params.api.sizeColumnsToFit(); // Adjust column sizes
};

/** response */

const removeEmptyRows = ref(false);

const rowDataX = computed(() => {
    const recordsData = [];

    loadData.value.forEach(record => {
        // Iterate over the fields to create a distinct entry for each event
        groupedFields.value.forEach(field => {
            field.eventIds.forEach((eventId, index) => {
                const eventName = field.eventNames[index];

                // Find if this record/event combination already exists
                let existingRow = recordsData.find(row => row.record === record.record && row.eventName === eventName);

                if (!existingRow) {
                    // If it doesn't exist, create a new row
                    existingRow = {
                        record: record.record,
                        eventName: eventName,
                    };
                    recordsData.push(existingRow);
                }

                // Populate field values for the specific event
                existingRow[field.name] = record.values[field.name]?.[eventId] || '-';
            });
        });
    });

    return recordsData;
});


const rowDataY = computed(() => {
    const recordsData = [];

    loadData.value.forEach(record => {
        groupedFields.value.forEach(field => {
            field.eventIds.forEach((eventId, index) => {
                const eventName = field.eventNames[index];

                // Find if this record/event combination already exists
                let existingRow = recordsData.find(row => row.record === record.record && row.eventName === eventName);

                if (!existingRow) {
                    // If it doesn't exist, create a new row
                    existingRow = {
                        record: record.record,
                        eventName: eventName,
                    };
                    recordsData.push(existingRow);
                }

                // Only populate the field if it has data
                const value = record.values[field.name]?.[eventId];
                if (value) {
                    existingRow[field.name] = value;
                }
            });
        });
    });

    return recordsData.filter(row => Object.keys(row).length > 2); // Ensure there's at least the record and eventName fields
});

const rowDataZ = computed(() => {
    console.log('empty row data', removeEmptyRows.value)
    if (removeEmptyRows.value) {
        return {
            data: rowDataY.value,
            btnText: 'Include'
        } 
    }

    return {
        data: rowDataX.value,
        btnText: 'Remove'
    } 
});

// Define column definitions for AG Grid
const columnDefsX = computed(() => {
    const columns = [
        { headerName: "Record", field: "record", minWidth: 150, cellClass: undefined,     sortable: true,
        filter: true, pinned: 'left' },
        { headerName: "Event Name", field: "eventName", minWidth: 150, sortable: true,
        filter: true },
    ];

    groupedFields.value.forEach(field => {
        columns.push({
            headerName: field.name,
            field: field.name,
            sortable: true,
            filter: true, 
            minWidth: 150,
            cellClass: `bg-${colors[columns.length % colors.length]}-100`,
            pinned: 'left'
        });
    });

    return columns;
});



// Back navigation function
const back = () => {
    window.history.back();
};

</script>

<template>
    <AppLayout title="Data ">
        <template #header>
            <div class="flex gap-1.5">
                <!-- Header content -->
                <div class="flex gap-1.5">
                <h2 class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/projects/${project.project_id}`">
                    {{ project.app_title }}
                    </Link>
                </h2>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/project/metadata/${project.project_id}`">
                    Metadata and Stats
                    </Link>
                </div>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" href="#" @click="back">
                    Back
                    </Link>
                </div>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl text-orange-500">
                     queried data list
                </div>
            </div>

            </div>
        </template>

        <div class="px-10">
            <div class="py-5">
                <div class="flex gap-5 ">
                    <SecondaryButton @click="toggleMode">
                        Switch Mode
                    </SecondaryButton>
                    <SecondaryButton @click="downloadCsv()">
                        Download CSV
                    </SecondaryButton>
                    <div v-if="activeTab === 'eventsRecords'" class="px-5 border-l border-orange-500">
                        <WarningOutlineButton @click="removeEmptyRows = !removeEmptyRows">
                            {{ rowDataZ.btnText }} Empty Rows
                        </WarningOutlineButton>
                    </div>
                </div>
                <div class="py-2.5">
                    <div>
                        <span class="pr-5 font-bold">Mode:</span> <span>{{ strictIntersectionMode ? 'Strict IntersectionMode' :
                            'Lazy Intersection Mode' }}</span>
                    </div>
                    <div>
                        <span class="pr-5 font-bold">Records:</span> <span>{{ numberWithSpaces(rowData.length) }}</span>
                    </div>
                </div>
            </div>
            <ul class="flex justify-center space-x-2 text-white">
                <li>
                    <button @click="setActiveTab('oneRowOneRecord')"
                        :class="['tab-button', { active: activeTab === 'oneRowOneRecord' }]">
                        By Row
                    </button>
                </li>
                <li>
                    <button @click="setActiveTab('eventsRecords')"
                        :class="['tab-button', { active: activeTab === 'eventsRecords' }]">
                        By Column
                    </button>
                </li>
            </ul>

            <div v-if="activeTab === 'oneRowOneRecord'">
            <!-- <pre>
                {{ records }}
            </pre> -->
                <div class="ag-theme-quartz" style="height: 800px; width: 100%;">
                    <AgGridVue :columnDefs="columnDefs" :rowData="rowData" :pagination="true" :paginationPageSize="100"
                        :defaultColDef="{ flex: 1, minWidth: 100 }" @grid-ready="onGridReady" class="ag-theme-quartz" />
                </div>
            </div>
            <div v-if="activeTab === 'eventsRecords'">
                <!-- <pre>
                    {{ rowDataZ }}
                </pre> -->
                <div class="ag-theme-quartz" style="height: 800px; width: 100%;">
                    <AgGridVue :columnDefs="columnDefsX" :rowData="rowDataZ.data" :pagination="true"
                        :paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }" @grid-ready="onGridReady"
                        class="ag-theme-quartz" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.ag-theme-quartz {
    height: 100%;
    width: 100%;
}

.tab-button {
    background-color: #f0f0f0;
    /* Light grey background */
    color: #333;
    /* Dark text color */
    border: 1px solid #ccc;
    /* Light border */
    border-radius: 4px;
    /* Rounded corners */
    padding: 10px 15px;
    /* Padding for the button */
    cursor: pointer;
    /* Pointer cursor on hover */
    transition: background-color 0.3s, border-color 0.3s;
    /* Smooth transition */
}

.tab-button:hover {
    background-color: #e0e0e0;
    /* Darker grey on hover */
    border-color: #bbb;
    /* Darker border on hover */
}

.tab-button.active {
    background-color: #007bff;
    /* AG Grid blue for active */
    color: white;
    /* White text for active */
    border-color: #007bff;
    /* Border same as background */
}
</style>