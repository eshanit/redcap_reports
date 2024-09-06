<script setup>
import transformSelect from '@/Utilities/transformSelect';
import { ref, onMounted, nextTick, reactive, watch } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';

const props = defineProps({
    modelValue: Array,
    options: {
        type: Array,
        required: true
    },
    name: {
        type: String,
        required: true
    },
    fieldNameMetadata: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

const checkboxGroup = ref(null);

onMounted(async () => {
    await nextTick();
    if (checkboxGroup.value && checkboxGroup.value.hasAttribute('autofocus')) {
        checkboxGroup.value.focus();
    }
});

const isEqual = (obj1, obj2) => {
    return obj1.name === obj2.name && obj1.event_id === 0;
};

const updateSelection = (value) => {
    const newValue = [...props.modelValue];
    const index = newValue.findIndex(item => isEqual(item, value));
    if (index === - 1) {
        newValue.push({
            ...value,
            query: {
                collection: [],
                operator: 'OR',
                searchkey: '',
                searchKeyMin: '',
                searchKeyMax: '',
                filter: '0'
            }
        });
    } else {
        newValue.splice(index, 1);
    }
    emit('update:modelValue', newValue);
};




const updateQuery = (property, value, item) => {
    const newValue = [...props.modelValue];
    const selectedEvent = newValue.find(i => i.event_id === 0);
    if (selectedEvent) {
        if (property == 'operator') {
            console.log('select:', selectedEvent.query[property])
            selectedEvent.query.collection = [{
                min: 0,
                max: 0,
            }];
            selectedEvent.query[property] = value;
        }
        selectedEvent.query[property] = value;
    }
    emit('update:modelValue', newValue);
};


const updateInputQuery = (key, value, option) => {
    const item = props.modelValue.find(item => item.event_id === 0);
    if (item) {
        if (!Array.isArray(item.query[key])) {
            item.query[key] = [];
        }
        // Replace the value in the collection array
        item.query[key] = [value]; // Ensure the value is a number and replace the array
    }
};

//
const updateInputBetweenQuery = (key, value, option, pointer) => {
    const item = props.modelValue.find(item => item.event_id === 0);
    console.log('pointer:', pointer);
    console.log('item:', item);
    if (item) {
        if (!Array.isArray(item.query[key])) {
            item.query[key] = [{
                [pointer]: 0
            }];
        }
        // Replace the value in the collection array
        if (pointer == 'min') {
            item.query[key][0].min = value; // Ensure the value is a number and replace the array
        } else {
            item.query[key][0].max = value;
        }

    }
};


const selectedCheckboxes = reactive({});

watch(props.modelValue, (newValue) => {
    console.log('newValue', newValue)

    newValue.forEach(item => {
        selectedCheckboxes[item.event_id] = true;
    });
}, { immediate: true });



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
        name: 'Greater Than or Equal to (>=)',
        value: '>='
    },
    {
        name: 'Less than (<)',
        value: '<'
    },
    {
        name: 'Less than or equal to (<=)',
        value: '<='
    },
    {
        name: 'Is not',
        value: '!='
    },
    {
        name: 'Is Between',
        value: 'BETWEEN'
    },
    {
        name: 'Contains',
        value: 'LIKE'
    },
    {
        name: 'OR',
        value: 'OR'
    },
    {
        name: 'AND',
        value: 'AND'
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
    <div ref="checkboxGroup" class="flex flex-col">
        <!-- {{ options }} -->
        <div class="grid grid-cols-1 gap-5 py-5">
            <div class="flex items-center mb-2 cursor-pointer" v-for="option in options" :key="0">
                <div class="w-1/4">
                    <label :for="0" class="flex items-center">
                        <input type="checkbox" :name="name" :value="{
                name: option.name, event_id: 0
            }" :id="0" :checked="modelValue.some(item => isEqual(item, {
                name: option.name,
                event_id: 0
            }))" @change="updateSelection({
                name: option.name,
                event_id: 0
            })" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
                        <span class="ml-2">{{ option.name }}</span>
                    </label>
                </div>
                <!-- {{ modelValue.some(item => isEqual(item, {
                name: option.name,
                event_id: 0
            })) }} -->
                <div v-if="modelValue.some(item => isEqual(item, {
                name: option.name,
                event_id: 0
            }))" class="w-3/4 ml-4">
                    {{ fieldNameMetadata.element_type }}
                    <div id="responses" class="border-l border-white">
                        <div id="fromSelect" v-if="fieldNameMetadata.element_type == 'select' ||
                fieldNameMetadata.element_type == 'radio' ||
                fieldNameMetadata.element_type == 'checkbox'
                ">
                            <div class="py-5 italic text-teal-500 font-extralight">
                                If you want records for all responses on this item, do not select any options below,
                                just ignore.
                            </div>
                            <div class="px-1.5">
                                <div ref="checkboxGroup" class="flex flex-col">
                                    <div class="grid grid-cols-3 gap-5 pb-5">
                                        <label v-for="responseOption in transformSelect(fieldNameMetadata.element_enum)"
                                            :key="responseOption.value" :for="responseOption.value"
                                            class="flex items-center mb-2 cursor-pointer">
                                            <input type="checkbox" name="fieldValues" :value="responseOption.value"
                                                :id="responseOption.value"
                                                :checked="modelValue.find(item => item.event_id === 0)?.query.collection.includes(responseOption.value)"
                                                @change="updateQuery('collection', $event.target.checked
                ? [...(modelValue.find(item => item.event_id === 0)?.query.collection || []), responseOption.value]
                : (modelValue.find(item => item.event_id === 0)?.query.collection || []).filter(q => q !== responseOption.value),
                { name: option.name, event_id: 0 }
            )" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
                                            <span class="ml-2" v-html="responseOption.name"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="fieldNameMetadata.element_type == 'yesno'">
                            <div class="py-5 italic text-teal-500 font-extralight">
                                If you want records for all responses on this item, do not select any options below,
                                just ignore.
                            </div>
                            <div class="px-1.5">
                                <label v-for="responseOption in yesnoOptions" :key="responseOption.value"
                                    :for="responseOption.value" class="flex items-center mb-2 cursor-pointer">
                                    <input type="checkbox" name="fieldValues" :value="responseOption.value"
                                        :id="responseOption.value"
                                        :checked="modelValue.find(item => item.event_id === 0)?.query.collection.includes(responseOption.value)"
                                        @change="updateQuery('collection', $event.target.checked
                ? [...(modelValue.find(item => item.event_id === 0)?.query.collection || []), responseOption.value]
                : (modelValue.find(item => item.event_id === 0)?.query.collection || []).filter(q => q !== responseOption.value),
                { name: option.name, event_id: 0 }
            )" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
                                    <span class="ml-2" v-html="responseOption.name"></span>
                                </label>
                            </div>
                        </div>
                        <div v-else-if="fieldNameMetadata.element_type == 'truefalse'">
                            <div class="py-5 italic text-teal-500 font-extralight">
                                If you want records for all responses on this item, do not select any options below,
                                just ignore.
                            </div>
                            <div class="px-1.5">
                                <label v-for="responseOption in truefalseOptions" :key="responseOption.value"
                                    :for="responseOption.value" class="flex items-center mb-2 cursor-pointer">
                                    <input type="checkbox" name="fieldValues" :value="responseOption.value"
                                        :id="responseOption.value"
                                        :checked="modelValue.find(item => item.event_id === 0)?.query.collection.includes(responseOption.value)"
                                        @change="updateQuery('collection', $event.target.checked
                ? [...(modelValue.find(item => item.event_id === 0)?.query.collection || []), responseOption.value]
                : (modelValue.find(item => item.event_id === 0)?.query.collection || []).filter(q => q !== responseOption.value),
                { name: option.name, event_id: 0 }
            )" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
                                    <span class="ml-2" v-html="responseOption.name"></span>
                                </label>
                            </div>
                        </div>
                        <div class="gap-5 " v-else-if="fieldNameMetadata.element_type == 'calc'">
                            <div class="pb-5">
                                <SelectInput :value="modelValue.find(item => item.event_id === 0)?.query.operator"
                                    @input="updateQuery('operator', $event.target.value, { name: option.name, event_id: 0 })">
                                    <template #options>
                                        <option :value="operator.value" v-for="operator in operatorOptions" class="">
                                            <span class="text-sm italic font-thin text-gray-400 cursor-pointer py-1.5">
                                                {{ operator.name }}
                                            </span>
                                        </option>
                                    </template>
                                </SelectInput>
                            </div>
                            <div>
                                <div class="flex gap-5" v-if="modelValue[0].query.operator == 'BETWEEN'">
                                    <div>
                                        <input ref="input" type="number"
                                            class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                            @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: 0 }, 'min')">
                                    </div>
                                    <div class="py-2 ">
                                        AND
                                    </div>
                                    <div>
                                        <input ref="input" type="number"
                                            class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                            @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: 0 }, 'max')">
                                    </div>
                                </div>
                                <div v-else-if="modelValue[0].query.operator == '=' ||
                modelValue[0].query.operator == '<' ||
                modelValue[0].query.operator == '<=' ||
                modelValue[0].query.operator == '>' ||
                modelValue[0].query.operator == '>=' ||
                modelValue[0].query.operator == '!='
                ">
                                    <input ref="input" type="number"
                                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                        @input="updateInputQuery('collection', $event.target.value, { name: option.name, event_id: 0 })">
                                </div>
                                <div v-else>
                                    The query <span class="italic font-light text-orange-500"> {{
                modelValue[0].query.operator }} </span>
                                    is not
                                    applicable to this field, please choose another.
                                </div>
                            </div>

                        </div>
                        <div v-else-if="fieldNameMetadata.element_type == 'text'">
                            <!--if type == text but it has a validation min and a max-->
                            <div
                                v-if="fieldNameMetadata.element_validation_min || fieldNameMetadata.element_validation_max">
                                <div class="gap-5 pb-5">
                                    <div>
                                        <SelectInput
                                            :value="modelValue.find(item => item.event_id === 0)?.query.operator"
                                            @input="updateQuery('operator', $event.target.value, { name: option.name, event_id: 0 })">
                                            <template #options>
                                                <option :value="operator.value" v-for="operator in operatorOptions"
                                                    class="">
                                                    <span
                                                        class="text-sm italic font-thin text-gray-400 cursor-pointer py-1.5">
                                                        {{ operator.name }}
                                                    </span>
                                                </option>
                                            </template>
                                        </SelectInput>
                                    </div>
                                    <div>
                                        <div class="flex gap-5" v-if="modelValue[0].query.operator == 'BETWEEN'">
                                            <div>
                                                <input ref="input" type="number"
                                                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                                    :min="fieldNameMetadata.element_validation_min"
                                                    :max="fieldNameMetadata.element_validation_max"
                                                    @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: 0 }, 'min')">
                                            </div>
                                            <div class="py-2 ">
                                                AND
                                            </div>
                                            <div>
                                                <input ref="input" type="number"
                                                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                                    :min="fieldNameMetadata.element_validation_min"
                                                    :max="fieldNameMetadata.element_validation_max"
                                                    @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: 0 }, 'max')">
                                            </div>
                                        </div>
                                        <div v-else-if="modelValue[0].query.operator == '=' ||
                modelValue[0].query.operator == '<' ||
                modelValue[0].query.operator == '<=' ||
                modelValue[0].query.operator == '>' ||
                modelValue[0].query.operator == '>=' ||
                modelValue[0].query.operator == '!='
                ">
                                            <input ref="input" type="number"
                                                class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                                :min="fieldNameMetadata.element_validation_min"
                                                :max="fieldNameMetadata.element_validation_max"
                                                @input="updateInputQuery('collection', $event.target.value, { name: option.name, event_id: 0 })">
                                        </div>
                                        <div v-else>
                                            The query <span class="italic font-light text-orange-500"> {{
                modelValue[0].query.operator }}
                                            </span>
                                            is not
                                            applicable to this field, please choose another.
                                        </div>
                                    </div>
                                </div>
                                <div class="py-1.5 text-xs text-gray-500 italicsont-thin">
                                    <div
                                        v-if="fieldNameMetadata.element_validation_min && fieldNameMetadata.element_validation_max">
                                        Please note minimum value should be {{ fieldNameMetadata.element_validation_min
                                        }} and maximum value
                                        should be {{ fieldNameMetadata.element_validation_max }}
                                    </div>
                                    <div
                                        v-else-if="fieldNameMetadata.element_validation_min && fieldNameMetadata.element_validation_max == null">
                                        Please note minimum value should be {{ fieldNameMetadata.element_validation_min
                                        }}
                                    </div>
                                    <div
                                        v-else-if="fieldNameMetadata.element_validation_min == null && fieldNameMetadata.element_validation_max">
                                        Please note should be maximum {{ fieldNameMetadata.element_validation_max }}
                                    </div>
                                </div>
                            </div>

                            <!--type = text but validation type is date-->

                            <div v-else-if="fieldNameMetadata.element_validation_type == 'date_dmy' ||
                fieldNameMetadata.element_validation_type == 'date_dym' ||
                fieldNameMetadata.element_validation_type == 'date_mdy' ||
                fieldNameMetadata.element_validation_type == 'date_myd' ||
                fieldNameMetadata.element_validation_type == 'date_ydm' ||
                fieldNameMetadata.element_validation_type == 'date_ymd'
                ">
                                <div class="gap-5 ">
                                    <div class="pb-5">
                                        <SelectInput
                                            :value="modelValue.find(item => item.event_id === 0)?.query.operator"
                                            @input="updateQuery('operator', $event.target.value, { name: option.name, event_id: 0 })">
                                            <template #options>
                                                <option :value="operator.value" v-for="operator in operatorOptions"
                                                    class="">
                                                    <span
                                                        class="text-sm italic font-thin text-gray-400 cursor-pointer py-1.5">
                                                        {{ operator.name }}
                                                    </span>
                                                </option>
                                            </template>
                                        </SelectInput>
                                    </div>
                                    <div class="">
                                        <div class="flex gap-5" v-if="modelValue[0].query.operator == 'BETWEEN'">

                                            <div>
                                                <input ref="input" type="date"
                                                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                                    @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: 0 }, 'min')">
                                            </div>
                                            <div class="py-2 ">
                                                AND
                                            </div>
                                            <div>
                                                <input ref="input" type="date"
                                                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                                    @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: 0 }, 'max')">
                                            </div>
                                        </div>
                                        <div v-else-if="modelValue[0].query.operator == '=' ||
                modelValue[0].query.operator == '<' ||
                modelValue[0].query.operator == '<=' ||
                modelValue[0].query.operator == '>' ||
                modelValue[0].query.operator == '>=' ||
                modelValue[0].query.operator == '!='
                ">
                                            <input ref="input" type="date"
                                                class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                                :min="fieldNameMetadata.element_validation_min"
                                                :max="fieldNameMetadata.element_validation_max"
                                                @input="updateInputQuery('collection', $event.target.value, { name: option.name, event_id: 0 })">
                                        </div>
                                        <div v-else>
                                            The query <span class="italic font-light text-orange-500"> {{
                modelValue[0].query.operator }}
                                            </span>
                                            is not
                                            applicable to this field, please choose another.
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div v-else>
                                <div class="flex gap-5">
                                    <div>
                                        <SelectInput
                                            :value="modelValue.find(item => item.event_id === 0)?.query.operator"
                                            @input="updateQuery('operator', $event.target.value, { name: option.name, event_id: 0 })">
                                            <template #options>
                                                <option :value="operator.value" v-for="operator in operatorOptions"
                                                    class="">
                                                    <span
                                                        class="text-sm italic font-thin text-gray-400 cursor-pointer py-1.5">
                                                        {{ operator.name }}
                                                    </span>
                                                </option>
                                            </template>
                                        </SelectInput>
                                    </div>
                                    <div v-if="modelValue[0].query.operator == 'LIKE'">
                                        <input ref="input" type="text"
                                            class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                            :min="fieldNameMetadata.element_validation_min"
                                            :max="fieldNameMetadata.element_validation_max"
                                            @input="updateInputQuery('collection', $event.target.value, { name: option.name, event_id: 0 })">
                                    </div>
                                    <div v-else>
                                        The query <span class="italic font-light text-orange-500"> {{
                modelValue[0].query.operator }}
                                        </span>
                                        is not
                                        applicable to this field, please choose another. We suggest for this field type,
                                        to use <span class="italic font-light text-orange-500"> Contains
                                        </span> to search the column for text which contains the text you entered.
                                    </div>

                                </div>
                            </div>

                        </div>
                        <div v-else-if="fieldNameMetadata.element_type == 'textarea'">
                            <div class="flex gap-5">
                                <div>
                                    <SelectInput :value="modelValue.find(item => item.event_id === 0)?.query.operator"
                                        @input="updateQuery('operator', $event.target.value, { name: option.name, event_id: 0 })">
                                        <template #options>
                                            <option :value="operator.value" v-for="operator in operatorOptions"
                                                class="">
                                                <span
                                                    class="text-sm italic font-thin text-gray-400 cursor-pointer py-1.5">
                                                    {{ operator.name }}
                                                </span>
                                            </option>
                                        </template>
                                    </SelectInput>
                                </div>
                                <div v-if="modelValue[0].query.operator == 'LIKE'">
                                    <!-- {{ modelValue[0].query.collection[0]= '' }} -->
                                    <input ref="input" type="text"
                                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :value="modelValue.find(item => item.event_id === 0)?.query.collection"
                                        :min="fieldNameMetadata.element_validation_min"
                                        :max="fieldNameMetadata.element_validation_max"
                                        @input="updateInputQuery('collection', $event.target.value, { name: option.name, event_id: 0 })">
                                </div>
                                <div v-else>
                                    The query <span class="italic font-light text-orange-500"> {{
                                        modelValue[0].query.operator }}
                                    </span>
                                    is not
                                    applicable to this field, please choose another. We suggest for this field type, to
                                    use <span class="italic font-light text-orange-500"> Contains
                                    </span> to search the column for text which contains the text you entered.
                                </div>

                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</template>