<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PieChart from '@/Components/Charts/D3/PieChart.vue';
import { AgGridVue } from 'ag-grid-vue3';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS for AG Grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme for AG Grid
import transformObjectToArray from '@/Utilities/transformObjectToArray';

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
    statusDistribution: Record<string, object>
    averageDaysDifference: Record<string, any>
    upcomingAppointments: Record<string, EventData>
    lateAppointments: Record<string, EventData>
    defaulters: Record<string, defaultersData>
    trends: Record<string, any>
    eventAnalysis: Record<string, any>
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

const recordTrend = ref();

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
    { headerName: 'No Data', field: 'dash', sortable: true, filter: true },
    {
        headerName: "View",
        cellRenderer: (params) => {
            const button = document.createElement('button');
            button.className = "inline-flex items-center justify-center px-4 py-1 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-orange-500 border border-transparent rounded-md hover:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2";
            button.innerText = "Trend";
            button.onclick = () => viewRecord(params.data.recordId);
            return button;
        },
    }
];

const viewRecord = (record: string) => {
    recordTrend.value = {
        record: record,
        trend: props.trends[record]
    }
};


//defaulters
const transformedRowData = ref(Object.entries(props.defaulters).map(([key, value]) => ({ key, ...value })));

const columnDefsDefaulters = ref([
    { headerName: 'Record ID', valueGetter: 'data.key', sortable: true, filter: true, width: 150 },
    { headerName: 'Last Event', field: 'last_event', sortable: true, filter: true, width: 150 },
    { headerName: 'Proposed Appointment Date', field: 'proposed_appointment_date', sortable: true, filter: true, width: 180 },
    { headerName: 'Actual Visit Date', field: 'actual_visit_date', sortable: true, filter: true, width: 150 },
    { headerName: 'Status', field: 'statusDefault', width: 100, cellClass: "text-red-500" },
    { headerName: 'Last Visit', field: 'days_difference', sortable: true, filter: true, width: 150 },
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
                        Upcoming Reviews
                    </button>
                </li>
                <li>
                    <button @click="setActiveTab('defaulters')"
                        :class="['tab-button', { active: activeTab === 'defaulters' }]">
                        Missed Reviews
                    </button>
                </li>
                <li>
                    <button @click="setActiveTab('visitAnalytics')"
                        :class="['tab-button', { active: activeTab === 'visitAnalytics' }]">
                        Visit Analytics
                    </button>
                </li>
            </ul>

            <div class="" v-if="activeTab === 'allVisitList'">
                <div class="pt-10">
                    This tab shows a list of <span class="font-bold text-orange-500">ALL</span> visits (for each
                    respondent) and
                    the status of those visits ,that is,
                    whether
                    they were <span class="text-orange-500 ">Late, Not Late </span>or <span class="text-orange-500 ">On
                        time
                    </span>to their appointment.
                </div>
                <div class="flex gap-5 py-10">
                    <div class="relative w-1/2">
                        <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                            <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefs" :rowData="rowData"
                                :pagination="true" paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }"
                                @grid-ready="onGridReady" />
                        </div>
                    </div>
                    <div class="w-1/2">
                        <div class="p-10 bg-white border rounded-lg">
                            <div class="grid grid-cols-2">
                                <div>
                                    <PieChart :dataObject="transformObjectToArray(statusDistribution)"
                                        chartTitle="Respondent Visits Distribution" />
                                </div>
                                <div class="px-5 border-l">
                                    <div class="pb-5 mb-4 text-xl font-bold border-b">Visit Status Counts</div>
                                    <div class="grid grid-cols-2 gap-5 border-b pb-2.5 font-bold text-green-500">
                                        <div> Status</div>
                                        <div>Count</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-5 py-2.5">
                                        <div> On Time</div>
                                        <div>{{ statusDistribution['On Time'] }}</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-5 py-2.5">
                                        <div> Late</div>
                                        <div>{{ statusDistribution.Late }}</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-5 py-2.5">
                                        <div> Not Late</div>
                                        <div>{{ statusDistribution['Not Late'] }}</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-5 py-2.5">
                                        <div> - </div>
                                        <div>{{ statusDistribution['-'] }}</div>
                                    </div>

                                    <div class="text-sm italic text-gray-500">
                                        The last entry of '-' is a count of all the visits which had no data for either
                                        current
                                        visit date or next review date hence the system could not calculate whether the
                                        respondent
                                        was late or not.
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- <pre>
                        - {{ statusDistribution }}
                        {{ averageDaysDifference }}
                        {{ upcomingAppointments }} 
                        {{ lateAppointments }}
                       {{ trends }}
                        {{ statusDistribution }}
                    </pre> -->
                    </div>
                </div>
            </div>
            <div v-if="activeTab === 'latestVisitList'">
                <div class="pt-10">
                    This tab shows a list of all the <span class="font-bold text-orange-500">LAST</span> visits (for
                    each
                    respondent) and the status of those visits ,that is,
                    whether
                    they were <span class="text-orange-500 ">Late, Not Late </span>or <span class="text-orange-500 ">On
                        time
                    </span>to their appointment.
                </div>
                <div class="relative w-1/2 py-12">
                    <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                        <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefs" :rowData="rowDataLatest"
                            :pagination="true" paginationPageSize="100" :defaultColDef="{ flex: 1, minWidth: 100 }"
                            @grid-ready="onGridReady" />
                    </div>
                </div>
            </div>
            <div class="" v-if="activeTab === 'futureVisitList'">
                <div class="pt-10">
                    This tab shows a list of the <span class="font-bold text-orange-500">Upcoming</span> visits (for
                    each
                    respondent) and the respective appointment date in the <span
                        class="font-bold text-orange-500">Proposed
                        Visit Date</span> column .
                </div>
                <div class="flex gap-5 py-10 ">
                    <div class="relative w-1/2">
                        <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                            <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefs"
                                :rowData="rowDataLatestBeforeToday" :pagination="true" paginationPageSize="100"
                                :defaultColDef="{ flex: 1, minWidth: 100 }" @grid-ready="onGridReady" />
                        </div>
                    </div>
                    <div class="relative w-1/2 ">

                        <div class="p-10 bg-white rounded-lg shadow-xl">
                            <div class="flex gap-5">
                                <span>Upcoming Appointments:</span> <span class="text-5xl font-bold text-green-400">{{
                                    rowDataLatestBeforeToday.length }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="" v-if="activeTab === 'defaulters'">
                <!-- <pre>
                    {{  defaulters }}
                </pre> -->
                <div class="pt-10">
                    This tab shows a list of the <span class="font-bold text-orange-500">Defaulted</span> reviews and
                    the
                    respective appointment date in the <span class="font-bold text-orange-500">Proposed
                        Visit Date</span> column . The <span class="text-orange-500 ">NS</span> means No Show.
                </div>
                <div class="flex gap-5 py-10">
                    <div class="relative w-1/2">
                        <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                            <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefsDefaulters"
                                :rowData="transformedRowData" :pagination="true" paginationPageSize="100"
                                :defaultColDef="{ flex: 1, minWidth: 100 }" @grid-ready="onGridReady" />
                        </div>
                    </div>
                    <div class="relative w-1/2 ">
                        <div class="p-10 bg-white rounded-lg shadow-xl">
                            <div class="flex gap-5">
                                <span>Defaulted Appointments:</span> <span class="text-5xl font-bold text-rose-400">{{
                                    transformedRowData.length }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="" v-if="activeTab === 'visitAnalytics'">
                <div class="pt-10">
                    This tab shows each record together with statistics on how many visits they <span
                        class="font-bold text-orange-500">Defaulted, Did not Default, On Time and those with no
                        Data</span>. If
                    you click on the Trend button, you will be shown each record's trend on reviews.
                </div>
                <div class="flex gap-5 py-10">
                    <div class="relative lg:w-1/2 md:w-full sm:w-full">
                        <!-- <pre>{{ dataCounts }}</pre> -->
                        <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                            <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefsAnalytics"
                                :rowData="rowDataAnalytics" :pagination="true" paginationPageSize="100"
                                :defaultColDef="{ flex: 1, minWidth: 100 }" @grid-ready="onGridReady" />
                        </div>
                    </div>
                    <div class="relative lg:w-1/2 md:w-full sm:w-full">
                        <div class="p-20 overflow-y-auto bg-white rounded-lg shadow-xl max-h-[800px]">
                            <div v-if="recordTrend">
                                <div class="pb-5 mb-4 text-xl font-bold ">Showing trend habits for <span
                                        class="text-green-400">{{ recordTrend.record }}</span></div>
                                
                                    <div class="gap-10 border-t " v-for="status in recordTrend.trend ">
                                        <div class="grid grid-cols-2">
                                        <div class=" py-2.5">
                                            <div class="" v-if="status.date">{{ status.date }}</div>
                                            <div v-else> - </div>
                                        </div>
                                        <div class=" py-2.5">
                                            <span class="text-red-500" v-if="status.status == 'Late'">{{ status.status
                                                }}</span>
                                            <span class="text-green-400" v-else-if="status.status == 'Not Late'">{{
                                                status.status
                                                }}</span>
                                            <span class="text-sky-500" v-else-if="status.status == 'On Time'">{{
                                                status.status
                                                }}</span>
                                            <span v-else> -</span>
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div v-else>
                                Please click on the <span class="text-orange-500 ">Orange Trend </span> button to view
                                the
                                review trend of a particular respondent.
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