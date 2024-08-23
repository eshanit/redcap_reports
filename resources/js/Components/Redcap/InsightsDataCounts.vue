<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import _ from 'lodash';
import numberWithSpaces from '@/Utilities/numberWithSpaces';
import OrangeBadge from '@/Components/OrangeBadge.vue';
import { ref, computed } from 'vue';

const props = defineProps<{
    'reportData': any,
    'valueCounts': any,
    'maxArrayCount': number,
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

</script>
<template>
    <div class="" v-if="dataKeys.valueLength <= maxArrayCount">
        <div class="font-bold text-orange-500 border-b py-2.5">
            Data Counts
        </div>
        <div class="grid grid-cols-3 gap-1 pb-2.5">
            <div class=" py-2.5 font-semibold ">Keys: </div>
            <div class=" py-2.5 font-semibold ">Counts </div>
            <div class=" py-2.5 font-semibold ">% </div>
        </div>


        <div class="grid grid-cols-3 gap-1 ">
            <div>
                <div class=" py-1.5" v-for="(dataKey, i) in dataKeys.keys">
                    {{ dataKey }}
                </div>
            </div>
            <div>
                <div class=" py-1.5" v-for="(dataValue, i) in dataKeys.values ">
                    <Link class="btn-indigo"
                        :href="`/project/${reportData.project_id}/question/${reportData.field_name}/response/${dataKeys.keys[i]}`">
                    <OrangeBadge color="green" :text="numberWithSpaces(dataValue)" />
                    </Link>
                    <!-- {{ numberWithSpaces(dataValue) }} -->
                </div>
            </div>
            <div>
                <div class=" py-1.5" v-for="dataValue in dataKeys.values ">
                    {{ (100 * dataValue / dataKeys.total).toFixed(2) }}
                </div>
            </div>
        </div>
    </div>

</template>