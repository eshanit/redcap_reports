<script setup>
import transformSelect from '@/Utilities/transformSelect';
import { ref, onMounted, nextTick, reactive, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import RadioInput from '@/Components/RadioInput.vue';

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
        return obj1.name === obj2.name && obj1.event_id === obj2.event_id;
};

const updateSelection = (value) => {
  const newValue = [...props.modelValue];
  const index = newValue.findIndex(item => isEqual(item, value));
  if (index === -1) {
    newValue.push({
      ...value,
      query: {
        collection: [],
        operator: '=',
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
  const selectedEvent = newValue.find(i => i.event_id === item.event_id);
  if (selectedEvent) {
    selectedEvent.query[property] = value;
  }
  emit('update:modelValue', newValue);
};

/*
const updateQuery = (query, event) => {
        const newValue = [...props.modelValue];
        const selectedEvent = newValue.find(item => item.event_id === event.event_id);
        if (selectedEvent) {
selectedEvent.query = query;
        }
emit('update:modelValue', newValue);
};
*/

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
    <div ref="checkboxGroup" class="flex flex-col">
        <div class="grid grid-cols-1 gap-5 py-5">
            <div class="flex items-center mb-2 cursor-pointer" v-for="option in options" :key="option.event_id">
                <div class="w-1/4">
                    <label :for="option.event_id" class="flex items-center">
                        <input type="checkbox" :name="name" :value="{
        name: option.descrip,
        event_id: option.event_id,
        query: []
                               }" :id="option.event_id" :checked="modelValue.some(item => isEqual(item, {
        name: option.descrip,
        event_id: option.event_id,
        query: []
                               }))" @change="updateSelection({
        name: option.descrip,
        event_id: option.event_id,
        query: []
                               })" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
                        <span class="ml-2">{{ option.descrip }}</span>
                    </label>
                </div>
                <div v-if="modelValue.some(item => isEqual(item, {
        name: option.descrip,
        event_id: option.event_id,
        query: []
                     }))" class="w-3/4 ml-4">
                    {{ fieldNameMetadata.element_type }}
                    <div id="responses" class="border-l border-white">
                        <div id="fromSelect" v-if="fieldNameMetadata.element_type == 'select' ||
                             fieldNameMetadata.element_type == 'radio' ||
                             fieldNameMetadata.element_type == 'checkbox'
                             ">
                            <div class="py-5 italic text-teal-500 font-extralight">
                                If you want records for all responses on this item, do not select any options below, just ignore.
                            </div>
                            <div class="px-1.5">
                                <div ref="checkboxGroup" class="flex flex-col">
                                    <div class="grid grid-cols-3 gap-5 pb-5">
                                        <label v-for="responseOption in transformSelect(fieldNameMetadata.element_enum)"
                                               :key="responseOption.value" :for="responseOption.value"
                                               class="flex items-center mb-2 cursor-pointer">
                                            <input type="checkbox" name="fieldValues" :value="responseOption.value" :id="responseOption.value"
                                                   :checked="modelValue.some(item => item.event_id === responseOption.event_id && item.query?.includes(responseOption.value))"
                                                   @change="updateQuery(
                                                   $event.target.checked
                                                   ? [...(modelValue.find(item => item.event_id === option.event_id)?.query || []), responseOption.value]
                                                   : (modelValue.find(item => item.event_id === option.event_id)?.query || []).filter(q => q !== responseOption.value),
                                                   { name: option.descrip, event_id: option.event_id }
                                                   )" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
                                            <span class="ml-2" v-html="responseOption.name"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="fieldNameMetadata.element_type == 'yesno'">
                            <div class="py-5 italic text-teal-500 font-extralight">
                                If you want records for all responses on this item, do not select any options below, just ignore.
                            </div>
                            <div class="px-1.5">
                                <div ref="checkboxGroup" class="flex flex-col">
                                    <div class="grid grid-cols-3 gap-5 pb-5">
                                        <label v-for="responseOption in yesnoOptions" :key="responseOption.value"
                                               :for="responseOption.value" class="flex items-center mb-2 cursor-pointer">
                                            <input type="checkbox" name="fieldValues" :value="responseOption.value" :id="responseOption.value"
                                                   :checked="modelValue.some(item => item.event_id === responseOption.event_id && item.query?.includes(responseOption.value))"
                                                   @change="updateQuery(
                                                   $event.target.checked
                                                   ? [...(modelValue.find(item => item.event_id === option.event_id)?.query || []), responseOption.value]
                                                   : (modelValue.find(item => item.event_id === option.event_id)?.query || []).filter(q => q !== responseOption.value),
                                                   { name: option.descrip, event_id: option.event_id }
                                                   )" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
                                            <span class="ml-2" v-html="responseOption.name"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="fieldNameMetadata.element_type == 'truefalse'">
                            <div class="py-5 italic text-teal-500 font-extralight">
                                If you want records for all responses on this item, do not select any options below, just ignore.
                            </div>
                            <div class="px-1.5">
                                <div ref="checkboxGroup" class="flex flex-col">
                                    <div class="grid grid-cols-3 gap-5 pb-5">
                                        <label v-for="responseOption in truefalseOptions" :key="responseOption.value"
                                               :for="responseOption.value" class="flex items-center mb-2 cursor-pointer">
                                            <input type="checkbox" name="fieldValues" :value="responseOption.value" :id="responseOption.value"
                                                   :checked="modelValue.some(item => item.event_id === responseOption.event_id && item.query?.includes(responseOption.value))"
                                                   @change="updateQuery(
                                                   $event.target.checked
                                                   ? [...(modelValue.find(item => item.event_id === option.event_id)?.query || []), responseOption.value]
                                                   : (modelValue.find(item => item.event_id === option.event_id)?.query || []).filter(q => q !== responseOption.value),
                                                   { name: option.descrip, event_id: option.event_id }
                                                   )" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
                                            <span class="ml-2" v-html="responseOption.name"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="fieldNameMetadata.element_type == 'calc'">
                            <SelectInput 
                                        :value="modelValue.find(item => item.event_id === option.event_id)?.query"
                                         @input="updateQuery($event.target.value, { name: option.descrip, event_id: option.event_id})">
                                <template #options>
                                    <option :value="operator.value" v-for="operator in operatorOptions" class="py-1.5">
                                    <span class="text-sm italic font-thin text-gray-400">
                                        {{ operator.name }}
                                    </span>
                                    </option>
                                </template>
                            </SelectInput>
                            <input ref="input" type="number"
                                   class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   :value="modelValue.find(item => item.event_id === option.event_id)?.query"
                                   @input="updateQuery($event.target.value, option)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>