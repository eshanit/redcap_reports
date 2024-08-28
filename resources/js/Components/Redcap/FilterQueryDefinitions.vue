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

const queryData = ref<QueryItem[]>([])

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
const droppedUnionItems = ref<QueryItem[]>([]);
const droppedIntersectionItems = ref<QueryItem[]>([]);


const onDragEnd = (event: any) => {
  // Handle drag end event if needed
};

const removeDroppedUnionItem = (item: QueryItem) => {
  droppedUnionItems.value = droppedUnionItems.value.filter(i => i !== item);
};

const removeDroppedIntersectionItem = (item: QueryItem) => {
  droppedIntersectionItems.value = droppedIntersectionItems.value.filter(i => i !== item);
};

//dropzone
const dropZoneRef = ref<HTMLElement>();

function onDrop(selectedQuery: QueryItem | null) {
  queryData.value = []
  if (selectedQuery) {
    return {
      field_name: selectedQuery.field_name,
      event_name: selectedQuery.event_name,
      operator: selectedQuery.operator,
      values: handleQueryArrays(selectedQuery.values),
    }
  }
}

const { isOverDropZone: isOverImageDropZone } = useDropZone(dropZoneRef)

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
      <div class="grid grid-cols-6 py-2.5 font-bold">
        <div class="grid col-span-1">Condition</div>
        <div class="grid col-span-4">Query</div>
        <div class="grid col-span-1">View</div>
      </div>

      <!-- Draggable list -->
      <draggable v-model="finalQueryArray" group="query" @end="onDragEnd" class="space-y-2">
        <template #item="{ element }">
          <div class="grid grid-cols-6 py-2.5 border-t border-gray-300 hover:cursor-grabbing">
            <div class="grid col-span-1">
              {{ element.index }}
            </div>
            <div class="grid col-span-4">
              <div class="flex gap-2">
                <span class="italic font-bold text-orange-500">{{ element.field_name }}</span>
                <span>values</span>
                <span class="text-blue-500">{{ element.operator }}</span>
                <span class="italic text-orange-500">
                  {{ element.operator === 'BETWEEN' ? element.values[0].min : handleQueryArrays(element.values) }}
                </span>
                <span class="text-blue-500" v-if="element.operator === 'BETWEEN'">AND</span>
                <span class="italic text-orange-500" v-if="element.operator === 'BETWEEN'">{{ element.values[0].max
                  }}</span>
                <span>for event</span>
                <span class="italic text-orange-500">{{ element.event_name }}</span>
              </div>
            </div>
            <div class="grid col-span-1">
              <div>
                <PrimaryButton @click="postSelectedObject(element)">View</PrimaryButton>
              </div>
            </div>
          </div>
        </template>
      </draggable>

      <!-- Drop zone -->
      <pre>
  {{ droppedUnionItems }}
</pre>
      <div class="grid grid-cols-3 gap-5">
        <div id="union" class="p-5 bg-white rounded-lg">
          <div class="font-bold text-orange-500">
            UNION DROPZONE
          </div>
          <div class="text-sm italic font-thin text-gray-500 pb-2.5">
            Drag conditions which you need union between them.
          </div>
          <div>
            <draggable v-model="droppedUnionItems" group="query" class="space-y-2">
              <template #item="{ element }">
                <div class="flex items-center justify-between py-2 border-t border-gray-300">
                  <span>{{ element.index }}</span>
                  <PrimaryButton @click="removeDroppedUnionItem(element)">Remove</PrimaryButton>
                </div>
              </template>
            </draggable>
          </div>
        </div>

        <div id="union" class="p-5 bg-white rounded-lg">
          <div class="font-bold text-orange-500">
            INTERSECTION DROPZONE
          </div>
          <div class="text-sm italic font-thin text-gray-500 pb-2.5">
            Drag conditions which you need intersection between them.
          </div>
          <div>
            <draggable v-model="droppedIntersectionItems" group="query" class="space-y-2">
              <template #item="{ element }">
                <div class="flex items-center justify-between py-2 border-t border-gray-300">
                  <span>{{ element.index }}</span>
                  <PrimaryButton @click="removeDroppedIntersectionItem(element)">Remove</PrimaryButton>
                </div>
              </template>
            </draggable>
          </div>
        </div>

      </div>


      <!-- General DropZone -->

    </div>
  </div>
</template>
