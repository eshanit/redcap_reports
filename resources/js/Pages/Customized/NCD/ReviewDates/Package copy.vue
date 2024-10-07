<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PieChart from '@/Components/Charts/D3/PieChart.vue';
import { AgGridVue } from 'ag-grid-vue3';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS for AG Grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme for AG Grid
import transformObjectToArray from '@/Utilities/transformObjectToArray';
import numberWithSpaces from '@/Utilities/numberWithSpaces';

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
        "Early": number,
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
    data: Record<string, any>
    dataCounts: Record<string, DataCounts>
    latestData: Record<string, any>
    statusDistribution: any
    averageDaysDifference: Record<string, any>
    upcomingAppointments: Record<string, EventData>
    lateAppointments: Record<string, EventData>
    defaulters: Record<string, any>
    trends: Record<string, any>
    eventAnalysis: Record<string, any>
}>();

const getStatusClass = (params: any) => {
    switch (params.value) {
        case 'Late':
            return 'text-red-500'; // Tailwind CSS class for red text
        case 'On Time':
            return 'text-sky-400'; // Tailwind CSS class for sky blue text
        case 'Early':
            return 'text-green-500'; // Tailwind CSS class for green text
        default:
            return '';
    }
};


const columnDefs = ref([
    { headerName: 'Record', field: 'record', sortable: true, filter: true },
    { headerName: 'Event', field: 'event', sortable: true, filter: true },
    { headerName: 'Proposed Dates', field: 'proposed_dates', sortable: true, filter: true },
    { headerName: 'Actual Dates', field: 'actual_dates', sortable: true, filter: true },
    { headerName: 'Days Difference', field: 'days_difference', sortable: true, filter: true },
    { headerName: 'Human Readable', field: 'human_readable', sortable: true, filter: true },
    { headerName: 'Status', field: 'status', sortable: true, filter: true, cellClass: getStatusClass }
]);

const rowData = ref([]);

Object.keys(props.data).forEach(recordKey => {
    const record = props.data[recordKey];
    for (let i = 0; i < record.event_id.length; i++) {
        rowData.value.push({
            record: recordKey,
            event: record.event[i],
            proposed_dates: record.proposed_dates[i] || '-',
            actual_dates: record.actual_dates[i] || '-',
            days_difference: record.days_difference[i] || '-',
            human_readable: record.human_readable[i] || '-',
            status: record.status[i]
        });
    }
});

//latest

const rowDataLatest = ref(
    Object.entries(props.latestData).map(([key, value]) => ({
        record: key, // Add the record key
        ...value      // Spread the rest of the value properties
    }))
);


// UpComing Trends

const columnDefUpComings = ref([
    { headerName: 'Record', field: 'record', sortable: true, filter: true },
    { headerName: 'Event', field: 'event', sortable: true, filter: true },
    { headerName: 'Review Date', field: 'ncd_visit_date', sortable: true, filter: true },
    { headerName: 'Next Review Date', field: 'ncd_next_review', sortable: true, filter: true },
    { headerName: 'Time To Review', field: 'days_difference', sortable: true, filter: true, cellClass: "text-orange-500" },
    { headerName: 'Status', field: 'status', sortable: true, filter: true, cellClass: "text-green-500" }
]);

const rowDataLatestBeforeToday = ref(
    Object.entries(props.upcomingAppointments).map(([key, value]) => ({
        record: key, // Add the record key
        ...value      // Spread the rest of the value properties
    }))
);

///Statistics

const recordTrend = ref();

const rowDataAnalytics = ref(Object.entries(props.dataCounts).map(([key, record]) => {
    // const statusCounts = record.status_counts;
    return {
        recordId: key,
        late: record['Late Visits'],
        notLate: record['Early Visits'],
        onTime: record['On Time Visits'],
        dash: record['No Data']
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
        trend: [{
            status: props.trends[record].status,
            date: props.trends[record].date
        }]
    }
};


//defaulters

const getDefaultedStatusClass = (params: any) => {
    switch (params.value) {
        case 'Missed Appointment':
            return 'text-teal-500'; // Tailwind CSS class for red text
        case 'Defaulted':
            return 'text-rose-500'; // Tailwind CSS class for sky blue text
        default:
            return '';
    }
};

const transformedRowData = ref(Object.entries(props.defaulters).map(([key, value]) => ({ key, ...value })));

const columnDefsDefaulters = ref([
    { headerName: 'Record ID', valueGetter: 'data.key', sortable: true, filter: true, width: 150 },
    { headerName: 'Last Event', field: 'last_event', sortable: true, filter: true, width: 150 },
    { headerName: 'Facility', field: 'facility', sortable: true, filter: true, width: 150 },
    { headerName: 'Proposed Appointment Date', field: 'proposed_appointment_date', sortable: true, filter: true, width: 180 },
    { headerName: 'Status', field: 'statusDefault', width: 150, sortable: true, filter: true, cellClass: getDefaultedStatusClass },
    { headerName: 'Last Visit', field: 'days_difference', sortable: true, filter: true, width: 150 },
    { headerName: 'Patient Telephone', field: 'tel_pat', width: 150 },
    { headerName: 'Kin Telephone', field: 'tel_kin', width: 150 },
]);


const countDefaulterStatus = computed(() => {
  const counts = {
    'Missed Appointment': 0,
    'Defaulted': 0
  };

  for (const key in props.defaulters) {
    const status = props.defaulters[key].statusDefault;
    if (counts.hasOwnProperty(status)) {
      counts[status]++;
    }
  }

  return counts;
});


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
                                        <div>{{ numberWithSpaces(statusDistribution['On Time']) }}</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-5 py-2.5">
                                        <div> Late</div>
                                        <div>{{ numberWithSpaces(statusDistribution.Late) }}</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-5 py-2.5">
                                        <div> Not Late</div>
                                        <div>{{ numberWithSpaces(statusDistribution['Early']) }}</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-5 py-2.5">
                                        <div> - </div>
                                        <div>{{ numberWithSpaces(statusDistribution['-']) }}</div>
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
                <!-- <pre>
                    {{ latestData }}
                </pre> -->
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
                    <!-- <pre>
                        {{  upcomingAppointments }}
                    </pre> -->
                    <div class="relative w-1/2">
                        <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                            <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefUpComings"
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
                <div class="pt-10">
                    This tab shows a list of the <span class="font-bold text-orange-500">Defaulted</span> reviews and
                    the
                    respective appointment date in the <span class="font-bold text-orange-500">Proposed
                        Visit Date</span> column . The <span class="text-orange-500 ">NS</span> means No Show.
                </div>
                <div class="flex gap-5 py-10">
                    <div class="relative w-8/12">
                        <div class="ag-theme-quartz" style="height: 900px; width: 100%;">
                            <AgGridVue class="ag-theme-quartz" :columnDefs="columnDefsDefaulters"
                                :rowData="transformedRowData" :pagination="true" paginationPageSize="100"
                                :defaultColDef="{ flex: 1, minWidth: 100 }" @grid-ready="onGridReady" />
                        </div>
                    </div>
                    <div class="relative w-4/12 ">
                        <div class="p-10 bg-white rounded-lg shadow-xl">
                            <div class="grid grid-cols-2 gap-5 border-b ">
                                <span>Defaulted + Missed Appointments:</span> <span class="text-5xl font-bold text-rose-400">{{
                                    numberWithSpaces(transformedRowData.length) }}</span>  
                            </div>
                            <div class="grid grid-cols-2 gap-5 py-10">
                               <div>Missed Appointments</div> <div class="text-5xl font-bold text-teal-500">{{ numberWithSpaces(countDefaulterStatus['Missed Appointment'])  }}</div>
                            </div>
                            <div class="grid grid-cols-2 gap-5 ">
                               <div>Defaulters</div> <div class="text-5xl font-bold text-orange-500">{{ numberWithSpaces(countDefaulterStatus['Defaulted'])  }}</div>
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
                                <div class="border-t" v-for="(status) in recordTrend.trend ">

                                    <div class="" v-for="(statusDate, index) in status.date">
                                        <div class="grid grid-cols-2 gap-5 py-2.5 border-t">
                                            <div>
                                                <span v-if="status.date[index] == ''">
                                                    -
                                                </span>
                                                <span ve-else>
                                                    {{ status.date[index] }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-red-500" v-if="status.status[index] == 'Late'">{{
                                                    status.status[index]
                                                    }}</span>
                                                <span class="text-green-400" v-else-if="status.status[index] == 'Early'">{{
                                                    status.status[index]
                                                    }}</span>
                                                <span class="text-sky-500" v-else-if="status.status[index] == 'On Time'">{{
                                                    status.status[index]
                                                    }}</span>
                                                <span v-else> -</span>
                                            </div>
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