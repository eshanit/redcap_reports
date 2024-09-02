<script setup lang='ts'>
import { defineModel } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
});

// Use defineModel to create a model binding
const modelValue = defineModel('checked');
</script>

<template>
    <input
        v-model="modelValue"
        type="checkbox"
        :value="value"
        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
    >
</template>