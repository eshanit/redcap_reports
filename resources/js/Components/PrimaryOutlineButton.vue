<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button 
        :type="type" 
        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest uppercase transition duration-150 ease-in-out bg-transparent bg-white border rounded-md shadow-sm text-sky-500 border-sky-500 border--300 hover:bg-sky-500 hover:border-transparent hover:text-white focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 disabled:opacity-25">
        <slot />
    </button>
</template>
<!-- <button class="px-4 py-2 font-semibold text-blue-700 bg-transparent border rounded border-sky-500 hover:bg-blue-500 hover:text-white hover:border-transparent">
    Button
  </button> -->