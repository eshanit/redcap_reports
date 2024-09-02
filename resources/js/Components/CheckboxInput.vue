<!-- BaseCheckboxGroup.vue -->
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

const updateSelection = (value) => {
  const newValue = [...props.modelValue];
  const index = newValue.indexOf(value);
  if (index === -1) {
    newValue.push(value);
  } else {
    newValue.splice(index, 1);
  }
  emit('update:modelValue', newValue);
};

defineExpose({ focus: () => checkboxGroup.value.focus() });
</script>

<template>

  <div ref="checkboxGroup" class="flex flex-col">
    <!-- <div v-for="option in options" :key="option.field_name" :for="option.field_name">
      {{ option.field_name }}
    </div> -->
  
    <div class="grid grid-cols-1 gap-5 pb-5">
      <label v-for="option in options" :key="option.field_name" :for="option.field_name"
        class="flex items-center mb-2 cursor-pointer">
        <input type="checkbox" :name="name" :value="option.field_name" :id="option.field_name"
          :checked="modelValue.includes(option.field_name)" @change="updateSelection(option.field_name)"
          class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
        <span class="ml-2">{{ option.field_name }}</span>
      </label>
    </div>
  </div>

</template>
