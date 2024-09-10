<!-- CustomButton.vue -->
<script setup lang="ts">
const props = defineProps<{
    recordId: string
}>()
const viewRecord = (record: string) => {
    alert(`Viewing record: ${record}`);
};
</script>
<template>
    <button class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-600 border border-transparent rounded-md hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" @click="viewRecord( recordId)">
        record
    </button>
</template>

