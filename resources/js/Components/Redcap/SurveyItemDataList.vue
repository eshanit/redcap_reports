<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InsightsFieldNameInfo from './InsightsFieldNameInfo.vue';

const props = defineProps<{
    'reportData': any,
}>()
</script>
<template>
    <div class="sm:px-6 lg:px-8">
        <div class="relative py-12">
            <div class="max-w-full mx-auto">
                <div class="grid grid-cols-9 gap-5">
                    <div class="sticky top-0 grid col-span-3">
                        <InsightsFieldNameInfo :report-data="reportData.data[0]" />
                    </div>
                    <div class="grid col-span-6">
                        <!-- Other content that can be scrolled -->
                        <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                            <div>
                                <div class="p-20">
                                    <div class="pb-5 text-3xl font-semibold border-b">
                                        Responses
                                    </div>
                                    <div class="overflow-x-auto bg-white rounded-md shadow">
                                        <table class="w-full whitespace-nowrap">
                                            <tr class="font-bold text-left">
                                                <th class="px-6 pt-6 pb-4">Record</th>
                                                <th class="px-6 pt-6 pb-4">Event</th>
                                                <th class="px-6 pt-6 pb-4">Response</th>
                                                <th class="px-6 pt-6 pb-4">Units</th>
                                                <th class="px-6 pt-6 pb-4">View</th>

                                                <!--th class="px-6 pt-6 pb-4" colspan="2">Phone</th-->
                                            </tr>
                                            <tr v-for="entry in reportData.data"
                                                class="hover:bg-gray-100 focus-within:bg-gray-100 ">
                                                <td class="px-2 border-t py-2.5">

                                                    {{ entry.record }}

                                                </td>

                                                <td class="px-2 border-t py-2.5">

                                                    {{ entry.event }}

                                                </td>
                                                <td class="border-t py-2.5">

                                                    {{ entry.value }}

                                                </td>
                                           
                                                <td class="border-t py-2.5">
                                                    <span v-if="entry.field_units">
                                                        {{ entry.field_units }}
                                                    </span>
                                                    <span v-else>
                                                        -
                                                    </span>
                                                </td>
                                                <td class="border-t">
                                                    <div>
                                                        <Link class="btn-indigo"
                                                            :href="`/project/${reportData.data[0].project_id}/event/${entry.event_id}/respondent/${entry.record}`">
                                                        <PrimaryButton>
                                                            Record
                                                        </PrimaryButton>
                                                        </Link>
                                                    </div>
                                                </td>
                                                <!-- <td class="border-t py-2.5">
            <div>
                <Link class="btn-indigo" :href="`/entrys/${entry.id}`">

                <PrimaryButton>
                    view
                </PrimaryButton>
                </Link>
            </div>
        </td> -->

                                            </tr>
                                            <tr v-if="reportData.data === 0">
                                                <td class="px-6 py-4 border-t" colspan="4">No
                                                    entries found.
                                                </td>
                                            </tr>
                                        </table>
                                        <Pagination class="mt-6" :links="reportData.links" />

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>