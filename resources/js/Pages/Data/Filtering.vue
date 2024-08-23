<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectFilters from '@/Components/Redcap/SelectFilters.vue';
import DataFilters from '@/Components/Redcap/DataFilters.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import { Ref, ref } from 'vue';

const showDropdown: Ref<Boolean> = ref(false);

const props = defineProps<{
    'project': any,
    'fieldEvents':any,
    'metadata': any,
    'metadataByField': any,
}>()

</script>
<template>
    <AppLayout title="field Metadta">
        <template #header>
            <div class="flex gap-1.5">
                <h2 class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/projects/${project.project_id}`">
                    {{ project.app_title }}    
                    </Link>
                </h2>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl text-orange-500">
                    Filtering
                </div>
            </div>
        </template>
        <div class="py-12">
            <!-- <pre>
                {{ project }}
                {{ metadata }}
            </pre> -->

            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
               
                    <div class="">
                        <DataFilters :metadata="metadata" :metadata-by-field="metadataByField" :project="project" :field-events="fieldEvents"  />
                    </div>
                
                <div class="flex justify-end py-10" @click="showDropdown = true">
                    <PrimaryButton>
                        Next
                    </PrimaryButton>
                </div>
            </div>

        </div>
    </AppLayout>
</template>