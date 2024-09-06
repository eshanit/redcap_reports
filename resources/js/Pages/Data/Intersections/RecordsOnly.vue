<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import { defineProps, computed } from 'vue';
import PrimaryOutlineButton from '@/Components/PrimaryOutlineButton.vue';
import Pagination from '@/Components/Pagination.vue';
import numberWithSpaces from '@/Utilities/numberWithSpaces';



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
        <!-- <pre>
    {{ records }}
</pre> -->
        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">

                <div class="text-3xl text-rose-500 py-2.5">
                    {{ Object.values(records).length }} | records
                </div>
                <div class="p-5 overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <div class="grid grid-cols-6 gap-5">
                            <div class="" v-for="record in records">
                                <Link class="" :href="`/project/${project.project_id}/record/${record}`">
                                <PrimaryOutlineButton>
                                    {{ record }}
                                </PrimaryOutlineButton>
                            </Link>
                            </div>
                        </div>

                        <!-- <table class="w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="sticky top-0 p-2 font-bold bg-gray-200">Record</th>
                                    <th class="sticky top-0 p-2 font-bold bg-gray-200">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, index) in records" :key="index"
                                    :class="index % 2 === 0 ? 'bg-gray-50' : 'bg-white'">
                                    <td class="sticky left-0 p-2 text-center">{{ row }}</td>
                                    <td class="p-2">
                                        <PrimaryButton>
                                            Go
                                        </PrimaryButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table> -->
                        <!-- <Pagination class="mt-6" :links="records.links" /> -->
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
<style scoped></style>