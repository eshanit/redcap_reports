<script setup lang="ts">
import InsightsDescriptiveStatistics from './InsightsDescriptiveStatistics.vue';
import InsightsGeneralInformation from './InsightsGeneralInformation.vue';
import InsightsDataCounts from './InsightsDataCounts.vue';
import numberWithSpaces from '@/Utilities/numberWithSpaces';
import chunkArrayDescription from '@/Utilities/chunkArrayDescription'
import useCalculateHistogramBins from '@/Composables/useCalculateHistogramBins';
import ChartsD3Histogram from '@/Components/Charts/D3/Histogram.vue';
import BarD3Chart from '@/Components/Charts/D3/BarChart.vue';
import DensityD3Chart from '@/Components/Charts/D3/DensityChart.vue';
import _ from 'lodash';
import { ref, computed } from 'vue';

const numbersFloat = ref([]);
const numbersInt = ref([]);

const props = defineProps<{
    'reportData': any,
    'valueCounts': any,
    'values': any[],
}>()

const dataKeys = computed(() => {

    const values: Array<any> = Object.values(props.valueCounts)

    if (props.valueCounts) {
        return {
            keys: Object.keys(props.valueCounts),
            values: values,
            valueLength: values.length,
            total: _.sum(values)
        };
    }
})

///
props.values.forEach((value) => {
    numbersFloat.value.push(parseFloat(value));
    numbersInt.value.push(parseInt(value));
})

</script>
<template>

    <div class="grid grid-cols-4 gap-5">
        <div id="general-information">
            <InsightsGeneralInformation :report-data="reportData.data[0]" />
        </div>
        <div id="insights-counts">
            <InsightsDataCounts :report-data="reportData.data[0]" :value-counts="valueCounts" :max-array-count="50" />
        </div>
        <div>
            <div class="font-bold text-orange-500 border-b py-2.5">
                Simple Descriptive Statistics
            </div>
            <div class="py-2.5">
                <InsightsDescriptiveStatistics :data-array="values" />
            </div>
        </div>
    </div>

    <div class="py-5 " v-if="dataKeys.valueLength <= 25">
        <div class="font-bold text-orange-500 border-b py-2.5">
            Data Diffs (CrossTab)
        </div>
        <table class="min-w-full py-1 bg-white border rounded-lg">
            <thead>
                <tr>
                    <th class="px-4 py-2 border">Field</th>
                    <th v-for="(value, valuecount) in valueCounts" :key="valuecount" class="px-4 py-2 border">{{
                    valuecount }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(value1, valuecount1) in valueCounts" :key="valuecount1">
                    <td class="px-4 py-2 border">{{ numberWithSpaces(valuecount1) }}</td>
                    <td v-for="(value2, valuecount2) in valueCounts" :key="valuecount2"
                        class="px-4 py-2 border cursor-pointer hover:bg-slate-100 hover:text-green-500">
                        <span v-if="valuecount1 === valuecount2"> - </span>
                        <span v-else class="">
                            <span class="italic text-red-500" v-if="value1 - value2 > 0">
                                ({{ numberWithSpaces(Math.abs(value1 - value2)) }})
                            </span>
                            <span v-else>
                                {{ numberWithSpaces(Math.abs(value1 - value2)) }}
                            </span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="py-5 ">
        <span v-if="reportData.data[0].element_type === 'select' || reportData.data[0].element_type === 'radio' || reportData.data[0].element_type === 'yesno' || reportData.data[0].element_type === 'text'">
            <BarD3Chart :data-object="valueCounts" />
        </span>
        <span v-else>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <ChartsD3Histogram :data-array="numbersFloat" />
                </div>
                <div>
                    <DensityD3Chart :data-array="numbersFloat" />
                </div>
            </div>
           
            
        </span>

    </div>
    <pre>
        <!-- {{ chunkArrayDescription(numbersFloat,700) }}

        {{ useCalculateHistogramBins(numbersFloat) }} -->

        <!-- {{ numbersFloat }} -->
          {{valueCounts}}
    </pre>

</template>./InsightsDescriptiveStatistics.vue