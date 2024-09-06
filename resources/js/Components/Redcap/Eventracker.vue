<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    projectId: number,
    fieldName: string,
    record: string
}>();

const respondentEventFieldData = ref<any[]>([]); // Make sure this is an array
const metadata = ref<any[]>([]); // Make sure this is an array

const getEventsData = async () => {
    try {
        const response = await fetch(`fieldname/${props.fieldName}`);
        const data = await response.json();

        console.log('Fetched data:', data); // Log the full fetched data

        // Ensure fieldEventsData is an array
        respondentEventFieldData.value = Array.isArray(data.fieldEventsData) ? data.fieldEventsData : [];
        metadata.value = Array.isArray(data.fieldMetadata) ? data.fieldMetadata : [];
    } catch (e) {
        console.log('Error fetching events:', e);
    }
};

// Watch for changes in fieldName and fetch events accordingly
watch(() => props.fieldName, async () => {
    await getEventsData();
}, { immediate: true });

// Group data by event_name
const groupedData = (data: any): any => {
    console.log('Data received for grouping:', data); // Debug log
    if (!Array.isArray(data)) return [];

    const newData = data.reduce((acc, entry) => {
        if (!acc[entry.event_name]) {
            acc[entry.event_name] = { event_name: entry.event_name, values: [] };
        }
        acc[entry.event_name].values.push(entry.value);
        return acc;
    }, {});

    const formattedData = Object.values(newData);
    console.log('Grouped data:', formattedData); // Debug log
    return formattedData;
};

</script>

<template>
    <div class="p-4" v-if="respondentEventFieldData.length > 0">
        <!-- <pre>
            {{ respondentEventFieldData }}
        </pre> -->
        <div class="grid grid-cols-6 gap-10">

            <div class="grid col-span-4">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Event Name</th>
                            <th class="px-4 py-2">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(entry, index) in groupedData(respondentEventFieldData)" :key="index">
                            <td class="px-4 py-2 border">{{ entry.event_name }}</td>
                            <td class="px-4 py-2 border">{{ entry.values.join(', ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="grid col-span-2" >
                
                <div></div>
            
                <div v-html="metadata[0].element_enum"/>
                
            </div>

        </div>

    </div>
    <div v-else>
        It looks like there is no data for this field on any events. However if you really this there is data , then
        please wait a moment whilst the engine fetches the data.
    </div>
</template>