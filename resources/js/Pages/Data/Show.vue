<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';


const props = defineProps<{
    data: any[]
}>();

const tableHeaders = computed(() => {
      const shifted: any[] = Object.keys(props.data[0])
    
    if (props.data.length > 0) {
        return {
            headers: Object.keys(props.data[0]),
            keys: shifted.slice(1)
        };
    }
});


</script>

<template>
    <AppLayout title="Field Metadata">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Field Metadata
            </h2>
        </template>
        <div class="sm:px-6 lg:px-8">
            <div class="py-12">
                <div class="max-w-full mx-auto">
                    <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="overflow-x-auto bg-white rounded-md shadow h-[1000px]">
                                <table class="w-full whitespace-nowrap">
                                    <thead class="bg-gray-100">
                                        <tr class="font-extrabold text-left text-teal-500">
                                            <th class="sticky top-0 px-6 pt-6 pb-4 bg-gray-100"
                                                v-for="header in tableHeaders.headers" :key="header">
                                                {{ header }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, i) in props.data" :key="row.record"
                                            class="hover:bg-gray-100 focus-within:bg-gray-100">
                                            <td class="sticky left-0 bg-white border-t">
                                                {{ row['record'] }}
                                            </td>
                                            <td v-for="header in tableHeaders.keys" :key="header" class="px-2 py-5 border-t">
                                               <!-- {{ header }} -->
                                                {{ row[header] }}

                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
