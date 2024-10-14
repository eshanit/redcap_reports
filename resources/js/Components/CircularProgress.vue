<!-- CircularSpinner.vue -->
<script setup lang="ts">
const props = defineProps({
    isLoading: {
        type: Boolean,
        required: true,
    },
});
</script>

<template>
    <div class="spinner" v-if="isLoading">
      <div class="circle"></div>
    </div>
  </template>
  
  <style scoped>
  .spinner {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100px; /* Adjust as needed */
  }
  
  .circle {
    width: 50px;
    height: 50px;
    border: 5px solid #29d;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  </style>