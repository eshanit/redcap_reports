<script setup>
import { ref, onMounted, nextTick, reactive, watch } from 'vue';
import ProcessSelectDropDowns from '@/Components/Redcap/ProcessSelectDropDowns.vue';

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

const updateQuery = (query, event) => {
  const selectedEvent = props.modelValue.find(item => item.event_id === event.event_id);
  if (selectedEvent) {
    selectedEvent.query = query;
  }
};

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
            event_id: option.event_id
          }" :id="option.event_id" :checked="modelValue.some(item => isEqual(item, {
            name: option.descrip,
            event_id: option.event_id
          }))" @change="updateSelection({
            name: option.descrip,
            event_id: option.event_id
          })" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
          <span class="ml-2">{{ option.descrip }}</span>
        </label>
        <div v-if="modelValue.some(item => isEqual(item, {
          name: option.descrip,
          event_id: option.event_id
        }))" class="ml-4">
          <ProcessSelectDropDowns :selected-field-metadata="fieldNameMetadata" :event="option" @update:query="query => updateQuery(query, { event_id: option.event_id })" />
        </div>
      </div>
    </div>
  </div>
</template>
