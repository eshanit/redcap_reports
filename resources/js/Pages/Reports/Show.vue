<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SurveyItemDataList from '@/Components/Redcap/SurveyItemDataList.vue'
import SurveyItemDataInsights from '@/Components/Redcap/SurveyItemDataInsights.vue'
import SurveyEventDataInsights from '@/Components/Redcap/SurveyEventDataInsights.vue'


import { ref, computed } from 'vue';

const activeTab = ref('dataList');

const setActiveTab = (tab) => {
    activeTab.value = tab;
};

const tabClass = (tab) => {
    return activeTab.value === tab ? 'bg-blue-500' : 'bg-gray-500';
};

const props = defineProps<{
    'reportData': any,
    'valueCounts': any[],
    'valueCountsByEventId': any[],
    'mapEventToId': any[],
    'values': any[],
}>()

const back = () => {
    window.history.back();
}

</script>
<template>
    <AppLayout title="entry">
        <template #header>
            <div class="flex gap-2.5">
                <div class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" href="#" @click="back">
                    Back
                    </Link>
                </div>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div>
                    <span v-html="reportData.data[0].element_lable" /> (<span class="text-sm italic text-green-500"> {{
                        reportData.data[0].field_name }}</span>)
                </div>
            </div>

        </template>

        <div class="sm:px-6 lg:px-8">
            <div class="mx-auto">
                <ul class="flex justify-center space-x-2 text-white">
                    <li>
                        <button @click="setActiveTab('dataList')" :class="tabClass('dataList')"
                            class="inline-block px-4 py-2 transition duration-300 ease-in-out transform rounded-t-lg hover:scale-105">
                            Data List
                        </button>
                    </li>
                    <li>
                        <button @click="setActiveTab('insights')" :class="tabClass('insights')"
                            class="inline-block px-4 py-2 transition duration-300 ease-in-out transform rounded-t-lg hover:scale-105">
                            Insights
                        </button>
                    </li>
                    <li>
                        <button @click="setActiveTab('eventInsights')" :class="tabClass('eventInsights')"
                            class="inline-block px-4 py-2 transition duration-300 ease-in-out transform rounded-t-lg hover:scale-105">
                            Events Insights
                        </button>
                    </li>
                </ul>
                <div class="p-3 mt-6">
                    <div v-show="activeTab === 'dataList'">
                        <!-- Data List Content -->
                        <SurveyItemDataList :report-data="reportData" />
                    </div>
                    <div v-show="activeTab === 'insights'">
                        <!-- Insights Content -->
                        <SurveyItemDataInsights :report-data="reportData" :value-counts="valueCounts"
                            :values="values" />
                    </div>
                    <div v-show="activeTab === 'eventInsights'">
                        <!-- Data List Content -->
                        <SurveyEventDataInsights :report-data="reportData" :value-counts="valueCounts"
                            :map-event-to-id="mapEventToId" :values="values"
                            :value-counts-by-event-id="valueCountsByEventId" />

                    </div>

                </div>
            </div>
        </div>
    </AppLayout>

</template>