<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'

const props = defineProps<{
    'record': any,
    'project': any
}>()

const back = () => {
    window.history.back();
}

</script>
<template>
    <AppLayout title="field Metadta">
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
                    ...
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

                    {{ record[0].value }}

                </div>
            </div>
        </template>


        <div class="sm:px-6 lg:px-8">
            <div class="py-12 ">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div class="p-20">
                        <div class="w-1/3 pb-10">
                            <div class="grid grid-cols-2 gap-5">
                                <div class="font-bold ">
                                    Record
                                </div>
                                <div>
                                    {{ record[0].value  }}
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-5">
                                <div class="font-bold ">
                                    Event
                                </div>
                                <div>
                                    {{ record[0].event  }}
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-5">
                                <div class="font-bold ">
                                    Project
                                </div>
                                <div>
                                    {{ project.app_title  }}
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto bg-white rounded-md shadow">
                            <table class="w-full whitespace-nowrap">
                                <tr class="font-extrabold text-left text-teal-500">
                                    <th class="px-6 pt-6 pb-4">Field</th>
                                    <th class="px-6 pt-6 pb-4">Possible Answers</th>
                                    <th class="px-6 pt-6 pb-4">Form/Instrument</th>
                                    <th class="px-6 pt-6 pb-4">Field Units</th>
                                    <th class="px-6 pt-6 pb-4">Response</th>
                                </tr>
                                <tr v-for="(field, i) in record" :key="i"
                                    class=" hover:bg-gray-100 focus-within:bg-gray-100">
                                    <td class="px-2 py-5 border-t ">
                                        <div class="" v-html="field.element_label" />
                                        <div class="text-xs font-light text-gray-500" v-html="field.element_note" />
                                    </td>
                                    <td class="py-5 border-t">
                                        <div v-html="field.element_enum" />
                                    </td>
                                    <td class="border-t y-5 ">
                                        {{ field.form_name }}
                                    </td>
                                    <td class="py-5 border-t">
                                        <span v-if="field.field_units">
                                            {{ field.field_units }}
                                        </span>
                                        <span v-else>
                                            -
                                        </span>
                                    </td>
                                    <td class="py-5 border-t">
                                        {{ field.value }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </AppLayout>
</template>