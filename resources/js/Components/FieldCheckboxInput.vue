<script setup>
import transformSelect from '@/Utilities/transformSelect';
import { ref, onMounted, nextTick, reactive, watch } from 'vue';

const props = defineProps({
  modelValue: Array,
  options: {
    type: Array,
    required: true
  },
  name: {
    type: String,
    required: true
  },
  fieldNameMetadata: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue']);

const checkboxGroup = ref(null);

onMounted(async () => {
  await nextTick();
  if (checkboxGroup.value && checkboxGroup.value.hasAttribute('autofocus')) {
    checkboxGroup.value.focus();
  }
});

const isEqual = (obj1, obj2) => {
  return obj1.name === obj2.name && obj1.event_id === obj2.event_id;
};

const updateSelection = (value) => {
  const newValue = [...props.modelValue];
  const index = newValue.findIndex(item => isEqual(item, value));
  if (index === -1) {
    newValue.push(value);
  } else {
    newValue.splice(index, 1);
  }
  emit('update:modelValue', newValue);
};

const updateQuery = (query, value) => {

  const newValue = [...props.modelValue];
  const selectedEvent = newValue.find(item => item.event_id === value.event_id);

  console.log('newValue',newValue)
  console.log('query',query)
  console.log('value',value)
  
  if (selectedEvent) {
    selectedEvent.query = query;
  } else {
    newValue.push({ ...value, query });
  }
  emit('update:modelValue', newValue);
};

/*
const updateQuery = (query, event) => {
const newValue = [...props.modelValue];
const selectedEvent = newValue.find(item => item.event_id === event.event_id);
if (selectedEvent) {
selectedEvent.query = query;
}
emit('update:modelValue', newValue);
};
*/

const selectedCheckboxes = reactive({});

watch(props.modelValue, (newValue) => {
  newValue.forEach(item => {
    selectedCheckboxes[item.event_id] = true;
  });
}, { immediate: true });
</script>

<template>
  <div ref="checkboxGroup" class="flex flex-col">
    <div class="grid grid-cols-1 gap-5 pb-5">
      <div class="flex items-center mb-2 cursor-pointer" v-for="option in options" :key="option.event_id">
        <label :for="option.event_id" class="flex items-center">
          <input type="checkbox" :name="name" :value="{
            name: option.descrip,
            event_id: option.event_id,
            query: []
          }" :id="option.event_id" :checked="modelValue.some(item => isEqual(item, {
            name: option.descrip,
            event_id: option.event_id,
            query: []
          }))" @change="updateSelection({
            name: option.descrip,
            event_id: option.event_id,
            query: []
          })" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
          <span class="ml-2">{{ option.descrip }}</span>
        </label>
        <div v-if="modelValue.some(item => isEqual(item, {
          name: option.descrip,
          event_id: option.event_id,
          query: []
        }))" class="ml-4">
       <div id="responses">
            <div class="py-5 italic text-teal-500 font-extralight">
              If you want records for all responses on this item, do not select any options below, just ignore.
            </div>
            <div class="px-1.5">
              <div ref="checkboxGroup" class="flex flex-col">
                <div class="grid grid-cols-3 gap-5 pb-5">
                  <label v-for="responseOption in transformSelect(fieldNameMetadata.element_enum)" :key="responseOption.value"
                    :for="responseOption.value" class="flex items-center mb-2 cursor-pointer">
                    <input type="checkbox" name="fieldValues" :value="responseOption.value" :id="responseOption.value"
                      :checked="modelValue.some(item => item.event_id === responseOption.event_id && item.query?.includes(responseOption.value))"
                      @change="updateQuery(
                        $event.target.checked
                          ? [...(modelValue.find(item => item.event_id === option.event_id)?.query || []), responseOption.value]
                          : (modelValue.find(item => item.event_id === option.event_id)?.query || []).filter(q => q !== responseOption.value),
                        { name: option.descrip, event_id: option.event_id }
                      )" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
                    <span class="ml-2" v-html="responseOption.name"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>