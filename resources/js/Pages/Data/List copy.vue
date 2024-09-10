<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import pickBy from 'lodash/pickBy';
import throttle from 'lodash/throttle';
import mapValues from 'lodash/mapValues';
import Pagination from '@/Components/Pagination.vue';
import Dropdown from '@/Components/Dropdown.vue';
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
    'project_id': string,
    'field_name': string,
    'value': any,
    'project': any,
}>();

const pseudoFields = {
    record: 'Record',
    event: 'Event',
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

const applyFilters = () => {
    router.get(`/project/${props.project_id}/question/${props.field_name}/response/${props.value}`, props.filters, { preserveState: true });
};

const sort = (field: any) => {
    props.filters.sort = props.filters.sort === field ? `-${field}` : field;
    applyFilters();
};

const toggleSort = (field: any) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    props.filters.value.sort = sortDirection.value === 'asc' ? field : `-${field}`;
    applyFilters();
};

watch(form, throttle(function () {
    router.get(`/project/${props.project_id}/question/${props.field_name}/response/${props.value}`, pickBy(form.value), { preserveState: true })
}, 150), {
    deep: true
})

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
                <div class="text-2xl text-orange-500">
                    {{ field_name }}: queried data list
                </div>
            </div>

        </template>

        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">

                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div>
                        <div class="p-20">
                            <div class="overflow-x-auto bg-white rounded-md shadow">
                                <div class="pb-10 text-lg">
                                    List of records where the response for question <span
                                        class="italic text-orange-500">{{
                        field_name }}</span> was <span class="italic text-orange-500">{{ value }}</span>
                                </div>
                                <div class="flex justify-between mb-6 py-2.5">
                                    <search-filter v-model="form.search" class="w-full max-w-md mr-1 "
                                        search="Search Projects..." @reset="reset">
                                    </search-filter>
                                    <div class="px-10 text-3xl" >
                                        <span class="text-teal-500">{{ numberWithSpaces(records.total) }} </span>
                                        <span v-if="records.total == 1">
                                            Record
                                        </span>
                                        <span v-else>
                                            Records
                                        </span>
                                    </div>

                                </div>
                                <table class="w-full whitespace-nowrap">
                                    <tr class="font-bold text-left">

                                        <th class="px-6 pt-6 pb-4 cursor-pointer" @click="sort(field)"
                                            v-for="field in fields" :key="field">
                                            <Dropdown>
                                                <template #trigger>
                                                    <span class="inline-flex rounded-md">
                                                        <button type="button"
                                                            class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-50 active:bg-gray-50 ">
                                                            {{ pseudoFields[field] }}
                                                            <svg class="ms-2 -me-0.5 h-4 w-4"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                            </svg>
                                                        </button>
                                                    </span>
                                                </template>
                                            </Dropdown>

                                        </th>

                                        <th class="px-6 pt-6 pb-4">VIEW</th>
                                    </tr>
                                    <tr v-for="(record, recordKey) in records.data" :key="recordKey"
                                        class="hover:bg-gray-100 focus-within:bg-gray-100">
                                        <td class="border-t " v-for="field in fields" :key="field">
                                            <span
                                                class="flex items-center px-6 py-4 focus:text-indigo-500" v-if="field === 'event'">{{ record[field].name
                        ||
                                                '' }}
                                            </span>
                                            <span
                                                class="flex items-center px-6 py-4 focus:text-indigo-500" v-else>{{ record[field]
                        ||
                                                '' }}
                                            </span>
                                        </td>
                                        <td class="border-t">
                                            <div>
                                                <Link class="btn-indigo" :href="`/project/${project.project_id}/event/${record['event'].id}/respondent/${record['record']}`">
                                                <!-- <button
                                                    class="px-4 py-2 font-bold text-white bg-blue-500 rounded-lg hover:bg-blue-700">
                                                    <span>View</span>
                                                    <span class="hidden md:inline">&nbsp;Project</span>
                                                </button> -->
                                                <PrimaryButton>
                                                    Record
                                                </PrimaryButton>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="records.data.length === 0">
                                        <td class="px-6 py-4 border-t" colspan="4">No projects found.</td>
                                    </tr>
                                </table>
                                <Pagination class="mt-6" :links="records.links" />

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- <div>
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Record</th>
                        <th v-for="fieldName in Object.keys(records[Object.keys(records)[0]])" :key="fieldName"
                            class="px-4 py-2">
                            {{ fieldName }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(record, recordKey) in records" :key="recordKey" class="border-b">
                        <td class="px-4 py-2">{{ recordKey }}</td>
                        <td v-for="(values, fieldName) in record" :key="`${recordKey}-${fieldName}`" class="px-4 py-2">
                            <template v-for="(value, index) in values" :key="index">
                                {{ value }}
                                <br v-if="index < values.length - 1">
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div> -->


    </AppLayout>
</template>