<script setup lang="ts">
import { ref, watch } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps<{
    projectId: number,
    fields: Array<string>
}>();

const events = ref<Record<string, any[]>>({}); // Store events for each field
const selectedEvents = ref<Record<string, Set<number>>>({}); // Store selected events

const getEventsForField = async (fieldName: string) => {
    try {
        const response = await fetch(`fieldname/${fieldName}/events`);
        const data = await response.json();
        events.value[fieldName] = data.fieldEvents || []; // Store events for the specific field
        selectedEvents.value[fieldName] = new Set(); // Initialize selected events for this field
    } catch (error) {
        console.log('Error fetching events:', error);
        events.value[fieldName] = []; // Return an empty array in case of an error
    }
};

// Watch for changes in fields and fetch events accordingly
watch(() => props.fields, async (newFields) => {
    events.value = {}; // Reset events
    await Promise.all(newFields.map(fieldName => getEventsForField(fieldName)));
}, { immediate: true }); // Fetch immediately on mount with initial fields

const toggleEventSelection = (fieldName: string, eventId: number) => {
    if (selectedEvents.value[fieldName].has(eventId)) {
        selectedEvents.value[fieldName].delete(eventId); // Uncheck if already selected
    } else {
        selectedEvents.value[fieldName].add(eventId); // Check if not selected
    }
};
</script>

<template>
    <div v-if="fields.length">
        <pre>
                {{ selectedEvents }}
        </pre>
        <div class="p-5" v-for="fieldName in fields" :key="fieldName">
            <div class="p-5 rounded-lg bg-zinc-100">
                <div class="py-5 text-lg font-bold text-green-500">{{ fieldName }}</div>
                <!-- Render the events for each field -->
                <div v-if="events[fieldName]?.length">
                    <div class="">
                        <div class="px-1.5">
                            <div class="grid grid-cols-3 gap-5">
                                <div v-for="event in events[fieldName]" :key="event.id">
                                    <div class="flex gap-5" v-if="event">
                                        <div>
                                            {{ event.name }}
                                        </div>
                                        <div>
                                            <Checkbox :v-model="selectedEvents[fieldName].has(event.id)"
                                                @change="toggleEventSelection(fieldName, event.id)" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    No events found for this field.
                </div>
            </div>
        </div>
    </div>
</template>