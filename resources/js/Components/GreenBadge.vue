<script lang="ts" setup>
import { computed } from 'vue';

// Define props with types
interface Props {
    text: any;
}

const props = defineProps<Props>();

// Compute the classes based on the color prop

</script>

<template>
    <span class="bg-green-100 hover:bg-green-300 text-green-800 text-sm font-bold  me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-green-400 border border-green-400 cursor-pointer">{{ props.text }}</span>
</template>
