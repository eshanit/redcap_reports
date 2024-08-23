<script setup>
import { ref, onMounted } from 'vue';


const props = defineProps({
  modelValue: Array,
  options: {
    type: Array,
    required: true
  },
  name: {
    type: String,
    required: true
  }

});

const emit = defineEmits(['update:modelValue']);

const checkboxGroup = ref(null);

onMounted(() => {
  if (checkboxGroup.value.hasAttribute('autofocus')) {
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

defineExpose({ focus: () => checkboxGroup.value.focus() });


/**
 * 
 * select query
 */

</script>

<template>
  <div ref="checkboxGroup" class="flex flex-col">
    <div class="grid grid-cols-1 gap-5 pb-5">
      <div class="flex gap-5 py-5 " v-for="option in options" :key="option.event_id" :for="option.descrip">
        <label class="flex items-center mb-2 cursor-pointer ">
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
      </div>
    </div>
  </div>
</template>
