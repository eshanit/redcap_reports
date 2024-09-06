<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Eventracker from '@/Components/Redcap/Eventracker.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import FieldRadioInput from '@/Components/FieldRadioInput.vue';
import { ref } from 'vue';

const props = defineProps<{
    project: any,
    recordId: any,
    metadata: any,
}>()

const selected_field = ref('')

const back = () => {
    window.history.back();
}

</script>
<template>

    <AppLayout title="Record FieldTracking ">
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
                    Tracking | <span class="font-bold">{{ recordId }}</span>
                </div>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <div class="flex gap-5 ">
                    <div class="w-1/4 pt-5 overflow-hidden bg-white shadow-xl sm:rounded-lg">
                        <div class="">
                            <div class="p-20 ">
                                <!-- {{ selected_field }} -->
                                <div class="pb-5 text-xl font-semibold text-green-500 border-b">
                                    1. Select the field that you want to track
                                </div>
                                <div class="p-5 overflow-x-auto h-[1000px]">
                                    <FieldRadioInput v-model="selected_field" :options="metadata" name="events" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-1/3">
                        <div class="p-20 ">
                            <div v-if="selected_field">
                                <div class="pb-5 text-xl font-semibold text-green-500 border-ben">
                                    Below is the data for <span class="italic font-thin text-orange-500">{{selected_field}}</span> over the
                                    events
                                </div>
                                <Eventracker :project-id="project.project_id" :field-name="selected_field"
                                    :record="recordId" />
                            </div>
                            <div v-else>
                                Select a field to track
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </AppLayout>

</template>
