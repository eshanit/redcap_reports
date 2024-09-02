<script setup>
import { ref, onMounted, reactive, watch } from 'vue';

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

// Track selected checkboxes
const selectedCheckboxes = reactive({});

// Watch for changes in modelValue to update selected checkboxes
watch(() => props.modelValue, (newValue) => {
  selectedCheckboxes = {}; // Reset selected checkboxes
  newValue.forEach(item => {
    selectedCheckboxes[item.event_id] = true; // Mark checkboxes as selected
  });
}, { immediate: true });

// Toggle the selection of an event
const toggleSelection = (event) => {
  const newValue = [...props.modelValue];
  const index = newValue.findIndex(item => item.event_id === event.event_id);

  if (index === -1) {
    // Add the event if it was not selected
    newValue.push({
      ...event,
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
    // Remove the event if it was selected
    newValue.splice(index, 1);
  }
  
  emit('update:modelValue', newValue);
};
</script>

<template>
  <div ref="checkboxGroup" class="flex flex-col">
    <div class="grid grid-cols-1 gap-5 py-5">
      <div class="flex items-center mb-2 cursor-pointer" v-for="option in options" :key="option.event_id">
{{ option }}
        <div class="w-1/4">
          <label :for="option.event_id" class="flex items-center">
            <input 
              type="checkbox" 
              :name="name" 
              :id="option.event_id" 
              :value="{
                name: option.name,
                event_id: option.event_id
              }" 
              :checked="modelValue.some(item => item.event_id === option.event_id)" 
              @change="toggleSelection({
                name: option.name,
                event_id: option.event_id
              })" 
              class="text-blue-600 transition duration-150 ease-in-out form-checkbox" 
            />
            <span class="ml-2">{{ option.name }}</span>
          </label>
        </div>

        <div v-if="modelValue.some(item => item.event_id === option.event_id)" class="w-3/4 ml-4">
          {{ fieldNameMetadata.element_type }}
          <div id="responses" class="border-l border-white">
            <div 
              v-if="['select', 'radio', 'checkbox'].includes(fieldNameMetadata.element_type)"
            >
              <div class="py-5 italic text-teal-500 font-extralight">
                If you want records for all responses on this item, do not select any options below, just ignore.
              </div>
              <div class="px-1.5">
                <div class="grid grid-cols-3 gap-5 pb-5">
                  <label 
                    v-for="responseOption in transformSelect(fieldNameMetadata.element_enum)"
                    :key="responseOption.value" 
                    :for="responseOption.value" 
                    class="flex items-center mb-2 cursor-pointer"
                  >
                    <input 
                      type="checkbox" 
                      name="fieldValues" 
                      :value="responseOption.value" 
                      :id="responseOption.value"
                      :checked="modelValue.find(item => item.event_id === option.event_id)?.query.collection.includes(responseOption.value)"
                      @change="updateQuery('collection', $event.target.checked 
                        ? [...(modelValue.find(item => item.event_id === option.event_id)?.query.collection || []), responseOption.value]
                        : (modelValue.find(item => item.event_id === option.event_id)?.query.collection || []).filter(q => q !== responseOption.value),
                        { name: option.name, event_id: option.event_id }
                      )" 
                      class="text-blue-600 transition duration-150 ease-in-out form-checkbox" 
                    />
                    <span class="ml-2" v-html="responseOption.name"></span>
                  </label>
                </div>
              </div>
            </div>

            <div v-else-if="fieldNameMetadata.element_type === 'yesno'">
              <div class="py-5 italic text-teal-500 font-extralight">
                If you want records for all responses on this item, do not select any options below, just ignore.
              </div>
              <div class="px-1.5">
                <label 
                  v-for="responseOption in yesnoOptions" 
                  :key="responseOption.value" 
                  :for="responseOption.value" 
                  class="flex items-center mb-2 cursor-pointer"
                >
                  <input 
                    type="checkbox" 
                    name="fieldValues" 
                    :value="responseOption.value" 
                    :id="responseOption.value"
                    :checked="modelValue.find(item => item.event_id === option.event_id)?.query.collection.includes(responseOption.value)"
                    @change="updateQuery('collection', $event.target.checked 
                      ? [...(modelValue.find(item => item.event_id === option.event_id)?.query.collection || []), responseOption.value]
                      : (modelValue.find(item => item.event_id === option.event_id)?.query.collection || []).filter(q => q !== responseOption.value),
                      { name: option.name, event_id: option.event_id }
                    )" 
                    class="text-blue-600 transition duration-150 ease-in-out form-checkbox" 
                  />
                  <span class="ml-2" v-html="responseOption.name"></span>
                </label>
              </div>
            </div>

            <div v-else-if="fieldNameMetadata.element_type === 'truefalse'">
              <div class="py-5 italic text-teal-500 font-extralight">
                If you want records for all responses on this item, do not select any options below, just ignore.
              </div>
              <div class="px-1.5">
                <label 
                  v-for="responseOption in truefalseOptions" 
                  :key="responseOption.value" 
                  :for="responseOption.value" 
                  class="flex items-center mb-2 cursor-pointer"
                >
                  <input 
                    type="checkbox" 
                    name="fieldValues" 
                    :value="responseOption.value" 
                    :id="responseOption.value"
                    :checked="modelValue.find(item => item.event_id === option.event_id)?.query.collection.includes(responseOption.value)"
                    @change="updateQuery('collection', $event.target.checked 
                      ? [...(modelValue.find(item => item.event_id === option.event_id)?.query.collection || []), responseOption.value]
                      : (modelValue.find(item => item.event_id === option.event_id)?.query.collection || []).filter(q => q !== responseOption.value),
                      { name: option.name, event_id: option.event_id }
                    )" 
                    class="text-blue-600 transition duration-150 ease-in-out form-checkbox" 
                  />
                  <span class="ml-2" v-html="responseOption.name"></span>
                </label>
              </div>
            </div>

            <div class="flex gap-5" v-else-if="fieldNameMetadata.element_type === 'calc'">
              <div>
                <SelectInput 
                  :value="modelValue.find(item => item.event_id === option.event_id)?.query.operator"
                  @input="updateQuery('operator', $event.target.value, { name: option.name, event_id: option.event_id })"
                >
                  <template #options>
                    <option 
                      v-for="operator in operatorOptions" 
                      :value="operator.value" 
                      class=""
                    >
                      <span class="text-sm italic font-thin text-gray-400 cursor-pointer py-1.5">
                        {{ operator.name }}
                      </span>
                    </option>
                  </template>
                </SelectInput>
              </div>
              <div>
                <div class="flex gap-5" v-if="modelValue[0].query.operator === 'BETWEEN'">
                  <div>
                    <input 
                      ref="input" 
                      type="number"
                      class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection?.[0]?.min"
                      @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id }, 'min')"
                    />
                  </div>
                  <div class="py-2">AND</div>
                  <div>
                    <input 
                      ref="input" 
                      type="number"
                      class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection?.[0]?.max"
                      @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id }, 'max')"
                    />
                  </div>
                </div>
                <div v-else-if="['=', '<', '<=', '>', '>=', '!='].includes(modelValue[0].query.operator)">
                  <input 
                    ref="input" 
                    type="number"
                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection"
                    @input="updateInputQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id })"
                  />
                </div>
                <div v-else>
                  The query <span class="italic font-light text-orange-500">{{ modelValue[0].query.operator }}</span> is not applicable to this field, please choose another.
                </div>
              </div>
            </div>

            <div v-else-if="fieldNameMetadata.element_type === 'text'">
              <div v-if="fieldNameMetadata.element_validation_min || fieldNameMetadata.element_validation_max">
                <div class="flex gap-5">
                  <div>
                    <SelectInput 
                      :value="modelValue.find(item => item.event_id === option.event_id)?.query.operator"
                      @input="updateQuery('operator', $event.target.value, { name: option.name, event_id: option.event_id })"
                    >
                      <template #options>
                        <option 
                          v-for="operator in operatorOptions" 
                          :value="operator.value" 
                          class=""
                        >
                          <span class="text-sm italic font-thin text-gray-400 cursor-pointer py-1.5">
                            {{ operator.name }}
                          </span>
                        </option>
                      </template>
                    </SelectInput>
                  </div>
                  <div>
                    <div class="flex gap-5" v-if="modelValue[0].query.operator === 'BETWEEN'">
                      <div>
                        <input 
                          ref="input" 
                          type="number"
                          class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection?.[0]?.min"
                          :min="fieldNameMetadata.element_validation_min"
                          :max="fieldNameMetadata.element_validation_max"
                          @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id }, 'min')"
                        />
                      </div>
                      <div class="py-2">AND</div>
                      <div>
                        <input 
                          ref="input" 
                          type="number"
                          class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection?.[0]?.max"
                          :min="fieldNameMetadata.element_validation_min"
                          :max="fieldNameMetadata.element_validation_max"
                          @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id }, 'max')"
                        />
                      </div>
                    </div>
                    <div v-else-if="['=', '<', '<=', '>', '>=', '!='].includes(modelValue[0].query.operator)">
                      <input 
                        ref="input" 
                        type="number"
                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection"
                        :min="fieldNameMetadata.element_validation_min" 
                        :max="fieldNameMetadata.element_validation_max"
                        @input="updateInputQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id })"
                      />
                    </div>
                    <div v-else>
                      The query <span class="italic font-light text-orange-500">{{ modelValue[0].query.operator }}</span> is not applicable to this field, please choose another.
                    </div>
                  </div>
                </div>
                <div class="py-1.5 text-xs text-gray-500 italic">
                  <div v-if="fieldNameMetadata.element_validation_min && fieldNameMetadata.element_validation_max">
                    Please note minimum value should be {{ fieldNameMetadata.element_validation_min }} and maximum value should be {{ fieldNameMetadata.element_validation_max }}
                  </div>
                  <div v-else-if="fieldNameMetadata.element_validation_min && !fieldNameMetadata.element_validation_max">
                    Please note minimum value should be {{ fieldNameMetadata.element_validation_min }}
                  </div>
                  <div v-else-if="!fieldNameMetadata.element_validation_min && fieldNameMetadata.element_validation_max">
                    Please note should be maximum {{ fieldNameMetadata.element_validation_max }}
                  </div>
                </div>
              </div>
              <div v-else-if="['date_dmy', 'date_dym', 'date_mdy', 'date_myd', 'date_ydm', 'date_ymd'].includes(fieldNameMetadata.element_validation_type)">
                <div class="flex gap-5">
                  <div>
                    <SelectInput 
                      :value="modelValue.find(item => item.event_id === option.event_id)?.query.operator"
                      @input="updateQuery('operator', $event.target.value, { name: option.name, event_id: option.event_id })"
                    >
                      <template #options>
                        <option 
                          v-for="operator in operatorOptions" 
                          :value="operator.value" 
                          class=""
                        >
                          <span class="text-sm italic font-thin text-gray-400 cursor-pointer py-1.5">
                            {{ operator.name }}
                          </span>
                        </option>
                      </template>
                    </SelectInput>
                  </div>
                  <div>
                    <div class="flex gap-5" v-if="modelValue[0].query.operator === 'BETWEEN'">
                      <div>
                        <input 
                          ref="input" 
                          type="date"
                          class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection?.[0]?.min"
                          @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id }, 'min')"
                        />
                      </div>
                      <div class="py-2">AND</div>
                      <div>
                        <input 
                          ref="input" 
                          type="date"
                          class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection?.[0]?.max"
                          @input="updateInputBetweenQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id }, 'max')"
                        />
                      </div>
                    </div>
                    <div v-else-if="['=', '<', '<=', '>', '>=', '!='].includes(modelValue[0].query.operator)">
                      <input 
                        ref="input" 
                        type="date"
                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection"
                        :min="fieldNameMetadata.element_validation_min" 
                        :max="fieldNameMetadata.element_validation_max"
                        @input="updateInputQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id })"
                      />
                    </div>
                    <div v-else>
                      The query <span class="italic font-light text-orange-500">{{ modelValue[0].query.operator }}</span> is not applicable to this field, please choose another.
                    </div>
                  </div>
                </div>
              </div>
              <div v-else>
                <div class="flex gap-5">
                  <div>
                    <SelectInput 
                      :value="modelValue.find(item => item.event_id === option.event_id)?.query.operator"
                      @input="updateQuery('operator', $event.target.value, { name: option.name, event_id: option.event_id })"
                    >
                      <template #options>
                        <option 
                          v-for="operator in operatorOptions" 
                          :value="operator.value" 
                          class=""
                        >
                          <span class="text-sm italic font-thin text-gray-400 cursor-pointer py-1.5">
                            {{ operator.name }}
                          </span>
                        </option>
                      </template>
                    </SelectInput>
                  </div>
                  <div v-if="modelValue[0].query.operator === 'LIKE'">
                    <input 
                      ref="input" 
                      type="text"
                      class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :value="modelValue.find(item => item.event_id === option.event_id)?.query.collection"
                      :min="fieldNameMetadata.element_validation_min" 
                      :max="fieldNameMetadata.element_validation_max"
                      @input="updateInputQuery('collection', $event.target.value, { name: option.name, event_id: option.event_id })"
                    />
                  </div>
                  <div v-else>
                    The query <span class="italic font-light text-orange-500">{{ modelValue[0].query.operator }}</span> is not applicable to this field, please choose another. We suggest for this field type, to use <span class="italic font-light text-orange-500">Contains</span> to search the column for text which contains the text you entered.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>
