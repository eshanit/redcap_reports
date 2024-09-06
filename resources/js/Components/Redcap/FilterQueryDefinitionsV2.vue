<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useDropZone } from '@vueuse/core';
import Draggable from 'vuedraggable/src/vuedraggable';
import { computed, ref } from 'vue';
import transformQueryArray from '@/Utilities/transformQueryArray';
import GreenBadgeTrash from '@/Components/GreenBadgeTrash.vue';
import handleQueryArrays from '@/Utilities/handleQueryArrays';
import WarningOutlineButton from '@/Components/WarningOutlineButton.vue';
import SetAllIcon from 'vue-material-design-icons/SetAll.vue'
import SetCenterIcon from 'vue-material-design-icons/SetCenter.vue'

const props = defineProps<{
  selectedData: any
}>()

interface QueryItem {
  field_name: string;
  event_name: string;
  event_id: number;
  values: Array<{ min: string; max: string } | string>;
  operator: string;
}

const queryData = ref<QueryItem[]>([])


const finalQueryArray = computed(() => {
  return transformQueryArray(props.selectedData)
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




///for unions

const postUnionArray = (selectedArray: any, type: string) => {
  router.get(`union/${type}/filtered/query`, selectedArray)
}


//
const postIntesectionArray = (selectedArray: any, type: string) => {
  router.get(`intersection/filtered/${type}/query`, selectedArray)
}

</script>

<template>
  <div class="p-4">
    <!-- <pre>
      hello
      {{ selectedData }}
      {{ finalQueryArray }}
    </pre> -->

    <div v-if="finalQueryArray.length > 0">
      <div class="pb-5 border-b">
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
      </div>

      <!-- Drop zone -->
      <!-- <pre>
  {{ droppedIntersectionItems }}
</pre> -->
      <div class="py-5 ">
        <div class="font-semibold">
          Drop Zones:
        </div>
      </div>
      <div class="grid grid-cols-1 gap-5 pt-5">
        <div id="union" class="p-5 bg-white border rounded-lg shadow-xl ">
          <div class="flex gap-5 font-bold text-orange-500 ">
            UNION DROPZONE <span class="">
              <SetAllIcon size="35" />
            </span>
          </div>
          <div class="text-sm italic font-thin text-gray-500 pb-2.5">
            Drag conditions which you need union between them.
          </div>
          <div>
           
              <draggable v-model="droppedUnionItems" group="query" class="grid grid-cols-3 gap-5">
                  <template #item="{ element }">
                    <GreenBadgeTrash :text="element.index" @click="removeDroppedUnionItem(element)" />
                </template>
              </draggable>
          
          </div>
          <div class="flex justify-end pt-2.5" v-if="droppedUnionItems.length > 0">
            <div>
              <WarningOutlineButton @click="postUnionArray(droppedUnionItems, 'union')">
                Get Data
              </WarningOutlineButton>
            </div>
          </div>
        </div>

        <div id="union" class="p-5 bg-white border rounded-lg shadow-xl ">
          <div class="flex gap-5 font-bold text-orange-500">
            INTERSECTION DROPZONE <SetCenterIcon size="35" />
          </div>
          <div class="text-sm italic font-thin text-gray-500 pb-2.5">
            Drag conditions which you need intersection between them.
          </div>
          <div>
            <draggable v-model="droppedIntersectionItems" group="query" class="grid grid-cols-3 gap-5">
              <template #item="{ element }">
                <GreenBadgeTrash :text="element.index" @click="removeDroppedIntersectionItem(element)" />
              </template>
            </draggable>
          </div>
          <div class="flex justify-end pt-2.5"  v-if="droppedIntersectionItems.length > 0">
            <div class="flex gap-5">
              <WarningOutlineButton @click="postIntesectionArray(droppedIntersectionItems, 'records')">
                Get Records
              </WarningOutlineButton>
              <WarningOutlineButton @click="postIntesectionArray(droppedIntersectionItems, 'full')">
                Get Full Data
              </WarningOutlineButton>
            </div>
          </div>
        </div>

      </div>


      <!-- General DropZone -->

    </div>
  </div>
</template>
