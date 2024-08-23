<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import RadioInput from '@/Components/RadioInput.vue';
import transformSelect from '@/Utilities/transformSelect';
import WarningOutlineButton from '../WarningOutlineButton.vue';
import { useStorage } from '@vueuse/core'
import { reactive } from 'vue';

const props = defineProps<{
    'project': any,
    'metadata': any,
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

const localStorageState = useStorage('dataQuery', state)

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
    <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
        <div>
            <!-- {{ metadata }} -->
            <div class="p-20 ">

                <div class="grid grid-cols-2 gap-5 pb-5">


                    <div id=" query">
                        <div>Fetch data from <span class="text-green-500 "> {{ project.app_title }} </span>
                            where
                        </div>

                        <div class=" py-2.5">
                            <SelectInput v-model="localStorageState.fieldLabel">
                                <template #options>
                                    <!-- <option value="" disabled>Select an option</option> -->

                                    <option :value="JSON.stringify(formLabel)" v-for=" formLabel in metadata "
                                        class="py-1.5">
                                        <!-- <span v-html="formLabel.element_label" /> -->
                                        <span class="text-sm italic font-thin text-gray-400">
                                            {{ formLabel.field_name }}
                                        </span>
                                    </option>
                                </template>
                            </SelectInput>
                        </div>

                        <div class="py-5">
                            <SelectInput v-model="localStorageState.operator">
                                <template #options>
                                    <!-- <option value="" disabled>Select an option</option> -->

                                    <option :value="operator.value" v-for=" operator in operatorOptions" class="py-1.5">
                                        <!-- <span v-html="formLabel.element_label" /> -->
                                        <span class="text-sm italic font-thin text-gray-400">
                                            {{ operator.name }}
                                        </span>
                                    </option>
                                </template>
                            </SelectInput>
                        </div>

                        <div class=" py-2.5" v-if="localStorageState.fieldLabel">
                            <!-- <pre>
        {{ JSON.parse(localStorageState.fieldLabel).element_label }}
        {{ JSON.parse(localStorageState.fieldLabel).element_type }}
        {{ localStorageState.operator }}
    </pre> -->

                            <div v-if="JSON.parse(localStorageState.fieldLabel).element_type == 'text'">
                                <div
                                    v-if="JSON.parse(localStorageState.fieldLabel).element_validation_min || JSON.parse(localStorageState.fieldLabel).element_validation_max">
                                    <div class="flex gap-5 " v-if="localStorageState.operator == 'BETWEEN'">
                                        <div>
                                            <TextInput id="searchKeyMin" v-model="localStorageState.searchKeyMin"
                                                type="number"
                                                :min="JSON.parse(localStorageState.fieldLabel).element_validation_min"
                                                :max="JSON.parse(localStorageState.fieldLabel).element_validation_max"
                                                class="block w-auto mt-1" autofocus />
                                        </div>
                                        <div class="pt-3">
                                            And
                                        </div>
                                        <div>
                                            <TextInput id="searchKeyMin" v-model="localStorageState.searchKeyMax"
                                                type="number"
                                                :min="JSON.parse(localStorageState.fieldLabel).element_validation_min"
                                                :max="JSON.parse(localStorageState.fieldLabel).element_validation_max"
                                                class="block w-auto mt-1" autofocus />
                                        </div>

                                    </div>
                                    <div v-else>
                                        <TextInput id="name" v-model="localStorageState.searchkey" type="number"
                                            :min="JSON.parse(localStorageState.fieldLabel).element_validation_min"
                                            :max="JSON.parse(localStorageState.fieldLabel).element_validation_max"
                                            class="block w-auto mt-1" autofocus />
                                    </div>
                                    <div class="py-1.5 text-xs text-gray-500 italicsont-thin">
                                        <div
                                            v-if="JSON.parse(localStorageState.fieldLabel).element_validation_min && JSON.parse(localStorageState.fieldLabel).element_validation_max">
                                            Please note minimum value should be {{
                            JSON.parse(localStorageState.fieldLabel).element_validation_min
                        }} and maximum value should be {{
                                JSON.parse(localStorageState.fieldLabel).element_validation_max }}
                                        </div>
                                        <div
                                            v-else-if="JSON.parse(localStorageState.fieldLabel).element_validation_min && JSON.parse(localStorageState.fieldLabel).element_validation_max == null">
                                            Please note minimum value should be {{
                            JSON.parse(localStorageState.fieldLabel).element_validation_min
                        }}
                                        </div>
                                        <div
                                            v-else-if="JSON.parse(localStorageState.fieldLabel).element_validation_min == null && JSON.parse(localStorageState.fieldLabel).element_validation_max">
                                            Please note should be maximum {{
                            JSON.parse(localStorageState.fieldLabel).element_validation_max
                        }}
                                        </div>

                                    </div>
                                </div>
                                <div v-else>
                                    <div v-if="JSON.parse(localStorageState.fieldLabel).element_validation_type == 'date_dmy' ||
                            JSON.parse(localStorageState.fieldLabel).element_validation_type == 'date_mdy' ||
                            JSON.parse(localStorageState.fieldLabel).element_validation_type == 'date_ymd'
                            ">
                                        <div class="flex gap-5" v-if="localStorageState.operator == 'BETWEEN'">
                                            <div>
                                                <TextInput id="name" v-model="localStorageState.searchKeyMin"
                                                    type="date" class="block w-auto mt-1" autofocus />
                                            </div>
                                            <div class="pt-3 ">
                                                And
                                            </div>
                                            <div>
                                                <TextInput id="name" v-model="localStorageState.searchKeyMax"
                                                    type="date" class="block w-auto mt-1" autofocus />
                                            </div>
                                        </div>
                                        <div v-else>
                                            <TextInput id="name" v-model="localStorageState.searchkey" type="date"
                                                class="block w-auto mt-1" autofocus />
                                        </div>
                                    </div>
                                    <div v-else>
                                        <TextInput id="name" v-model="localStorageState.searchkey" type="text"
                                            class="block w-auto mt-1" autofocus />
                                    </div>

                                </div>


                            </div>
                            <div v-else-if="JSON.parse(localStorageState.fieldLabel).element_type == 'radio'">
                                <SelectInput v-model="localStorageState.searchkey">
                                    <template #options>
                                        <!-- <option value="" disabled>Select an option</option> -->

                                        <option :value="enumOption.value"
                                            v-for=" enumOption in transformSelect(JSON.parse(localStorageState.fieldLabel).element_enum)"
                                            :key="enumOption.value" class="py-1.5">
                                            <!-- <span v-html="formLabel.element_label" /> -->

                                            <span v-html="enumOption.name" />

                                        </option>
                                    </template>
                                </SelectInput>
                            </div>
                            <div v-else-if="JSON.parse(localStorageState.fieldLabel).element_type == 'select'">
                                <SelectInput v-model="localStorageState.searchkey">
                                    <template #options>
                                        <!-- <option value="" disabled>Select an option</option> -->

                                        <option :value="enumOption.value"
                                            v-for=" enumOption in transformSelect(JSON.parse(localStorageState.fieldLabel).element_enum)"
                                            :key="enumOption.value" class="py-1.5">
                                            <!-- <span v-html="formLabel.element_label" /> -->
                                            <span class="text-sm italic font-thin text-gray-400">
                                                {{ enumOption.name }}
                                            </span>
                                        </option>
                                    </template>
                                </SelectInput>
                            </div>
                            <div v-else-if="JSON.parse(localStorageState.fieldLabel).element_type == 'checkbox'">
                                <SelectInput v-model="localStorageState.searchkey">
                                    <template #options>
                                        <!-- <option value="" disabled>Select an option</option> -->

                                        <option :value="enumOption.value"
                                            v-for=" enumOption in transformSelect(JSON.parse(localStorageState.fieldLabel).element_enum)"
                                            :key="enumOption.value" class="py-1.5">
                                            <!-- <span v-html="formLabel.element_label" /> -->
                                            <span class="text-sm italic font-thin text-gray-400">
                                                {{ enumOption.name }}
                                            </span>
                                        </option>
                                    </template>
                                </SelectInput>
                            </div>
                            <div v-else-if="JSON.parse(localStorageState.fieldLabel).element_type == 'yesno'">
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
                            <div v-else-if="JSON.parse(localStorageState.fieldLabel).element_type == 'truefalse'">
                                <SelectInput v-model="localStorageState.searchkey">
                                    <template #options>
                                        <!-- <option value="" disabled>Select an option</option> -->

                                        <option :value="truefalse.value" v-for=" truefalse in truefalseOptions"
                                            class="py-1.5">
                                            <!-- <span v-html="formLabel.element_label" /> -->
                                            <span class="text-sm italic font-thin text-gray-400">
                                                {{ truefalse.name }}
                                            </span>
                                        </option>
                                    </template>
                                </SelectInput>
                            </div>
                        </div>

                    </div>
                    <div class="p-5 bg-teal-50">
                        <div v-if="localStorageState.fieldLabel">
                            <div class="text-lg font-bold">
                                Survey Question
                            </div>
                            <div class="p-5 ">
                                <span v-html="JSON.parse(localStorageState.fieldLabel).element_label" />
                            </div>

                        </div>
                        <div class="flex justify-center" v-else>
                            No fields selected.
                        </div>

                    </div>

                </div>

                <!--end-->
                <div class="flex justify-around gap-5 pt-5 border-t">
                    <div class="flex gap-5 ">
                        <div>
                            <div>
                                Do you want to fetch all the fields for the above query?
                            </div>
                            <div>
                                Choose <span class="text-rose-500">No</span> to select particular fields or <span
                                    class="text-sky-500">Yes</span> if you want it run for all the fields.
                            </div>
                        </div>
                        <div>
                            <RadioInput v-model="localStorageState.filter" :options="yesnoOptions" name="example" />
                        </div>
                    </div>

                    <div v-if=" localStorageState.filter == '1'">

                        <Link class="flex items-center px-6 py-4 focus:text-indigo-500"
                            :href="`/project/${project.project_id}/data`">
                        <WarningOutlineButton>
                            Fetch Data
                        </WarningOutlineButton>
                        </Link>
                    </div>

                </div>


            </div>

        </div>
    </div>
</template>