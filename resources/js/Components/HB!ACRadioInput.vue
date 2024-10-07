<!-- BaseRadioButtonGroup.vue -->
<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: String,
  options: {
    type: Array,
    required: true,
  },
  name: {
    type: String,
    required: true,
  }
});

const emit = defineEmits(['update:modelValue']);

const radioGroup = ref(null);

// No need for onMounted() since we're not focusing the radio group

const handleRadioChange = (selectedValue) => {
  emit('update:modelValue', selectedValue); // Emit the selected value
};
</script>

<template>
<div ref="radioGroup" class="grid grid-cols-4 gap-5 ">

    <label v-for="option in options" :key="option.name" :for="option.name" class="flex items-center py-2 mb-2 cursor-pointer">
      <input
        type="radio"
        :name="name"
        :value="option.name"
        :id="option.value"
        @change="handleRadioChange(option.name)"
        :checked="option.value === modelValue" 
        class="text-blue-600 transition duration-150 ease-in-out form-radio"
      />
      <span class="ml-2">{{ option.name }}</span>
    </label>
  </div>
</template>
