<script setup lang="ts">
import numberWithSpaces from '@/Utilities/numberWithSpaces';
import HumanFemaleIcon from 'vue-material-design-icons/HumanFemale.vue';
import HumanMaleIcon from 'vue-material-design-icons/HumanMale.vue';

const props = defineProps<
    {
        demographics: any
    }>()

</script>
<template>
    <div class="pb-5 border-b">
        <div class="grid grid-cols-2 gap-5">
            <div class="">
                <div>
                    <HumanMaleIcon class="text-blue-500" :size="250" />
                </div>
                <div class="flex gap-2.5">
                    <span>Number of HB1AC Readings:</span>
                    <span class="text-3xl text-blue-500">{{ numberWithSpaces(demographics.genderStatistics?.Male?.count ?? 0) }}</span>
                </div>
                <div class="flex gap-2.5">
                    <span>Mean Hba1c:</span>
                    <span class="text-3xl text-blue-500">{{ demographics.genderStatistics?.Male?.mean ?? 0 }}</span>
                </div>
                <div class="flex gap-2.5">
                    <span>Min Hba1c:</span>
                    <span class="text-3xl text-blue-500">{{ demographics.genderStatistics?.Male?.min ?? 0 }}</span>
                </div>
                <div class="flex gap-2.5">
                    <span>Max Hba1c:</span>
                    <span class="text-3xl text-blue-500">{{ demographics.genderStatistics?.Male?.max ?? 0 }}</span>
                </div>
            </div>
            <div class="">
                <div>
                    <HumanFemaleIcon class="text-pink-500" :size="250" />
                </div>
                <div class="flex gap-2.5">
                    <span>Number of HB1AC Readings:</span>
                    <span class="text-3xl text-pink-500">{{ numberWithSpaces(demographics.genderStatistics?.Female?.count ?? 0) }}</span>
                </div>
                <div class="flex gap-2.5">
                    <span>Mean Hba1c:</span>
                    <span class="text-3xl text-pink-500">{{ demographics.genderStatistics?.Female?.mean ?? 0 }}</span>
                </div>
                <div class="flex gap-2.5">
                    <span>Min Hba1c:</span>
                    <span class="text-3xl text-pink-500">{{ demographics.genderStatistics?.Female?.min ?? 0 }}</span>
                </div>
                <div class="flex gap-2.5">
                    <span>Max Hba1c:</span>
                    <span class="text-3xl text-pink-500">{{ demographics.genderStatistics?.Female?.max ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="container p-4 mx-auto border-b">
        <h1 class="mb-4 text-xl font-bold">Age Group Statistics</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full border border-collapse border-gray-200">
                <thead>
                    <tr class="text-green-400 bg-gray-100">
                        <th class="p-2 border border-gray-300">Age Group</th>
                        <th class="p-2 border border-gray-300">Count</th>
                        <th class="p-2 border border-gray-300">Min</th>
                        <th class="p-2 border border-gray-300">Max</th>
                        <th class="p-2 border border-gray-300">Mean</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(data, ageGroup) in demographics.ageStatistics" :key="ageGroup" class="hover:bg-gray-50">
                        <td class="p-2 font-bold border border-gray-300">{{ ageGroup }}</td>
                        <td class="p-2 border border-gray-300">{{ data.count ?? 0 }}</td>
                        <td class="p-2 border border-gray-300">{{ data.min ?? 0 }}</td>
                        <td class="p-2 border border-gray-300">{{ data.max ?? 0 }}</td>
                        <td class="p-2 border border-gray-300">{{ data.mean ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="container p-4 mx-auto border-b">
        <h1 class="mb-4 text-xl font-bold">Facility Statistics</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full border border-collapse border-gray-200">
                <thead>
                    <tr class="text-orange-500 bg-gray-100">
                        <th class="p-2 border border-gray-300">Facility</th>
                        <th class="p-2 border border-gray-300">Count</th>
                        <th class="p-2 border border-gray-300">Min</th>
                        <th class="p-2 border border-gray-300">Max</th>
                        <th class="p-2 border border-gray-300">Mean</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(data, facility) in demographics.healthFacility" :key="facility" class="hover:bg-gray-50">
                        <td class="p-2 font-bold border border-gray-300">{{ facility }}</td>
                        <td class="p-2 border border-gray-300">{{ data.count ?? 0 }}</td>
                        <td class="p-2 border border-gray-300">{{ data.min ?? 0 }}</td>
                        <td class="p-2 border border-gray-300">{{ data.max ?? 0 }}</td>
                        <td class="p-2 border border-gray-300">{{ data.mean ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>