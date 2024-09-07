<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SurveyEvents from '@/Components/Redcap/SurveyEvents.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue';
import { Ref, ref } from 'vue';

const surveyData: Ref<any[]> = ref([])
const selectedEvent: Ref<string> = ref('')


const props = defineProps<{
    'respondentData': any,
    'metadata': any[],
    'project': any,
    'record': string
}>()

const emit = defineEmits(['viewEventResponses'])

const eventfn = (e: string) => {
    surveyData.value = props.respondentData[e]
    selectedEvent.value = e
}

const back = () => {
    window.history.back();
}

</script>
<template>
    <AppLayout title="Respondent Data">
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
                    <Link class="btn-indigo" href="#" @click="back">
                    Back
                    </Link>
                </div>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-orange-500">
                    {{ record }}
                </div>
            </div>
        </template>
        <!-- <pre>
            {{ respondentData }}
            {{ metadata }}

        </pre> -->
        <div class="sm:px-6 lg:px-8">
            <div class="relative py-12 ">
                <div class="w-1/5 overflow-y-auto h-[900px]">
                    <div class="">
                            <h2 class=" text-2xl font-semibold leading-tight text-green-500 pb-2.5 border-b">
                                Survey Events
                            </h2>
                    </div>
                    <div class="py-2.5 " v-for="event in Object.keys(respondentData)">
                        <div class="text-center text-xl font-bold bg-white hover:bg-teal-400 py-2.5 rounded-lg cursor-pointer"
                            @click="eventfn(event)" v-if="event != selectedEvent">
                            {{ event }}
                        </div>
                        <div class="text-center text-xl font-bold bg-teal-400  py-2.5 rounded-lg cursor-pointer" v-else>
                            {{ event }}
                        </div>
                    </div>
                </div>
                <div class="absolute inset-y-0 right-0 w-3/4 py-20">
                    <div class="max-w-full mx-auto">
                        <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                            <SurveyEvents :event-data="surveyData" :metadata="metadata"
                                :selected-event="selectedEvent" />
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>