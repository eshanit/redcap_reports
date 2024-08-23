<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import pickBy from 'lodash/pickBy';
import throttle from 'lodash/throttle';
import mapValues from 'lodash/mapValues';
import SearchFilter from '@/Components/SearchFilter.vue'
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ArrowUpIcon from 'vue-material-design-icons/ArrowUp.vue';
import ArrowDownIcon from 'vue-material-design-icons/ArrowDown.vue';

import { ref, watch } from 'vue';
import { filter } from 'lodash';

const props = defineProps<{
    'filters': any
    'projects': any
}>()

const form = ref({
    search: props.filters.search,
    trashed: props.filters.trashed,
})

const reset = () => {
    form.value = mapValues(form.value, () => null)
}

const sortField = ref(props.filters.sort?.replace('-', '') || '');
const sortDirection = ref(props.filters.sort?.startsWith('-') ? 'desc' : 'asc');


const applyFilters = () => {
    router.get('/projects', props.filters, { preserveState: true });
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
    router.get('/projects', pickBy(form.value), { preserveState: true })
}, 150), {
    deep: true
})

</script>
<template>
    <!-- {{ projects.data }} -->
    <AppLayout title="Projects">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Projects
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div>
                        <div class="p-20">
                            <div class="overflow-x-auto bg-white rounded-md shadow">
                                <div class="items-center mb-6 py-2.5">
                                    <search-filter v-model="form.search" class="w-full max-w-md mr-1 "
                                        search="Search Projects..." @reset="reset">
                                        <label class="block text-gray-700">Trashed:</label>
                                        <select v-model="form.trashed" class="w-full mt-1 form-select">
                                            <option :value="null" />
                                            <option value="with">With Trashed</option>
                                            <option value="only">Only Trashed</option>
                                        </select>
                                    </search-filter>
                                </div>
                                <table class="w-full whitespace-nowrap">
                                    <tr class="font-bold text-left">
                                        <th class="flex px-6 pt-6 pb-4 cursor-pointer">
                                            <SecondaryButton  @click="sort('app_title')">
                                                Name

                                                <span v-if="filters.sort === 'app_title'">
                                                    <ArrowUpIcon class="text-blue-500 font-extralight" :size="20" />
                                                </span>
                                                <span v-else>
                                                    <ArrowDownIcon class="text-red-500 font-extralight" :size="20" />
                                                </span>
                                            </SecondaryButton>


                                        </th>
                                        <th class="px-6 pt-6 pb-4 cursor-pointer" @click="sort('creation_time')">
                                            <Dropdown>
                                                <template #trigger>
                                                    <span class="inline-flex rounded-md">
                                                        <button type="button"
                                                            class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-50 active:bg-gray-50 ">
                                                            DATE CREATED
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
                                        <th class="px-6 pt-6 pb-4">View</th>
                                        <!--th class="px-6 pt-6 pb-4" colspan="2">Phone</th-->
                                    </tr>
                                    <tr v-for="project in projects.data" :key="project.id"
                                        class="hover:bg-gray-100 focus-within:bg-gray-100">
                                        <td class="border-t">
                                            <Link class="flex items-center px-6 py-4 focus:text-indigo-500"
                                                :href="`/projects/${project.id}/edit`">
                                            {{ project.app_title }}
                                            <icon v-if="project.deleted_at" name="trash"
                                                class="w-3 h-3 ml-2 shrink-0 fill-gray-400" />
                                            </Link>
                                        </td>
                                        <td class="border-t">
                                            <Link class="flex items-center px-6 py-4"
                                                :href="`/projects/${project.id}/edit`" tabindex="-1">
                                            <div v-if="project.creation_time">
                                                {{ project.creation_time }}
                                            </div>
                                            </Link>
                                        </td>
                                        <td class="border-t">
                                            <div>
                                                <Link class="btn-indigo" :href="`/projects/${project.id}`">
                                                <!-- <button
                                                    class="px-4 py-2 font-bold text-white bg-blue-500 rounded-lg hover:bg-blue-700">
                                                    <span>View</span>
                                                    <span class="hidden md:inline">&nbsp;Project</span>
                                                </button> -->
                                                <PrimaryButton>
                                                    Project
                                                </PrimaryButton>
                                                </Link>
                                            </div>
                                        </td>

                                    </tr>
                                    <tr v-if="projects.data.length === 0">
                                        <td class="px-6 py-4 border-t" colspan="4">No projects found.</td>
                                    </tr>
                                </table>
                                <Pagination class="mt-6" :links="projects.links" />

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>