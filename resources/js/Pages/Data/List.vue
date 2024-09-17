<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import pickBy from 'lodash/pickBy';
import throttle from 'lodash/throttle';
import mapValues from 'lodash/mapValues';
import Pagination from '@/Components/Pagination.vue';
import Dropdown from '@/Components/Dropdown.vue';
import CustomButton from '@/Components/CustomButton.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue';
import numberWithSpaces from '@/Utilities/numberWithSpaces';
import SearchFilter from '@/Components/SearchFilter.vue'
import { AgGridVue } from 'ag-grid-vue3';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS for AG Grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme for AG Grid
import { computed } from 'vue';


import { ref, watch } from 'vue';
import { filter } from 'lodash';
import Record from '../Reports/Record.vue';

interface RecordData {
    [key: string]: {
        [key: string]: string;
    };
}
const props = defineProps<{
    records: any,
    'filters': any,
    'project_id': string,
    'field_name': string,
    'value': any,
    'project': any,
    'queryDefinition': string,
}>();

interface RowData {
    record: string;
    event: Event;
    field_name: string;
    value: string;
    form_name: string;
}


const form = ref({
    search: props.filters.search,
    trashed: props.filters.trashed,
})

const rowData = ref<RowData[]>(props.records)

const columnDefs = ref([
    { headerName: "Record", field: "record", sortable: true, filter: true },
    {
        headerName: "Event", sortable: true, filter: true,
        valueGetter: (params: any) => params.data.event.name,
    },
    { headerName: "Value", field: "value", sortable: true, filter: true },
    { headerName: "Form Name", field: "form_name" },
    {
        headerName: "View",
        cellRenderer: (params) => {
            const button = document.createElement('button');
            button.className = "inline-flex items-center justify-center px-4 py-1 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-orange-500 border border-transparent rounded-md hover:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2";
            button.innerText = "Record";
            button.onclick = () => viewRecord(params.data.record, params.data.event.id);
            return button;
        },
    }

]);

// Method to handle grid ready event
const agGrid = ref(null);

const onGridReady = (params) => {
    console.log(agGrid.value)
    agGrid.value = params.api; // Store the grid API
    params.api.sizeColumnsToFit(); // Adjust column sizes
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

const viewRecord = (record: string, eventId: number) => {
    router.get(`/project/${props.project.project_id}/event/${eventId}/respondent/${record}`)
};

watch(form, throttle(function () {
    router.get(`/project/${props.project_id}/question/${props.field_name}/response/${props.value}`, pickBy(form.value), { preserveState: true })
}, 150), {
    deep: true
})



const uniqueValues = computed(() => {
    let recordList = [];

    props.records.forEach((record: any) => {
        recordList.push(record.record);
    })

    const uniqueValuesSet = new Set(Object.values(recordList));

    // Convert the Set back to an array
    return Array.from(uniqueValuesSet);

})

const back = () => {
    window.history.back();
}

</script>
<template>
    <AppLayout title="Data ">
        <template #header>
            <div class="flex gap-1.5">
                <h2 class="text-xl font-light leading-tight text-gray-500 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/projects/${project.project_id}`">
                    {{ project.app_title }}
                    </Link>
                </h2>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl font-light leading-tight text-gray-500 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/project/metadata/${project.project_id}`">
                    Metadata and Stats
                    </Link>
                </div>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl font-light leading-tight text-gray-500 hover:text-orange-500">
                    <Link class="btn-indigo" href="#" @click="back">
                    Back
                    </Link>
                </div>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-2xl text-orange-500">
                    {{ field_name }}
                </div>
            </div>

        </template>

        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <!-- <pre>
            {{ records }}
        </pre> -->
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div>
                        <div class="p-20">
                            <div class="p-10 bg-green-100 rounded-xl ">
                                <div class="pb-5 text-xl font-bold text-teal-800">Data Definition</div>
                                <div>{{ queryDefinition }}</div>
                            </div>
                            <div class="flex justify-between pt-10 pb-5">
                                <div class="flex gap-5 px-10 text-3xl">
                                    <div>
                                        <span class="text-orange-500">{{ numberWithSpaces(records.length) }} </span>
                                        <span v-if="records.length == 1">
                                            Record
                                        </span>
                                        <span v-else>
                                            Records
                                        </span>
                                    </div>
                                    <div>|</div>
                                    <div class="text-3xl ">
                                        <span class="text-green-500">{{ uniqueValues.length }} </span> Unique   <span v-if="uniqueValues.length == 1">
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

                            <div class="ag-theme-quartz" style="height: 800px; width: 100%;">
                                <AgGridVue :columnDefs="columnDefs" :rowData="rowData" :pagination="true"
                                    :paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }"
                                    @grid-ready="onGridReady" class="ag-theme-quartz" />
                            </div>

                        </div>
                    </div>
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

.view-button {
    background-color: #4CAF50;
    /* Green */
    color: white;
    border: none;
    padding: 5px 10px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    margin: 4px 2px;
    cursor: pointer;
}
</style>