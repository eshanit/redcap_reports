<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue';
import AllValuesTable from '@/Components/AGTables/Hb1ac/AllValuesTable.vue';
import StatisticsTable from '@/Components/AGTables/Hb1ac/StatisticsTable.vue'
import Demographics from '@/Components/CustomPackages/NCD/HB1AC/Demographics.vue'
import LineChart from '@/Components/Charts/Apex/LineChart.vue';
import TrendsTable from '@/Components/AGTables/Hb1ac/TrendsTable.vue';
import HBACRadioInput from '@/Components/HB!ACRadioInput.vue';
import Dropdown from '@/Components/Dropdown.vue';
import { AgGridVue } from 'ag-grid-vue3';
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS for AG Grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme for AG Grid


const props = defineProps<{
    'project': any,
    'results': any,
    'statisticsResults': any,
    'demographics': any

}>();

//tabs

const activeTab = ref('statisticsHb1ac');

const setActiveTab = (tab) => {
    activeTab.value = tab;
};

const tabClass = (tab) => {
    return activeTab.value === tab ? 'bg-blue-500' : 'bg-gray-500';
};

//

const recordId = ref('');


const back = () => {
    window.history.back();
}

const selectedOption = ref('All Records')

const hb1acOptions = [
    {
        name: 'All Records',
        value: 'all',
    },
    {
        name: 'Normal Records',
        value: 'normal',
    },
    {
        name: 'PreDiabetes Records',
        value: 'pre',
    },
    {
        name: 'Diabetic Records',
        value: 'diabetic',
    }
]

const statsData = computed(() => {

    let data = {
        selection: null,
        statistics: null,
        demographics: null
    }

    if(selectedOption.value === 'All Records') {
        data = {
            selection:selectedOption.value ,
            statistics: props.statisticsResults.all,
            demographics: props.demographics.all
        }
    } else if(selectedOption.value === 'Normal Records') {

        data = {
            selection:selectedOption.value ,
            statistics: props.statisticsResults.normal,
            demographics: props.demographics.normal
        }
    } else if(selectedOption.value === 'PreDiabetes Records' ) {
        data = {
            selection:selectedOption.value ,
            statistics: props.statisticsResults.prediabetes,
            demographics: props.demographics.prediabetes
        }
    }else {
        data = {
            selection:selectedOption.value ,
            statistics: props.statisticsResults.diabetes,
            demographics: props.demographics.diabetes
        }
    }

    return data;
})

</script>
<template>
    <AppLayout title="HB1AC">
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
                <div class="text-xl text-orange-500">HB1AC | Customized Reports</div>
            </div>
        </template>

        <div class="sm:px-6 lg:px-8">

            
            <ul class="flex justify-center py-10 space-x-2 text-white">
                <li>
                    <button @click="setActiveTab('statisticsHb1ac')"
                        :class="['tab-button', { active: activeTab === 'statisticsHb1ac' }]">
                        Hb1ac Statistics
                    </button>
                </li>
                <li>
                    <button @click="setActiveTab('allHb1ac')"
                        :class="['tab-button', { active: activeTab === 'allHb1ac' }]">
                        All Hb1ac
                    </button>
                </li>

                <li>
                    <button @click="setActiveTab('trends')" :class="['tab-button', { active: activeTab === 'trends' }]">
                        Respondent Trends
                    </button>
                </li>
                <!-- <li>
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
                </li> -->
            </ul>
            <div class="" v-if="activeTab === 'statisticsHb1ac'">
                <div class="w-1/3 border-b">
                    <HBACRadioInput v-model="selectedOption" :options="hb1acOptions" name="hb1ac" />
                </div>
            <div class=" text-center text-orange-500 py-2.5 text-2xl font-bold">{{ selectedOption }}</div>
                <div class="flex gap-5" v-if="selectedOption == 'All Records'">
                    <div class="w-7/12 ">
                        <StatisticsTable :statistics-results="statisticsResults.all" />
                    </div>
                    <div class="w-5/12">
                    
                        <Demographics :demographics="props.demographics.all" />
                    </div>
                </div>
                <div class="flex gap-5" v-if="selectedOption == 'Normal Records'">
                    <div class="w-7/12 ">
                        <StatisticsTable :statistics-results="statisticsResults.normal" />
                    </div>
                    <div class="w-5/12">
                    <!-- <pre>
                        {{ props.demographics.normal  }}
                    </pre> -->
                        <Demographics :demographics="props.demographics.normal" />
                    </div>
                </div>
                <div class="flex gap-5" v-if="selectedOption == 'PreDiabetes Records'">
                    <div class="w-7/12 ">
                        <StatisticsTable :statistics-results="statisticsResults.prediabetes" />
                    </div>
                    <div class="w-5/12">
                    
                        <Demographics :demographics="props.demographics.prediabetes" />
                    </div>
                </div>
                <div class="flex gap-5" v-if="selectedOption == 'Diabetic Records'">
                    <div class="w-7/12 ">
                        <StatisticsTable :statistics-results="statisticsResults.diabetes" />
                    </div>
                    <div class="w-5/12">
                        <Demographics :demographics="props.demographics.diabetes" />
                    </div>
                </div>

            </div>
            <div class="" v-if="activeTab === 'allHb1ac'">

                <AllValuesTable :all-results="results" />
            </div>
            <div class="" v-if="activeTab === 'trends'">
                <!-- <pre>
                    {{ results }}
                </pre> -->
                <div class="flex gap-5 ">
                    <div class="w-1/3">
                        <TrendsTable :statistics-results="statisticsResults.all" @record="recordId = $event" />
                    </div>
                    <div class="w-2/3">
                        <LineChart :data="results" :record-id="recordId" />
                    </div>
                </div>

            </div>


        </div>

    </AppLayout>
</template>
<style scoped>
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