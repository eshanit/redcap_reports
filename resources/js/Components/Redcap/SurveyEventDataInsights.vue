<script setup lang="ts">
import InsightsGeneralInformation from './InsightsGeneralInformation.vue';
import InsightsDataCounts from './InsightsDataCounts.vue';
import InsightsDescriptiveStatistics from './InsightsDescriptiveStatistics.vue';
import InsightsEventDataCounts from './InsightsEventDataCounts.vue'

const props = defineProps<{
    'reportData': any,
    'valueCounts': any,
    'mapEventToId': any
    'valueCountsByEventId': any,
    'values': any[],
}>()


</script>
<template>
    <!-- <pre>
    {{ reportData }}
</pre> -->
    <div class="grid grid-cols-4 gap-5">
        <div id="general-information">
            <InsightsGeneralInformation :report-data="reportData.data[0]" />
        </div>

        <div id="insights-counts">
            <InsightsDataCounts :report-data="reportData.data[0]" :value-counts="valueCounts" :max-array-count="25" />
        </div>
        <div id="descriptive-statistics">
            <div class="font-bold text-orange-500 border-b py-2.5">
                Simple Descriptive Statistics
            </div>
            <div class="py-2.5">
                <InsightsDescriptiveStatistics :data-array="values" />
            </div>
        </div>
    </div>
    <div class="py-5 ">
        <!-- <pre>

              {{ rows }}
              {{ surveyEvents }}
            {{ transformedData }}
            
        </pre> -->
        <InsightsEventDataCounts 
        :report-data="reportData.data[0]" 
        :map-event-to-id="mapEventToId"
        :value-counts-by-event-id="valueCountsByEventId"
            :max-array-count="25" />

    </div>
</template>