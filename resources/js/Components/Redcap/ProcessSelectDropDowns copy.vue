<script setup lang="ts">
import { useStorage } from '@vueuse/core'
import { reactive } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import CheckboxInputName from '@/Components/CheckboxInputName.vue';
import RadioInput from '@/Components/RadioInput.vue';
import transformSelect from '@/Utilities/transformSelect';
import WarningOutlineButton from '../WarningOutlineButton.vue';

defineProps<{
    'selectedFieldMetadata': any
}>()


const init = {
    fieldLabel: undefined,
    operator: '=',
    searchkey: '',
    searchKeyMin: '',
    searchKeyMax: '',
    filter: '0',
}

const state = reactive(init)

const localStorageState = useStorage('dataFieldQuery', state)

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
    {{ selectedFieldMetadata.element_type }}
    <div v-if="selectedFieldMetadata.element_type == 'text'">
        <div v-if="selectedFieldMetadata.element_validation_min || selectedFieldMetadata.element_validation_max">
            <div class="flex gap-5 " v-if="localStorageState.operator == 'BETWEEN'">
                <div>
                    <TextInput id="searchKeyMin" v-model="localStorageState.searchKeyMin" type="number"
                        :min="selectedFieldMetadata.element_validation_min"
                        :max="selectedFieldMetadata.element_validation_max" class="block w-auto mt-1" autofocus />
                </div>
                <div class="pt-3">
                    And
                </div>
                <div>
                    <TextInput id="searchKeyMin" v-model="localStorageState.searchKeyMax" type="number"
                        :min="selectedFieldMetadata.element_validation_min"
                        :max="selectedFieldMetadata.element_validation_max" class="block w-auto mt-1" autofocus />
                </div>

            </div>
            <div v-else>
                <TextInput id="name" v-model="localStorageState.searchkey" type="number"
                    :min="selectedFieldMetadata.element_validation_min"
                    :max="selectedFieldMetadata.element_validation_max" class="block w-auto mt-1" autofocus />
            </div>
            <div class="py-1.5 text-xs text-gray-500 italicsont-thin">
                <div
                    v-if="selectedFieldMetadata.element_validation_min && selectedFieldMetadata.element_validation_max">
                    Please note minimum value should be {{
        selectedFieldMetadata.element_validation_min
    }} and maximum value should be {{
            selectedFieldMetadata.element_validation_max }}
                </div>
                <div
                    v-else-if="selectedFieldMetadata.element_validation_min && selectedFieldMetadata.element_validation_max == null">
                    Please note minimum value should be {{
        selectedFieldMetadata.element_validation_min
    }}
                </div>
                <div
                    v-else-if="selectedFieldMetadata.element_validation_min == null && selectedFieldMetadata.element_validation_max">
                    Please note should be maximum {{
        selectedFieldMetadata.element_validation_max
    }}
                </div>

            </div>
        </div>
        <div v-else>
            <div v-if="selectedFieldMetadata.element_validation_type == 'date_dmy' ||
        selectedFieldMetadata.element_validation_type == 'date_mdy' ||
        selectedFieldMetadata.element_validation_type == 'date_ymd'
        ">
                <div class="flex gap-5" v-if="localStorageState.operator == 'BETWEEN'">
                    <div>
                        <TextInput id="name" v-model="localStorageState.searchKeyMin" type="date"
                            class="block w-auto mt-1" autofocus />
                    </div>
                    <div class="pt-3 ">
                        And
                    </div>
                    <div>
                        <TextInput id="name" v-model="localStorageState.searchKeyMax" type="date"
                            class="block w-auto mt-1" autofocus />
                    </div>
                </div>
                <div v-else>
                    <TextInput id="name" v-model="localStorageState.searchkey" type="date" class="block w-auto mt-1"
                        autofocus />
                </div>
            </div>
            <div v-else>
                <TextInput id="name" v-model="localStorageState.searchkey" type="text" class="block w-auto mt-1"
                    autofocus />
            </div>

        </div>


    </div>
    <div v-else-if="selectedFieldMetadata.element_type == 'radio'">
        <SelectInput v-model="localStorageState.searchkey">
            <template #options>
                <!-- <option value="" disabled>Select an option</option> -->

                <option :value="enumOption.value"
                    v-for=" enumOption in transformSelect(selectedFieldMetadata.element_enum)" :key="enumOption.value"
                    class="py-1.5">
                    <!-- <span v-html="formLabel.element_label" /> -->

                    <span v-html="enumOption.name" />

                </option>
            </template>
        </SelectInput>
    </div>
    <div v-else-if="selectedFieldMetadata.element_type == 'select'">
        <SelectInput v-model="localStorageState.searchkey">
            <template #options>
                <!-- <option value="" disabled>Select an option</option> -->

                <option :value="enumOption.value"
                    v-for=" enumOption in transformSelect(selectedFieldMetadata.element_enum)" :key="enumOption.value"
                    class="py-1.5">
                    <!-- <span v-html="formLabel.element_label" /> -->
                    <span class="text-sm italic font-thin text-gray-400">
                        {{ enumOption.name }}
                    </span>
                </option>
            </template>
        </SelectInput>
    </div>
    <div v-else-if="selectedFieldMetadata.element_type == 'checkbox'">
        <!-- {{ transformSelect(selectedFieldMetadata.element_enum) }} -->
        <SelectInput v-model="localStorageState.searchkey">
            <template #options>
                <!-- <option value="" disabled>Select an option</option> -->

                <option :value="enumOption.value"
                    v-for=" enumOption in transformSelect(selectedFieldMetadata.element_enum)" :key="enumOption.value"
                    class="py-1.5">
                    <!-- <span v-html="formLabel.element_label" /> -->
                    <span class="text-sm italic font-thin text-gray-400" v-html="enumOption.name" />
                </option>
            </template>
        </SelectInput>
        <div class=" py-2.5">
            OR
        </div>
        <div class="px-1.5 ">
            <CheckboxInputName v-model="localStorageState.fieldLabel"
                :options="transformSelect(selectedFieldMetadata.element_enum)" name="fieldCheck"
                 />
        </div>

    </div>
    <div v-else-if="selectedFieldMetadata.element_type == 'yesno'">
        <SelectInput v-model="localStorageState.searchkey">
            <template #options>
                <!-- <option value="" disabled>Select an option</option> -->

                <option :value="yesno.value" v-for=" yesno in yesnoOptions" class="py-1.5">
                    <!-- <span v-html="formLabel.element_label" /> -->
                    <span class="text-sm italic font-thin text-gray-400">
                        {{ yesno.name }}
                    </span>
                </option>
            </template>
        </SelectInput>
    </div>
    <div v-else-if="selectedFieldMetadata.element_type == 'truefalse'">
        <SelectInput v-model="localStorageState.searchkey">
            <template #options>
                <!-- <option value="" disabled>Select an option</option> -->

                <option :value="truefalse.value" v-for=" truefalse in truefalseOptions" class="py-1.5">
                    <!-- <span v-html="formLabel.element_label" /> -->
                    <span class="text-sm italic font-thin text-gray-400">
                        {{ truefalse.name }}
                    </span>
                </option>
            </template>
        </SelectInput>
    </div>
</template>