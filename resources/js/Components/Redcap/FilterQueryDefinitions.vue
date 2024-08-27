<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useStorage } from '@vueuse/core';
import { computed } from 'vue';
import formatQueryObjectIntoArray from '@/Utilities/formatQueryObjectIntoArray';
import handleQueryArrays from '@/Utilities/handleQueryArrays';

// Create a reactive reference to the localStorage data
const selectedData = useStorage<string | null>('selectedEvents', null);

// Parse the JSON data reactively
const data = computed(() => {
  try {
    return selectedData.value ? JSON.parse(selectedData.value) : null;
  } catch (e) {
    console.error('Failed to parse JSON:', e);
    return null;
  }
});

const finalQueryArray = computed(() => {
  return formatQueryObjectIntoArray(data.value)
})
</script>

<template>
  <div>
    <pre>
      {{ finalQueryArray }}
        </pre>
    <div class="flex gap-2 py-2.5">
      It looks like you need data from the following conditions:
    </div>

    <div class="" v-if="finalQueryArray.length > 0">
      <!-- header-->
      <div class="grid grid-cols-2 py-2.5">
        <div>
          Condition
        </div>
        <div>
          View
        </div>
      </div>
      <!-- body-->
      <div v-for="selectedEvent in finalQueryArray">
        <div class="grid grid-cols-2 py-2.5 border-t">
          <div class="flex gap-2" v-if="selectedEvent.operator === 'BETWEEN'">
            <span class="italic font-bold text-orange-500">
              {{ selectedEvent.field_name }}
            </span> values
            <span class="text-blue-500 ">{{ selectedEvent.operator }}</span>
            <span class="italic text-orange-500">{{ selectedEvent.values[0].min }}</span>
            <span class="text-blue-500 ">AND</span>
            <span class="italic text-orange-500">{{ selectedEvent.values[0].max }}</span>
            <span>
              for event
            </span>
            <span class="italic text-orange-500">
              {{ selectedEvent.event_name }}
            </span>
          </div>
          <div class="flex gap-2" v-if="selectedEvent.operator != 'BETWEEN'">
                    <span class="italic font-bold text-orange-500"> {{ selectedEvent.field_name }}</span> values
                    <span class="text-blue-500 " v-if="selectedEvent.operator != 'OR'" >{{ selectedEvent.operator }}</span>
                    <span class="italic text-orange-500">
                        {{  handleQueryArrays(selectedEvent.values) }}
                    </span>
                    <span>
                        for event
                    </span>
                    <span class="italic text-orange-500">
                        {{  selectedEvent.event_name }}
                    </span>
                </div>
                <div>
                    <PrimaryButton>
                        View
                    </PrimaryButton>
                </div>
        </div>
      </div>


    </div>
  </div>
</template>
