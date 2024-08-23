<script setup>
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
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

onMounted(async () => {
  await nextTick();
  if (checkboxGroup.value && checkboxGroup.value.hasAttribute('autofocus')) {
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
    <div class="grid grid-cols-3 gap-5 pb-5">
      <label v-for="option in options" :key="option.value" :for="option.value"
        class="flex items-center mb-2 cursor-pointer">
        <input type="checkbox" :name="name" :value="option.value" :id="option.value"
          :checked="modelValue.includes(option.value)" @change="updateSelection(option.value)"
          class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
        <span class="ml-2" v-html="option.name"></span>
      </label>
    </div>
  </div>
</template>
