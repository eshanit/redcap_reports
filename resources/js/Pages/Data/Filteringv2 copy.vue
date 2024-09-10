<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CheckboxInput from '@/Components/CheckboxInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import { Ref, ref } from 'vue';
import FieldNameEvents from './FieldNameEvents_V2.vue'

const showDropdown: Ref<Boolean> = ref(false);

const props = defineProps<{
    'project': any,
    'metadata': any,
    'metadataByField': any,

}>()

const selected_fields = ref([])

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

            <div class="max-w-full mx-auto sm:px-6 lg:px-8">

                <div class="flex gap-5">
                    <div class="w-1/4 pt-5 overflow-hidden bg-white shadow-xl sm:rounded-lg">
                        <div class="">
                            <div class="p-20 ">
                                <div class="pb-5 text-xl font-semibold text-green-500 border-b">
                                    1. Select/Check all the fields required
                                </div>
                                <div class="p-5 overflow-x-auto h-[1000px]">
                                    <CheckboxInput v-model="selected_fields" :options="metadata" name="example" />
                                </div>
                                <p class="mt-4">Number of selected fields: <span class="text-orange-500">
                                        {{ selected_fields }}</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="w-3/4 pt-5 overflow-hidden bg-white shadow-xl sm:rounded-lg">
                        <div class="p-20 ">

                            <div class="pb-5 text-xl font-semibold text-orange-500 border-b">
                                2. Select the Events for the field names
                            </div>
                            <!-- {{ metadataByField }} -->
                            <div class="p-5 overflow-x-auto h-[1000px]">
                                <FieldNameEvents 
                                    :fields="selected_fields" 
                                    :project-id="project.project_id"
                                    :metadata-by-field="metadataByField"
                                    />
                            </div>
                        </div>
                    </div>

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