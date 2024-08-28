<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useStorage, useDropZone } from '@vueuse/core';
import Draggable from 'vuedraggable/src/vuedraggable';
import { computed, ref } from 'vue';
import formatQueryObjectIntoArray from '@/Utilities/formatQueryObjectIntoArray';
import handleQueryArrays from '@/Utilities/handleQueryArrays';

interface QueryItem {
  field_name: string;
  event_name: string;
  event_id: number;
  values: Array<{ min: string; max: string } | string>;
  operator: string;
}

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

//draggable 
const droppedItems = ref<QueryItem[]>([]);

const onDragEnd = (event: any) => {
  // Handle drag end event if needed
};

const removeDroppedItem = (item: QueryItem) => {
  droppedItems.value = droppedItems.value.filter(i => i !== item);
};

//

const postSelectedObject = (selectedObject: any) => {

  router.get('process/filtered/query', selectedObject)

}
</script>

<template>
  <div class="p-4">
    <pre>
      {{ finalQueryArray }}
    </pre>

    <div v-if="finalQueryArray.length > 0">
      <!-- Header -->
      <div class="grid grid-cols-2 py-2.5 font-bold">
        <div>Condition</div>
        <div>View</div>
      </div>

      <!-- Draggable list -->
      <draggable v-model="finalQueryArray" group="query" @end="onDragEnd" class="space-y-2">
        <template #item="{ element }">
          <div class="grid grid-cols-2 py-2.5 border-t border-gray-300">
            <div class="flex gap-2">
              <span class="italic font-bold text-orange-500">{{ element.field_name }}</span>
              <span>values</span>
              <span class="text-blue-500">{{ element.operator }}</span>
              <span class="italic text-orange-500">
                {{ element.operator === 'BETWEEN' ? element.values[0].min : handleQueryArrays(element.values) }}
              </span>
              <span class="text-blue-500" v-if="element.operator === 'BETWEEN'">AND</span>
              <span class="italic text-orange-500" v-if="element.operator === 'BETWEEN'">{{ element.values[0].max }}</span>
              <span>for event</span>
              <span class="italic text-orange-500">{{ element.event_name }}</span>
            </div>
            <div>
              <PrimaryButton @click="postSelectedObject(element)">View</PrimaryButton>
            </div>
          </div>
        </template>
      </draggable>

      <!-- Drop zone -->
        <pre>
          {{ droppedItems }}
        </pre>
        <div>
          
        </div>
      <draggable v-model="droppedItems" group="query" class="space-y-2">
        <template #item="{ element }">
          <div class="flex items-center justify-between py-2 border-b border-gray-300">
            <span>{{ element.field_name }} - {{ element.event_name }}</span>
            <PrimaryButton @click="removeDroppedItem(element)">Remove</PrimaryButton>
          </div>
        </template>
      </draggable>
    </div>
  </div>
</template>
