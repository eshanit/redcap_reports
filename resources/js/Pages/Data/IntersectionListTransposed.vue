<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import pickBy from 'lodash/pickBy';
import throttle from 'lodash/throttle';
import mapValues from 'lodash/mapValues';
import Pagination from '@/Components/Pagination.vue';
import Dropdown from '@/Components/Dropdown.vue';
import ArrowUpIcon from 'vue-material-design-icons/ArrowUp.vue';
import ArrowDownIcon from 'vue-material-design-icons/ArrowDown.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import numberWithSpaces from '@/Utilities/numberWithSpaces';
import SearchFilter from '@/Components/SearchFilter.vue'
import { computed } from 'vue';

import { ref, watch } from 'vue';
import { filter } from 'lodash';
import Record from '../Reports/Record.vue';


interface RecordData {
    [key: string]: {
        [key: string]: string;
    };
}
const props = defineProps<{
    records: any,
    'filters': any,
    'requests': any,
    'project_id': string,
    'field_name': string,
    'value': any,
    'project': any,
}>();

const pseudoFields = {
    record: 'Record',
    event_id: 'Event',
    field_name: 'Field Name',
    value: 'Response',
    form_name: 'Form Name',
    units: 'Units'
}

const form = ref({
    search: props.filters.search,
    trashed: props.filters.trashed,
})

const reset = () => {
    form.value = mapValues(form.value, () => null)
}

const sortField = ref(props.filters.sort?.replace('-', '') || '');
const sortDirection = ref(props.filters.sort?.startsWith('-') ? 'desc' : 'asc');

// Compute the fields from the first record
const fields = computed(() => {
    const firstRecord = Object.values(props.records.data)[0];
    return firstRecord ? Object.keys(firstRecord) : [];
});

const applyFilters = (requestData: any) => {
    console.log('requestData: ', requestData);
    router.get(`/project/${props.project_id}/intersection/filtered/query`, requestData, { preserveState: true });
};

// const sort = (field: any) => {
//     props.requests.sort = props.requests.sort === field ? `-${field}` : field;
//     applyFilters();
// };
const searchField = ref(null)
const sortingField = ref(null)

const sort = (field: any) => {
    const requestData = []
    props.requests.forEach((request: any) => {
        // request.sort === field ? `-${field}` : field; 
        sortingField.value = field
        requestData.push({
            index: request.index,
            field_name: request.field_name,
            event_name: request.event_name,
            event_id: request.event_id,
            values: request.values,
            operator: request.operator,
            search: searchField.value,
            sort: sortingField.value
        })

    });
    console.log(field, requestData)
    applyFilters(requestData);
};




watch(form, throttle(function () {
    router.get(`/project/${props.project_id}/intersection/filtered/query`, pickBy(form.value), { preserveState: true })
}, 150), {
    deep: true
})


//

// Unique events for column headers
const uniqueEvents = computed(() => {
  const events = new Set();
  props.records.data.forEach(record => {
    events.add(record.event); // Assuming event is an object
  });
  return Array.from(events);
});

// Group records by their IDs and organize values by events
const groupedRecords = computed(() => {
  const groups: { recordId: string; values: { [key: string]: string } }[] = [];

  props.records.data.forEach(record => {
    const eventId = record.event.id; // Assuming event has an id
    const recordId = record.record; // Assuming record has an id
    const eventName = record.event.name; // Assuming event has a name

    // Check if the record already exists in groups
    const existingRecord = groups.find(group => group.recordId === recordId);
    if (!existingRecord) {
      groups.push({ recordId, values: { [eventId]: record.value } }); // Assuming value is what you want to display
    } else {
      existingRecord.values[eventId] = record.value; // Add or update value for the event
    }
  });

  return groups;
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

        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <!-- <pre>
    {{ props.requests }}
</pre> -->
<div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
    <div>
      <div class="p-20">
        <div class="overflow-x-auto bg-white rounded-md shadow">
          <div class="pb-10 text-lg">
            List of records where the response for question 
            <span class="italic text-orange-500">{{ field_name }}</span> was 
            <span class="italic text-orange-500">{{ value }}</span>
          </div>
          <div class="flex justify-between mb-6 py-2.5">
            <search-filter v-model="form.search" class="w-full max-w-md mr-1" search="Search Projects..." @reset="reset" />
            <div class="px-10 text-3xl">
              <span class="text-teal-500">{{ numberWithSpaces(records.total) }}</span>
              <span v-if="records.total == 1">Record</span>
              <span v-else>Records</span>
            </div>
          </div>
          <table class="w-full whitespace-nowrap">
            <thead>
              <tr class="font-bold text-left">
                <th class="px-6 pt-6 pb-4">Record</th>
                <th v-for="event in uniqueEvents" :key="event.id" class="px-6 pt-6 pb-4">
                  {{ event.name }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(record, recordIndex) in groupedRecords" :key="recordIndex">
                <td class="px-6 py-4 border-t">{{ record.recordId }}</td>
                <td
                  v-for="event in uniqueEvents"
                  :key="event.id"
                  class="px-6 py-4 border-t"
                >
                  {{ record.values[event.id] || '' }}
                </td>
              </tr>
              <tr v-if="records.data.length === 0">
                <td class="px-6 py-4 border-t" colspan="4">No projects found.</td>
              </tr>
            </tbody>
          </table>
          <Pagination class="mt-6" :links="records.links" />
        </div>
      </div>
    </div>
  </div>
            </div>
        </div>



    </AppLayout>
</template>