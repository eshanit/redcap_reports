<script setup lang="ts">
import { Ref, ref, watch } from 'vue';
import FieldCheckboxInput from '@/Components/FieldCheckboxInput.vue';
import CircularProgress from '@/Components/CircularProgress.vue';
import FieldCheckboxInputAllEvents from '@/Components/FieldCheckboxInputAllEvents.vue';
import FilterQueryDefinitionsV2 from '@/Components/Redcap/FilterQueryDefinitionsV2.vue';
import VueSelect from "vue3-select-component";
import hasNonEmptyElements from '@/Utilities/hasNonEmptyElements';

const props = defineProps<{
    projectId: number,
    fields: Array<string>,
    metadataByField: any

}>();


const events = ref<Record<string, any[]>>({}); // Store events for each field
const selectedEvents = ref<Array<any>>([]); // Store selected events for each field
const dataModel = ref<Array<string>>([]); // Store data models for each field
/***-  */
props.fields.forEach((selectedField) => {
    selectedEvents.value.push({
        [selectedField]: []
    });
    dataModel.value.push('events'); // Ini
});

const emit = defineEmits(['selected']);

///

const isLoading = ref(false); // Add loading state

// const getEventsForField = async (fieldName: string) => {
//     try {
//         const response = await fetch(`fieldname/${fieldName}/events`);
//         const data = await response.json();
//         events.value[fieldName] = data.fieldEvents || []; // Store events for the specific field
//     } catch (error) {
//         console.log('Error fetching events:', error);
//         events.value[fieldName] = []; // Return an empty array in case of an error
//     }
// };

const getEventsForField = async (fieldName: string) => {
    isLoading.value = true; // Start loading
    try {
        const response = await fetch(`fieldname/${fieldName}/events`);
        const data = await response.json();
        events.value[fieldName] = data.fieldEvents || []; // Store events for the specific field
    } catch (error) {
        console.log('Error fetching events:', error);
        events.value[fieldName] = []; // Return an empty array in case of an error
    } finally {
        isLoading.value = false; // End loading
    }
};

// Watch for changes in fields and fetch events accordingly
watch(() => props.fields, async (newFields) => {
    events.value = {}; // Reset events
    await Promise.all(newFields.map(fieldName => getEventsForField(fieldName)));

    /** - **/
    selectedEvents.value = newFields.map(fieldName => ({
        [fieldName]: []
    }
));
dataModel.value.push('events'); // Ini

}, { immediate: true }); // Fetch immediately on mount with initial fields

const dataOptions = [
    {
        label: 'All Data',
        value: 'all'
    },
    {
        label: 'Events Based',
        value: 'events'
    }
]



const allData = [
    {
        name: 'All Data',
        event_id: 0
    }
]

//
watch(() => selectedEvents, (newValue) => {
    console.log('selected:', newValue.value);
    emit('selected', hasNonEmptyElements(newValue.value));
}, { deep: true })

</script>
<template>
    <div class="grid grid-cols-2 gap-5">
        <div v-if="fields.length">
            <div class="p-5 " v-for="(fieldName, i) in fields" :key="i">
                <div class="p-5 rounded-lg bg-zinc-100">
                    <div class="flex justify-between">
                        <div class="py-5 text-lg font-bold text-green-500">{{ fieldName }}</div>
                        <div class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <VueSelect v-model="dataModel[i]" :options="dataOptions" placeholder="Select an option" />
                        </div>
                    </div>
                    <div v-if="dataModel[i] === 'events'">
                        <div v-if="isLoading">
                            <CircularProgress :isLoading="isLoading" />
                        </div>
                        <div v-else-if="events[fieldName]?.length">
                            <div class="">
                                <div class="px-1.5">
                                    <FieldCheckboxInput 
                                        :name="fieldName" 
                                        v-model="selectedEvents[i][fieldName]"
                                        :options="events[fieldName]"
                                        :field-name-metadata="metadataByField[fieldName][0]" 
                                    />
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            No events found for this field. If you are sure this field has events, please wait while they load, if not you can select 
                            <span class="text-orange-500">All data</span>
                        </div>
                    </div>
                    <div v-else>
                        <div class="px-1.5">
                            <FieldCheckboxInputAllEvents :name="fieldName" v-model="selectedEvents[i][fieldName]"
                                :options="allData" :field-name-metadata="metadataByField[fieldName][0]" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <FilterQueryDefinitionsV2 :selected-data="selectedEvents" />
        </div>
    </div>
</template>
<style scoped>
/* Add any global styles you need */
</style>