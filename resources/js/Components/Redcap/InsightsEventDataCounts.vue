<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GreenBadge from '@/Components/GreenBadge.vue';
import { ref, computed } from 'vue';

const props = defineProps<{
    'reportData': any,
    'maxArrayCount': any,
    'mapEventToId': any,
    'valueCountsByEventId': any
}>()

const eventIdToEventMap = props.mapEventToId.reduce((acc: { [x: string]: any; }, curr: { event_id: string | number; event: any; }) => {
  acc[curr.event_id] = curr.event;
  return acc;
}, {});


const surveyEvents = computed(() => {
  return Object.keys(props.valueCountsByEventId)
});

const transformedData = computed(() => {
    const result: any = {};
    for (const [key, value] of Object.entries(props.valueCountsByEventId)) {
        for (const [innerKey, innerValue] of Object.entries(value)) {
            if (!result[innerKey]) {
                result[innerKey] = {};
            }
            result[innerKey][key] = innerValue;
        }
    }
    return result;
});

const rows = computed(() => Object.keys(transformedData.value));

</script>
<template>

    <div class="p-4" v-if="rows.length <= maxArrayCount">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-zinc-400">
                <tr>
                    <th class="px-4 py-2 text-white border-b">Events</th>
                    <th v-for="(value, key) in rows" :key="key" class="px-4 py-2 border-b">{{ value }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, key) in surveyEvents" :key="key">
                    <td class="px-4 py-2 text-center border-b ">{{ eventIdToEventMap[row] }}</td>
                    <td v-for="(value, colKey) in transformedData" :key="colKey" class="px-4 py-2 text-center border-b">
                        <span v-if="value[row]">
                            <Link class="btn-indigo"
                                :href="`/project/${reportData.project_id}/event/${row}/question/${reportData.field_name}/response/${colKey}`">
                            <GreenBadge :text="value[row]" />
                            </Link>
                        </span>
                        <span v-else>
                            -
                        </span>

                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>