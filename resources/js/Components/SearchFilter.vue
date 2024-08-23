<template>
  <div class="flex items-center">
    <div class="flex w-full bg-white rounded-lg shadow">
      <!-- <dropdown :auto-close="false" class="px-4 border-r rounded-l focus:z-10 hover:bg-gray-100 focus:border-white focus:ring md:px-6" placement="bottom-start">
        <template #default>
          <div class="flex items-baseline">
            <span class="hidden text-gray-700 md:inline">Filter</span>
            <svg class="w-2 h-2 fill-gray-700 md:ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 961.243 599.998">
              <path d="M239.998 239.999L0 0h961.243L721.246 240c-131.999 132-240.28 240-240.624 239.999-.345-.001-108.625-108.001-240.624-240z" />
            </svg>
          </div>
        </template>
        <template #dropdown>
          <div class="w-screen px-4 py-6 mt-2 bg-white rounded shadow-xl" :style="{ maxWidth: `${maxWidth}px` }">
            <slot />
          </div>
        </template>
      </dropdown> -->
      <input class="relative w-full px-6 py-3 rounded-lg focus:shadow-outline" autocomplete="off" type="text" name="search" :placeholder="search" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" />
    </div>
    <button class="ml-3 text-sm text-gray-500 hover:text-gray-700 focus:text-indigo-500" type="button" @click="$emit('reset')">Reset</button>
  </div>
</template>

<script>
import Dropdown from '@/Components/Dropdown.vue'

export default {
  components: {
    Dropdown,
  },
  props: {
    modelValue: String,
    maxWidth: {
      type: Number,
      default: 300,
    },
    search: String,
  },
  emits: ['update:modelValue', 'reset'],
}
</script>
