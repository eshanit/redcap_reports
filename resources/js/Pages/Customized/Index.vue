<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'

const props = defineProps<{
    'project': any,
    'customProjectData': any
}>()

const back = () => {
    window.history.back();
}

</script>
<template>
    <AppLayout :title="project.app_title">
        <template #header>
            <div class="flex items-center space-x-2">
                <h2 class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link :href="`/projects/${project.project_id}`">{{ project.app_title }}</Link>
                </h2>
                <ChevronRightIcon class="text-green-500" :size="25" />
                <div class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" href="#" @click="back">
                    Back
                    </Link>
                </div>
                <ChevronRightIcon class="text-green-500" :size="25" />
                <div class="text-xl text-orange-500">Customized Reports Dashboard</div>
            </div>
        </template>

        <div class="sm:px-6 lg:px-8">
            <div class="relative grid grid-cols-4 gap-5 py-12" v-if="customProjectData.length > 0">
                <div class="overflow-hidden bg-white rounded-lg shadow-lg"
                    v-for="customProject in customProjectData ">
                    <!-- <img class="object-cover w-full h-48" src="your-image-url.jpg" alt="Card Image"> -->
                    <div class="p-6">
                        <h2 class="mb-2 text-xl font-semibold text-gray-800">{{ customProject.customization_name }}</h2>
                        <p class="text-gray-600"><span v-html="customProject.description" /></p>
                        <Link :href="`/project/${project.project_id}/custom-pages/${customProject.path}`">
                        <button
                            class="px-4 py-2 mt-4 text-white bg-blue-500 rounded hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-200">Have
                            a go
                        </button>
                        </Link>
                    </div>
                </div>
            </div>
            <div class="relative py-12 " v-else>
                You do not have any customized packages or reports for this project. Please contact the admin if you have any special requests.
            </div>
        </div>
    </AppLayout>
</template>