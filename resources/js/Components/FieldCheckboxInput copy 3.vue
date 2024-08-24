<script setup>
import { ref, onMounted, nextTick, reactive, watch } from 'vue';

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
    newValue.push(value);
  } else {
    newValue.splice(index, 1);
  }
  emit('update:modelValue', newValue);
};

const updateQuery = (query, event) => {
  const selectedEvent = props.modelValue.find(item => item.event_id === event.event_id);
  if (selectedEvent) {
    selectedEvent.query = query;
  }
};

const selectedCheckboxes = reactive({});

watch(props.modelValue, (newValue) => {
  newValue.forEach(item => {
    selectedCheckboxes[item.event_id] = true;
  });
}, { immediate: true });

const query = ref({
  fieldLabel: [],
  operator: '=',
  searchkey: '',
  searchKeyMin: '',
  searchKeyMax: '',
  filter: '0'
});

const updateQueryField = (field, value) => {
  query.value[field] = value;
  emit('update:modelValue', [...props.modelValue]);
};


</script>

<template>
  <div ref="checkboxGroup" class="flex flex-col">
    <div class="grid grid-cols-1 gap-5 pb-5">
      <div class="flex items-center mb-2 cursor-pointer" v-for="option in options" :key="option.event_id">
        <label :for="option.event_id" class="flex items-center">
          <input type="checkbox" :name="name" :value="{
        name: option.descrip,
        event_id: option.event_id
      }" :id="option.event_id" :checked="modelValue.some(item => isEqual(item, {
        name: option.descrip,
        event_id: option.event_id
      }))" @change="updateSelection({
        name: option.descrip,
        event_id: option.event_id
      })" class="text-blue-600 transition duration-150 ease-in-out form-checkbox" />
          <span class="ml-2">{{ option.descrip }}</span>
        </label>
        <div v-if="modelValue.some(item => isEqual(item, {
        name: option.descrip,
        event_id: option.event_id
      }))" class="ml-4">


          <div v-if="fieldNameMetadata.element_type == 'text'">
            <div v-if="fieldNameMetadata.element_validation_min || fieldNameMetadata.element_validation_max">
              <div class="flex gap-5 " v-if="localStorageState.operator == 'BETWEEN'">
                <div>
                  <TextInput id="searchKeyMin" v-model="localStorageState.searchKeyMin" type="number"
                    :min="fieldNameMetadata.element_validation_min"
                    :max="fieldNameMetadata.element_validation_max" class="block w-auto mt-1" autofocus />
                </div>
                <div class="pt-3">
                  And
                </div>
                <div>
                  <TextInput id="searchKeyMin" v-model="localStorageState.searchKeyMax" type="number"
                    :min="fieldNameMetadata.element_validation_min"
                    :max="fieldNameMetadata.element_validation_max" class="block w-auto mt-1" autofocus />
                </div>
              </div>
              <div v-else>
                <TextInput id="name" v-model="localStorageState.searchkey" type="number"
                  :min="fieldNameMetadata.element_validation_min"
                  :max="fieldNameMetadata.element_validation_max" class="block w-auto mt-1" autofocus />
              </div>
              <div class="py-1.5 text-xs text-gray-500 italicsont-thin">
                <div
                  v-if="fieldNameMetadata.element_validation_min && fieldNameMetadata.element_validation_max">
                  Please note minimum value should be {{ fieldNameMetadata.element_validation_min }} and maximum value
                  should be {{
        fieldNameMetadata.element_validation_max }}
                </div>
                <div
                  v-else-if="fieldNameMetadata.element_validation_min && fieldNameMetadata.element_validation_max == null">
                  Please note minimum value should be {{ fieldNameMetadata.element_validation_min }}
                </div>
                <div
                  v-else-if="fieldNameMetadata.element_validation_min == null && fieldNameMetadata.element_validation_max">
                  Please note should be maximum {{ fieldNameMetadata.element_validation_max }}
                </div>
              </div>
            </div>
            <div v-else>
              <div v-if="fieldNameMetadata.element_validation_type == 'date_dmy' ||
        fieldNameMetadata.element_validation_type == 'date_mdy' ||
        fieldNameMetadata.element_validation_type == 'date_ymd'
        ">
                <div class="flex gap-5" v-if="localStorageState.operator == 'BETWEEN'">
                  <div>
                    <TextInput id="name" v-model="localStorageState.searchKeyMin" type="date" class="block w-auto mt-1"
                      autofocus />
                  </div>
                  <div class="pt-3 ">
                    And
                  </div>
                  <div>
                    <TextInput id="name" v-model="localStorageState.searchKeyMax" type="date" class="block w-auto mt-1"
                      autofocus />
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
          <div v-else-if="fieldNameMetadata.element_type == 'radio'">
            <SelectInput v-model="localStorageState.searchkey">
              <template #options>
                <option :value="enumOption.value"
                  v-for=" enumOption in transformSelect(fieldNameMetadata.element_enum)" :key="enumOption.value"
                  class="py-1.5">
                  <span v-html="enumOption.name" />
                </option>
              </template>
            </SelectInput>
          </div>
          <div v-else-if="fieldNameMetadata.element_type == 'select'">
            <SelectInput v-model="localStorageState.searchkey">
              <template #options>
                <option :value="enumOption.value"
                  v-for=" enumOption in transformSelect(fieldNameMetadata.element_enum)" :key="enumOption.value"
                  class="py-1.5">
                  <span class="text-sm italic font-thin text-gray-400">
                    {{ enumOption.name }}
                  </span>
                </option>
              </template>
            </SelectInput>
          </div>
          <div v-else-if="fieldNameMetadata.element_type == 'checkbox'">
            <!-- <SelectInput v-model="localStorageState.searchkey">
            <template #options>
                <option :value="enumOption.value"
                    v-for=" enumOption in transformSelect(fieldNameMetadata.element_enum)" :key="enumOption.value"
                    class="py-1.5">
                    <span class="text-sm italic font-thin text-gray-400" v-html="enumOption.name" />
                </option>
            </template>
        </SelectInput> -->
            <div class="py-5 italic text-teal-500 font-extralight">
              If you want records for all responses on this item , do not select any options below, just ignore.
            </div>
            <div class="px-1.5 ">
              <CheckboxInputName v-model="localStorageState.fieldLabel"
                :options="transformSelect(fieldNameMetadata.element_enum)" name="fieldCheck" />
            </div>
          </div>
          <div v-else-if="fieldNameMetadata.element_type == 'yesno'">
            <SelectInput v-model="localStorageState.searchkey">
              <template #options>
                <option :value="yesno.value" v-for=" yesno in yesnoOptions" class="py-1.5">
                  <span class="text-sm italic font-thin text-gray-400">
                    {{ yesno.name }}
                  </span>
                </option>
              </template>
            </SelectInput>
          </div>
          <div v-else-if="fieldNameMetadata.element_type == 'truefalse'">
            <SelectInput v-model="localStorageState.searchkey">
              <template #options>
                <option :value="truefalse.value" v-for=" truefalse in truefalseOptions" class="py-1.5">
                  <span class="text-sm italic font-thin text-gray-400">
                    {{ truefalse.name }}
                  </span>
                </option>
              </template>
            </SelectInput>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
