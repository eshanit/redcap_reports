<template>
    <div class="flex justify-between">
        <button
            v-if="pagination.current_page > 1"
            @click="goToPage(pagination.current_page - 1)"
            class="btn"
        >
            Previous
        </button>

        <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>

        <button
            v-if="pagination.current_page < pagination.last_page"
            @click="goToPage(pagination.current_page + 1)"
            class="btn"
        >
            Next
        </button>
    </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    pagination: {
        current_page: number;
        last_page: number;
    };
}>();

const goToPage = (page: number) => {
    const { url } = usePage();
    const newUrl = new URL(url.value);
    newUrl.searchParams.set('page', page.toString());
    window.location.href = newUrl.toString();
};
</script>

<style scoped>
.btn {
    padding: 0.5em 1em;
    background-color: #4a5568; /* Example color */
    color: white;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background-color: #2d3748; /* Darker shade on hover */
}
</style>