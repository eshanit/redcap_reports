<script setup>
import { onMounted, ref, defineProps, defineEmits, defineExpose } from 'vue';

const props = defineProps({
    modelValue: String,
});

const emit = defineEmits(['update:modelValue']);
const select = ref(null);

onMounted(() => {
    if (select.value.hasAttribute('autofocus')) {
        select.value.focus();
    }
});

defineExpose({ focus: () => select.value.focus() });
</script>

<template>
    <select
        ref="select"
        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        :value="modelValue"
        @change="emit('update:modelValue', $event.target.value)"
    >
        <option value="" disabled>Select an option</option>
        <slot name="options" />
    </select>
</template>