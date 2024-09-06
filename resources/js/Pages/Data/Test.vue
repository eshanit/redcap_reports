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
    records: any,
    'filters': any,
    'project_id': string,
    'field_name': string,
    'value': any,
    'project': any,
}>();
const dynamicHeaders = computed(() => {
    const headers = new Set<string>();
    props.records.data.forEach(item => {
        headers.add(item.field_name);
    });
    return Array.from(headers);
});

const groupedData = computed(() => {
    const result: Array<{ record: string; eventName: string; fields: Record<string, string> }> = [];

    props.records.data.forEach(item => {
        const { record, event, field_name, value } = item;
        const eventName = event.name;

        // Find or create the entry for this record and event
        let entry = result.find(r => r.record === record && r.eventName === eventName);
        if (!entry) {
            entry = {
                record,
                eventName,
                fields: {}
            };
            result.push(entry);
        }

        // Add the field value to the fields object
        entry.fields[field_name] = value;
    });

    return result;
});

const back = () => {
    window.history.back();
}
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
                    <Link class="btn-indigo" href="#" @click="back">
                    Back
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
        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <div class="relative">
                            <table class="min-w-full border border-collapse border-gray-300">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="sticky left-0 z-10 px-4 py-2 bg-gray-200 border border-gray-300">
                                            Record</th>
                                        <th class="sticky z-10 px-4 py-2 bg-gray-200 border border-gray-300 left-12">
                                            Event</th>
                                        <th v-for="(key, index) in dynamicHeaders" :key="index"
                                            class="px-4 py-2 border border-gray-300">
                                            {{ key }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in groupedData" :key="index">
                                        <td class="sticky left-0 z-0 px-4 py-2 bg-orange-300 border border-gray-300">{{
                        item.record
                    }}</td>
                                        <td class="sticky z-0 px-4 py-2 bg-orange-300 border border-gray-300 left-12">{{
                            item.eventName }}</td>
                                        <td v-for="(key, idx) in dynamicHeaders" :key="idx"
                                            class="px-4 py-2 border border-gray-300">
                                            {{ item.fields[key] || '' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination class="mt-6" :links="records.links" />
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
<style scoped>
/* Additional styles for fixed columns */
.table-container {
    position: relative;
    overflow-x: auto;
}

.sticky {
    position: sticky;
    background-color: white;
    /* Adjust as needed */
}

.sticky.left-0 {
    left: 0;
}

.sticky.left-12 {
    left: 12rem;
    /* Adjust based on the width of the first column */
}
</style>