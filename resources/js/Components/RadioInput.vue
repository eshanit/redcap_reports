<!-- BaseRadioButtonGroup.vue -->
<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  modelValue: String,
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

const radioGroup = ref(null);

onMounted(() => {
  if (radioGroup.value.hasAttribute('autofocus')) {
    radioGroup.value.focus();
  }
});

defineExpose({ focus: () => radioGroup.value.focus() });
</script>

<template>
  <div ref="radioGroup" class="flex flex-col">
    <label
      v-for="option in options"
      :key="option.value"
      :for="option.value"
      class="flex items-center mb-2 cursor-pointer"
    >
      <input
        type="radio"
        :name="name"
        :value="option.value"
        :id="option.value"
        @change="$emit('update:modelValue', option.value)"
        class="text-blue-600 transition duration-150 ease-in-out form-radio"
      />
      <span class="ml-2">{{ option.name }}</span>
    </label>
  </div>
</template>


