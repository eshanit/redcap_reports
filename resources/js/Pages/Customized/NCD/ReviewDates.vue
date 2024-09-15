<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { AgGridVue } from 'ag-grid-vue3';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS for AG Grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme for AG Grid

interface EventData {
    proposed_appointment_date: string | null; // Adjust the type as necessary
    actual_visit_date: string | null; // Assuming this can be null
    status: string;
    days_difference: number;
}

interface defaultersData {
    proposed_appointment_date: string | null; // Adjust the type as necessary
    actual_visit_date: string | null; // Assuming this can be null
    status: string;
    days_difference: number;
    ncd_tel_pat: string | null; //
    ncd_tel_kin: string | null; 
    
}

interface RowData {
    recordId: string;
    eventId: string;
    lastVisitDate: string;
    nextReviewDate: string | null;
    status: string;
}

interface DataCounts {
    
    status_counts: {
        "Late": number;
        "Not Late": number,
        "On Time": number,
        "-": number
    }
}

///
const activeTab = ref('allVisitList');

const setActiveTab = (tab) => {
    activeTab.value = tab;
};

const tabClass = (tab) => {
    return activeTab.value === tab ? 'bg-blue-500' : 'bg-gray-500';
};


const props = defineProps<{
    project: any,
    data: Record<string, EventData>
    dataCounts: Record<string, DataCounts>
    latestData: Record<string, EventData>
    statusDistribution: Record<string, any>
    averageDaysDifference: Record<string, any>
    upcomingAppointments: Record<string, EventData>
    lateAppointments: Record<string, EventData>
    defaulters: Record<string, defaultersData>
}>();

const getStatusClass = (params: any) => {
    switch (params.value) {
        case 'Late':
            return 'text-red-500'; // Tailwind CSS class for red text
        case 'On Time':
            return 'text-sky-400'; // Tailwind CSS class for sky blue text
        case 'Not Late':
            return 'text-green-500'; // Tailwind CSS class for green text
        default:
            return '';
    }
};

const columnDefs = [
    { headerName: 'Record ID', field: 'recordId', sortable: true, filter: true },
    { headerName: 'Event ID', field: 'eventId', sortable: true, filter: true },
    { headerName: 'Proposed Visit Date', field: 'lastVisitDate', sortable: true, filter: true },
    { headerName: 'Actual Visit Date', field: 'nextReviewDate', sortable: true, filter: true },
    { headerName: 'Status', field: 'status', sortable: true, filter: true, cellClass: getStatusClass },
    { headerName: 'Date Difference', field: 'days_difference', sortable: true, filter: true },
];

const rowData = ref(Object.keys(props.data).flatMap(record => {
    return Object.entries(props.data[record]).map(([eventId, eventData]) => (
        {
            recordId: record,
            eventId: eventId,
            lastVisitDate: eventData.proposed_appointment_date || '-',
            nextReviewDate: eventData.actual_visit_date,
            status: eventData.status,
            days_difference: eventData.days_difference
        }));
}));


const rowDataLatest = ref(Object.keys(props.latestData).flatMap(record => {
    return Object.entries(props.latestData[record]).map(([eventId, eventData]) => (
        {
            recordId: record,
            eventId: eventId,
            lastVisitDate: eventData?.proposed_appointment_date || '-',
            nextReviewDate: eventData?.actual_visit_date,
            status: eventData?.status,
            days_difference: eventData?.days_difference
        }));
}));

const rowDataLatestBeforeToday = ref(Object.keys(props.upcomingAppointments).flatMap(record => {
    return Object.entries(props.upcomingAppointments[record]).map(([eventId, eventData]) => (
        {
            recordId: record,
            eventId: eventId,
            lastVisitDate: eventData.proposed_appointment_date || '-',
            nextReviewDate: eventData.actual_visit_date,
            status: eventData.status,
            days_difference: eventData.days_difference
        }));
}));

///Statistics

const rowDataAnalytics = ref(Object.entries(props.dataCounts).map(([key, record]) => {
  const statusCounts = record.status_counts;
  return {
    recordId: key,
    late: statusCounts.Late,
    notLate: statusCounts['Not Late'],
    onTime: statusCounts['On Time'],
    dash: statusCounts['-']
  };
}));

const columnDefsAnalytics = [
    { headerName: 'Record ID', field: 'recordId', sortable: true, filter: true },
    { headerName: 'Late Visits', field: 'late', sortable: true, filter: true, cellClass: "text-red-500" },
    { headerName: 'Not Late Visits', field: 'notLate', sortable: true, filter: true, cellClass: "text-green-500" },
    { headerName: 'On Time Visits', field: 'onTime', sortable: true, filter: true, cellClass: "text-sky-500" },
    { headerName: 'N/A', field: 'dash', sortable: true, filter: true },
];

//defaulters
const transformedRowData = ref( Object.entries(props.defaulters).map(([key, value]) => ({ key, ...value })));

const columnDefsDefaulters = ref([
  { headerName: 'Record ID', valueGetter: 'data.key', width: 150 },
  { headerName: 'Last Event', field: 'last_event', width: 150 },
  { headerName: 'Proposed Appointment Date', field: 'proposed_appointment_date', width: 180 },
  { headerName: 'Actual Visit Date', field: 'actual_visit_date', width: 150 },
  { headerName: 'Status', field: 'status', width: 100 , cellClass: "text-red-500"},
  { headerName: 'Last Visit', field: 'days_difference', width: 150 },
  { headerName: 'Patient Telephone', field: 'ncd_tel_pat', width: 150 },
  { headerName: 'Kin Telephone', field: 'ncd_tel_kin', width: 150 },
]);




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

const back = () => {
    window.history.back();
}
</script>
<template>
    <AppLayout title="Data ">
        <template #header>
            <div class="flex items-center space-x-2">
                <h2 class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link :href="`/projects/${project.project_id}`">{{ project.app_title }}</Link>
                </h2>
                <ChevronRightIcon class="text-green-500" :size="25" />
                <div class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" href="#" @click="back">
                    Back
                    </Link>
                </div>
                <ChevronRightIcon class="text-green-500" :size="25" />
                <div class="text-xl text-orange-500">Customized Reports</div>
            </div>
        </template>
        <div class="sm:px-6 lg:px-8">
            <div class="pt-5">
                <SecondaryButton @click="downloadCsv()">
                    Download CSV
                </SecondaryButton>
            </div>

            <ul class="flex justify-center space-x-2 text-white">
                <li>
                    <button @click="setActiveTab('allVisitList')"
                        :class="['tab-button', { active: activeTab === 'allVisitList' }]">
                        All Visits List
                    </button>
                </li>
                <li>
                    <button @click="setActiveTab('latestVisitList')"
                        :class="['tab-button', { active: activeTab === 'latestVisitList' }]">
                        Last Visit List
                    </button>
                </li>
                <li>
                    <button @click="setActiveTab('futureVisitList')"
                        :class="['tab-button', { active: activeTab === 'futureVisitList' }]">
                        Upcoming Visits
                    </button>
                </li>
                <li>
                    <button @click="setActiveTab('defaulters')"
                        :class="['tab-button', { active: activeTab === 'defaulters' }]">
                        Defaulters
                    </button>
                </li>
                <li>
                    <button @click="setActiveTab('visitAnalytics')"
                        :class="['tab-button', { active: activeTab === 'visitAnalytics' }]">
                        Visit Analytics
                    </button>
                </li>
            </ul>

            <div class="flex gap-5" v-if="activeTab === 'allVisitList'">
                <div class="relative w-1/2 py-12">
                    <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                        <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefs" :rowData="rowData"
                            :pagination="true" paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }"
                            @grid-ready="onGridReady" />
                    </div>
                </div>
                <div>
                    <pre>
                        <!-- {{ statusDistribution }}
                        {{ averageDaysDifference }}
                        {{ upcomingAppointments }} 
                        {{ lateAppointments }}-->
                       
                    </pre>
                </div>
            </div>
            <div v-if="activeTab === 'latestVisitList'">
                <div class="relative w-1/2 py-12">
                    <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                        <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefs" :rowData="rowDataLatest"
                            :pagination="true" paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }"
                            @grid-ready="onGridReady" />
                    </div>
                </div>
            </div>
            <div class="" v-if="activeTab === 'futureVisitList'">
                <div class="relative w-1/2 py-12">
                    <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                        <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefs" :rowData="rowDataLatestBeforeToday"
                            :pagination="true" paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }"
                            @grid-ready="onGridReady" />
                    </div>
                </div>
            </div>
            <div class="" v-if="activeTab === 'defaulters'">
                <!-- <pre>
                    {{  defaulters }}
                </pre> -->
                <div class="relative w-1/2 py-12">
                    <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                        <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefsDefaulters" :rowData="transformedRowData"
                            :pagination="true" paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }"
                            @grid-ready="onGridReady" />
                    </div>
                </div>
            </div>
            <div class="flex" v-if="activeTab === 'visitAnalytics'">
                <div class="relative w-1/2 py-12">
                    <!-- <pre>{{ dataCounts }}</pre> -->
                    <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                        <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefsAnalytics" :rowData="rowDataAnalytics"
                            :pagination="true" paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }"
                            @grid-ready="onGridReady" />
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