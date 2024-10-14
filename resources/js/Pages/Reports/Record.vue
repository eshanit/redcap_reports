<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SurveyEvents from '@/Components/Redcap/SurveyEvents.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue';
import { Ref, ref } from 'vue';

const surveyData: Ref<any> = ref([]);
const selectedEvent: Ref<string | null> = ref(null);
const selectedInstances: Ref<any> = ref([]);
const selectedInstance: Ref<string | null> = ref(null);
const selectedVisits: Ref<any> = ref(null);
const selectedVisit: Ref<string | null> = ref(null);

const props = defineProps<{
    respondentData: any,
    metadata: any[],
    project: any,
    record: string,
}>();

const emit = defineEmits(['viewEventResponses']);

const eventfn = (e: string) => {
    surveyData.value = props.respondentData[e];
    selectedEvent.value = selectedEvent.value === e ? null : e; // Toggle selection
};

const instanceSelect = (e: string) => {
    selectedInstances.value = surveyData.value[e];
    selectedInstance.value = selectedInstance.value === e ? null : e; // Toggle selection
}

const visitSelect = (e: string) => {
    selectedVisits.value = selectedInstances.value[e];
    selectedVisit.value = selectedVisit.value === e ? null : e;
}

const back = () => {
    window.history.back();
};
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
                <div class="font-thin text-green-500">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" href="#" @click="back">
                    Back
                    </Link>
                </div>
                <div class="font-thin text-green-500">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-orange-500">
                    {{ record }}
                </div>
            </div>
        </template>

        <div class="sm:px-6 lg:px-8">
            <div class="relative py-12">
                <div class="w-1/5 overflow-y-auto h-[900px]">
                    <h2 class="text-2xl font-semibold leading-tight text-green-500 pb-2.5 border-b">
                        Survey Events
                    </h2>
                    <div class="py-2.5" v-for="event in Object.keys(respondentData)" :key="event">
                        <div class="text-center text-xl font-bold bg-white hover:bg-teal-500 transition-colors duration-300 py-2.5 rounded-lg cursor-pointer shadow-md"
                            @click="eventfn(event)" v-if="event != selectedEvent">
                            {{ event }}
                        </div>
                        <div class="text-center text-xl font-bold bg-teal-400  py-2.5 rounded-lg cursor-pointer" v-else>
                            {{ event }}
                        </div>
                        <transition name="fade">
                            <div v-if="selectedEvent === event"
                                class="p-4 mt-2 transition-all duration-300 ease-in-out transform bg-gray-200 rounded-lg shadow-lg ">
                                <div v-for="instrument in Object.keys(surveyData)" :key="instrument"
                                    @click="instanceSelect(instrument)">
                                    <div class="flex gap-1 text-lg font-semibold cursor-pointer hover:bg-orange-200 p-2.5 rounded-lg"
                                        v-if="instrument != selectedInstance">
                                        <div class="font-thin text-green-500">
                                            <ChevronRightIcon :size="25" />
                                        </div>
                                        <div> {{ instrument }}</div>
                                    </div>
                                    <div class="flex gap-1 text-lg font-semibold cursor-pointer bg-orange-200 p-2.5 rounded-lg"
                                        v-else>
                                        <div class="font-thin text-green-500">
                                            <ChevronRightIcon :size="25" />
                                        </div>
                                        <div> {{ instrument }}</div>
                                    </div>
                                    <!--Inner Transition for Visit Instances-->
                                    <transition name="fade">
                                        <div v-if="selectedInstance === instrument"
                                            class="p-4 mt-2 transition-all duration-300 ease-in-out transform bg-gray-200 rounded-lg shadow-lg ">
                                            <div v-for=" visit in Object.keys(selectedInstances)" :key="visit"
                                            @click="visitSelect(visit)"
                                            >
                                                <div class="flex gap-1 text-lg font-semibold cursor-pointer hover:bg-sky-200 p-2.5 rounded-lg"
                                                    v-if="visit != selectedVisit">
                                                    <div class="font-thin text-orange-500">
                                                        <ChevronRightIcon :size="25" />
                                                    </div>
                                                    <div> Visit - {{ visit }}</div>
                                                </div>
                                                <div class="flex gap-1 text-lg font-semibold cursor-pointer bg-sky-200 p-2.5 rounded-lg"
                                                    v-else>
                                                    <div class="font-thin text-orange-500">
                                                        <ChevronRightIcon :size="25" />
                                                    </div>
                                                    <div> Visit - {{ visit }}</div>
                                                </div>
                                            </div>

                                        </div>
                                    </transition>

                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
                <div class="absolute inset-y-0 right-0 w-3/4 py-20">
                    <div class="max-w-full mx-auto">
                        <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                            <SurveyEvents :event-data="selectedVisits" :metadata="metadata"
                                :selected-event="selectedEvent" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
    /* Added transform for smooth effect */
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
    /* Slide effect */
}
</style>