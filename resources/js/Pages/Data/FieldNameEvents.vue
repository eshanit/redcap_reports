<script setup lang="ts">
import { Ref, ref, watch } from 'vue';
import FieldCheckboxInput from '@/Components/FieldCheckboxInput.vue';
import FilterQueryDefinitionsV2 from '@/Components/Redcap/FilterQueryDefinitionsV2.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps<{
    projectId: number,
    fields: Array<string>,
    metadataByField: any

}>();



const events = ref<Record<string, any[]>>({}); // Store events for each field
const selectedEvents = ref<Array<any>>([]); // Store selected events for each field

/***-  */
props.fields.forEach((selectedField) => {
    selectedEvents.value.push({
        [selectedField]: []
    });
});


const getEventsForField = async (fieldName: string) => {
    try {
        const response = await fetch(`fieldname/${fieldName}/events`);
        const data = await response.json();
        events.value[fieldName] = data.fieldEvents || []; // Store events for the specific field
    } catch (error) {
        console.log('Error fetching events:', error);
        events.value[fieldName] = []; // Return an empty array in case of an error
    }
};

// Watch for changes in fields and fetch events accordingly
watch(() => props.fields, async (newFields) => {
    events.value = {}; // Reset events
    await Promise.all(newFields.map(fieldName => getEventsForField(fieldName)));

    /** - **/
    selectedEvents.value = []

    newFields.forEach((selectedField) => {
        selectedEvents.value.push({
            [selectedField]: []
        });
    });


}, { immediate: true }); // Fetch immediately on mount with initial fields
</script>

<template>
    <div v-if="fields.length">
        <div class="p-5 " v-for="(fieldName, i) in fields" :key="i">
            <div class="p-5 rounded-lg bg-zinc-100">
                <div class="py-5 text-lg font-bold text-green-500">{{ fieldName }}</div>
                <!-- Render the events for each field -->
                <div v-if="events[fieldName]?.length">
                    {{ metadataByField[fieldName] }}
                    <div class="">
                        <div class="px-1.5">
                            <FieldCheckboxInput name="fieldName" v-model="selectedEvents[i][fieldName]"
                                :options="events[fieldName]" :field-name-metadata="metadataByField[fieldName][0]" />
                        </div>

                    </div>
                </div>
                <div v-else>
                    No events found for this field.
                </div>
            </div>
        </div>
    </div>
    <div>
        <!-- {{ selectedEvents }} -->
        <FilterQueryDefinitionsV2 :selected-data="selectedEvents" />
    </div>

</template>