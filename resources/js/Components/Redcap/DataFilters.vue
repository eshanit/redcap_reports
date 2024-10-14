<script setup lang="ts">
import CheckboxInput from '@/Components/CheckboxInput.vue';
import FilterQueryDefinitions from './FilterQueryDefinitions.vue';
import SelectFilters from './SelectFilters.vue';
import { useStorage } from '@vueuse/core'
import { reactive } from 'vue';

defineProps<{
    'project': any,
    'fieldEvents': any,
    'metadata': any,
    'metadataByField': any,
}>()

const init = {
    selected_fields: []
}


const state = reactive(init)

const localStorageState = useStorage('dataFilters', state)


</script>
<template>
    <div class="flex gap-5">
        <!-- <pre>
            {{ metadata }}
        </pre> -->
        <div class="w-1/4 pt-5 overflow-hidden bg-white shadow-xl sm:rounded-lg">
            <div class="">
                <div class="p-20 ">
                    <div class="pb-5 text-xl font-semibold text-green-500 border-b">
                        1. Select/Check all the fields required
                    </div>
                    <div class="p-5 overflow-x-auto h-[1000px]">
                        <CheckboxInput v-model="localStorageState.selected_fields" :options="metadata" name="example" />
                    </div>
                    <p class="mt-4">Number of selected fields: <span class="text-orange-500">
                            {{ localStorageState.selected_fields.length }}</span></p>
                </div>
            </div>
        </div>
        <div class="w-3/4 pt-5 overflow-hidden bg-white shadow-xl sm:rounded-lg">
            <div class="p-20 ">

                <div class="pb-5 text-xl font-semibold text-orange-500 border-b">
                    2. Select the Events for the field names
                </div>
                <div class="p-5 overflow-x-auto h-[1000px]">
                    hi
                    <SelectFilters :selected-data-fields="localStorageState.selected_fields" :field-events="fieldEvents"
                        :metadata-by-field="metadataByField" />
                </div>
            </div>
        </div>
        
    </div>
    <div>
        <FilterQueryDefinitions />
    </div>


</template>