<script lang="ts" setup>
import { computed } from 'vue';

// Define props with types
interface Props {
    text: string;
    color: string;
}

const props = defineProps<Props>();

// Compute the classes based on the color prop

</script>

<template>
    <span class="bg-orange-100 hover:bg-orange-300 text-orange-800 text-sm font-bold  me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-orange-400 border border-orange-400 cursor-pointer">{{ props.text }}</span>
</template>
