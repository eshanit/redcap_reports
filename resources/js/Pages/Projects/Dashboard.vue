<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import SeachQuery from '@/Components/Redcap/SearchQuery.vue';
import DataFilters from '@/Components/Redcap/DataFilters.vue';

const props = defineProps<{
    'project': any,
    'metadata': any,
}>()



</script>
<template>
    <AppLayout title="Project Data Portal">
        <template #header>
            <div class="flex gap-2.5">
                <h2 class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/projects/${project.project_id}`">
                    {{ project.app_title }}
                    </Link>
                </h2>
                <div>|</div>
                <div>
                    Data Portal
                    <!-- <pre>
                        {{ metadata }}
                    </pre> -->
                </div>
            </div>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="pb-5 ">
                    <SeachQuery :project="project" :metadata="metadata" />
                </div>
                <div class="pt-5 ">
                    <DataFilters :metadata="metadata" />
                </div>

            </div>
        </div>
    </AppLayout>
</template>