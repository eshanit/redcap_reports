<script setup lang="ts">
import FieldCheckboxInput from '@/Components/FieldCheckboxInput.vue';
import { useStorage } from '@vueuse/core';
import { reactive, ref, Ref, watch } from 'vue';

const props = defineProps<{
  selectedDataFields: any[]
  fieldEvents: any,
  metadataByField: any,
}>();

const eventArr: Ref<Array<any>> = ref([]);

props.selectedDataFields.forEach((selectedField) => {
  eventArr.value.push({
    [selectedField]: []
  });
});

const init = {
  selectedEvents: eventArr.value
};

const state = reactive(init);

const localStorageState: any = useStorage('selectedEvents', state);

watch(() => props.selectedDataFields, (newFields) => {
  eventArr.value = [];
  newFields.forEach((selectedField) => {
    eventArr.value.push({
      [selectedField]: []
    });
  });
  state.selectedEvents = eventArr.value;
}, { immediate: true });

</script>

<template>
  <div v-if="selectedDataFields">
    <div class="p-20">
      <div class="pb-5 text-xl font-semibold text-orange-500 border-b">
        2. Select the Events for the field names
      </div>
      <div v-for="(selectedField, i) in selectedDataFields" :key="i">
        <div class="py-10 border-b">
          <div class="py-1 text-lg font-bold">{{ selectedField }}</div>
          <div class="bg-zinc-100 rounded-lg py-2.5">
            <div class="px-1.5">
              <FieldCheckboxInput v-model="localStorageState.selectedEvents[i][selectedField]"
                :options="fieldEvents[selectedField]" name="example" :field-name-metadata="metadataByField[selectedField][0]" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
