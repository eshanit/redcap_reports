<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import { defineProps, computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import numberWithSpaces from '@/Utilities/numberWithSpaces';
import groupBy from 'lodash/groupBy';;

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

const data = groupBy(props.records, 'record')


// Group data by field names
const groupedFields = computed(() => {
    const fieldMap: Record<string, { name: string; eventIds: number[]; eventNames: string[] }> = {};

    for (const records of Object.values(data)) {
        records.forEach(record => {
            if (!fieldMap[record.field_name]) {
                fieldMap[record.field_name] = { name: record.field_name, eventIds: [], eventNames: [], };
            }
            if (!fieldMap[record.field_name].eventIds.includes(record.event_id)) {
                fieldMap[record.field_name].eventIds.push(record.event_id);
            }

            if (!fieldMap[record.field_name].eventNames.includes(record.event)) {
                fieldMap[record.field_name].eventNames.push(record.event);
            }
        });
    }

    return Object.values(fieldMap);
});

const records = computed(() => {
    return Object.entries(data).map(([key, records]) => {
        const values: Record<string, Record<number, string | undefined>> = {};
        records.forEach(record => {
            if (!values[record.field_name]) {
                values[record.field_name] = {};
            }
            values[record.field_name][record.event_id] = record.value;
        });
        return { record: key, values };
    });
});


const filterSimilarKeys = computed(() => {
    // Create an array to hold the transformed records
  const result = [];

// Iterate through each record
records.value.forEach(item => {
  const newItem = { record: item.record, values: {} };
  
  // Get the field names
  const fields = Object.keys(item.values);
  
  // Find common keys in values
  const commonKeys = Object.keys(item.values[fields[0]]).filter(key =>
    fields.every(field => item.values[field].hasOwnProperty(key))
  );

  // If there are common keys, add them to the new item
  if (commonKeys.length > 0) {
    fields.forEach(field => {
      newItem.values[field] = {};
      commonKeys.forEach(key => {
        newItem.values[field][key] = item.values[field][key];
      });
    });
    result.push(newItem);
  }
});

return result;
})

//
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
                <div class="text-xl text-orange-500">
                    queried data list
                </div>
            </div>

        </template>
        <pre>
    <!-- {{ groupBy(records,'record') }} -->
    <!-- {{ transformedData }} -->
      <!-- {{ filterSimilarKeys }}
    {{ records }} -->
</pre>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300">
                <thead>
                    <tr>
                        <th class="p-2 border border-gray-300">Record</th>
                        <th v-for="(field, index) in groupedFields" :key="index" :colspan="field.eventIds.length"
                            class="p-2 text-center border border-gray-300">
                            {{ field.name }}
                        </th>
                    </tr>
                    <tr>
                        <th class="p-2 border border-gray-300"></th>
                        <template v-for="field in groupedFields" :key="field.name">
                            <template v-for="event in field.eventNames" :key="event">
                                <th class="p-2 border border-gray-300">
                                    {{ event }}
                                </th>
                            </template>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="record in filterSimilarKeys" :key="record.record">
                        <td class="p-2 border border-gray-300">{{ record.record }}</td>
                        <template v-for="field in groupedFields" :key="field.name">
                            <template v-for="event in field.eventIds" :key="event">
                                <td class="p-2 border border-gray-300">
                                    {{ record.values[field.name][event] || '-' }}
                                </td>
                            </template>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
<style scoped>
table {
    border-collapse: collapse;
}
</style>