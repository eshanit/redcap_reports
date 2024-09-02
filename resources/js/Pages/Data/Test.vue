<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import { defineProps, computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import numberWithSpaces from '@/Utilities/numberWithSpaces';

import Record from '../Reports/Record.vue';

interface Event {
    id: number;
    name: string;
}

interface RecordData {
    record: string;
    event: Event;
    field_name: string;
    value: string;
    form_name: string;
}

const props = defineProps<{
    records:any,
    'filters': any,
    'project_id': string,
    'field_name': string,
    'value': any,
    'project': any,
}>();

const transformedData = computed(() => {
    const result: { record: string; event: string;[key: string]: string }[] = [];

    props.records.data.forEach(item => {
        const existingRecord = result.find(row => row.record === item.record);

        if (!existingRecord) {
            result.push({
                record: item.record,
                event: item.event.name,
                [item.field_name]: item.value,
            });
        } else {
            existingRecord[item.field_name] = item.value;
        }
    });

    return result;
});

const dynamicHeaders = computed(() => {
    const headers: Set<string> = new Set();

    props.records.data.forEach(item => {
        headers.add(item.field_name);
    });

    return Array.from(headers);
});

</script>
<template>
    <AppLayout title="Data ">
        <template #header>
            <div class="flex gap-1.5">
                <h2 class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/projects/${project.project_id}`">
                    {{ project.app_title }}
                    </Link>
                </h2>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/project/metadata/${project.project_id}`">
                    Metadata and Stats
                    </Link>
                </div>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/project/${project.project_id}/fieldname/${field_name}`">
                    {{ field_name }}: Insights
                    </Link>
                </div>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-orange-500">
                    {{ field_name }}: queried data list
                </div>
            </div>

        </template>
<!-- <pre>
    {{ records }}
</pre> -->
        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">

                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-collapse border-gray-200">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 p-2 bg-white border border-gray-300">Record</th>
                                    <th class="sticky p-2 bg-white border border-gray-300 left-24">Event</th>
                                    <th v-for="header in dynamicHeaders" :key="header"
                                        class="p-2 border border-gray-300">
                                        {{ header }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in transformedData" :key="row.record">
                                    <td class="sticky left-0 p-2 bg-white border border-gray-300">{{ row.record }}</td>
                                    <td class="sticky p-2 bg-white border border-gray-300 left-24">{{ row.event }}</td>
                                    <td v-for="header in dynamicHeaders" :key="header"
                                        class="p-2 border border-gray-300">
                                        {{ row[header] }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination class="mt-6" :links="records.links" />
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
<style scoped>
.table-container {
  position: relative;
}

.sticky {
  position: sticky;
  z-index: 10;
}

.sticky.left-0 {
  left: 0;
}

.sticky.left-24 {
  left: 6rem; /* Adjust this value based on your layout */
}
</style>