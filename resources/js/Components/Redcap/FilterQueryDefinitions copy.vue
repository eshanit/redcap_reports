<script setup lang="ts">
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useStorage } from '@vueuse/core';
import { computed } from 'vue';
import handleQueryArrays from '@/Utilities/handleQueryArrays';

// Create a reactive reference to the localStorage data
const selectedData = useStorage<string | null>('selectedEvents', null);

// Parse the JSON data reactively
const data = computed(() => {
        try {
        return selectedData.value ? JSON.parse(selectedData.value) : null;
        } catch (e) {
console.error('Failed to parse JSON:', e);
        return null;
        }
});
</script>

<template>
    <div>
        <pre>
      {{ data }}
        </pre>
        <div class="flex gap-2 py-2.5">
            It looks like you need data from the following conditions:
        </div>

        <div class="list-decimal" v-if="data.selectedEvents.length > 0">
          <div class="grid grid-cols-2 py-2.5">
            <div>
              Condition
            </div>
            <div>
              View
            </div>
          </div>
            <div class="" v-for="selectedEvent in data.selectedEvents">
              <div class="grid grid-cols-2 py-2.5 border-t" v-for="selected in selectedEvent[Object.keys(selectedEvent)[0]]">
                <!-- <span class="text-red-500">{{ selectedEvent }}</span> 
                <span class="text-green-500">{{ selected.length }}</span> -->

                <div class="flex gap-2" v-if="selected?.[0]?.query?.operator === 'BETWEEN'">
                    <span class="italic font-bold text-orange-500">{{ Object.keys(selectedEvent)[0] }}</span> values
                    <span class="text-blue-500 ">{{selected.query.operator}}</span>
                    <span class="italic text-orange-500">{{selected.query.collection[0].min }}</span>
                    <span class="text-blue-500 ">AND</span>
                    <span class="italic text-orange-500">{{selected.query.collection[0].max }}</span>
                    <span>
                        for event
                    </span>
                    <span class="italic text-orange-500">
                        {{  selected.name }}
                    </span>

                </div>
                <!-- {{ selected }} -->
                <div class="flex gap-2" v-if="selected?.query?.operator != 'BETWEEN'">
                    <span class="italic font-bold text-orange-500">{{ Object.keys(selectedEvent)[0] }}</span> values
                    <span class="text-blue-500 " v-if="selected?.query?.operator != 'OR'" >{{ selected?.query?.operator }}</span>
                    <span class="italic text-orange-500">
                        {{  handleQueryArrays(selected?.query?.collection) }}
                    </span>
                    <span>
                        for event
                    </span>
                    <span class="italic text-orange-500">
                        {{  selected?.name }}
                    </span>
                </div>

                <div>
                    <PrimaryButton>
                        View
                    </PrimaryButton>
                </div>
                
              </div>
               
            </div>
          </div>
    </div>
</template>
