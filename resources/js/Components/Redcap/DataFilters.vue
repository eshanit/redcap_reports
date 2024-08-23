<script setup lang="ts">
import CheckboxInput from '@/Components/CheckboxInput.vue';
import SelectFilters from './SelectFilters.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import RadioInput from '@/Components/RadioInput.vue';
import transformSelect from '@/Utilities/transformSelect';
import { useStorage } from '@vueuse/core'
import { reactive } from 'vue';

defineProps<{
    'project': any,
    'fieldEvents':any,
    'metadata': any,
    'metadataByField': any,
}>()

const init = {
    selected_fields: []
}


const state = reactive(init)

const localStorageState = useStorage('dataFilters', state)

const operatorOptions = [
    {
        name: 'Equal to (=)',
        value: '='
    },
    {
        name: 'Greater Than (>)',
        value: '>'
    },
    {
        name: 'Greater Than or Equal to (>)',
        value: '>='
    },
    {
        name: 'Less than (<)',
        value: '<'
    },
    {
        name: 'Less than or equal to (>)',
        value: '<='
    },
    {
        name: 'Is not',
        value: '!='
    },
    {
        name: 'Is Between',
        value: 'BETWEEN'
    }
]

const yesnoOptions = [
    {
        name: 'Yes',
        value: 1
    },
    {
        name: 'No',
        value: 0
    }
]

const truefalseOptions = [
    {
        name: 'True',
        value: 1
    },
    {
        name: 'False',
        value: 0
    }
]
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
                    <p class="mt-4">Number of selected fields: <span class="text-orange-500">{{
                            localStorageState.selected_fields.length }}</span></p>
                </div>
            </div>
        </div>
        <div class="w-3/4 pt-5 overflow-hidden bg-white shadow-xl sm:rounded-lg">
            <!-- {{ localStorageState.selected_fields }} -->
            <SelectFilters :selected-data-fields="localStorageState.selected_fields" :field-events="fieldEvents" :metadata-by-field="metadataByField" />
        </div>
    </div>


</template>