<script setup lang="ts">
import { computed, ref, Ref } from 'vue';

const surveyForm: Ref<any> = ref([])

const props = defineProps<{
    'eventData': Array<any>,
    'metadata': Array<any>,
    'selectedEvent': string
}>()

const calculatedForm = computed(() => {
    surveyForm.value = []
    props.metadata.forEach((elem: any) => {
        props.eventData.forEach((el: any) => {
            if (el.field_name == elem.field_name) {
                surveyForm.value.push({
                    project_id: el.project_id,
                    event_id: el.event_id,
                    field_name: el.field_name,
                    form_name: elem.form_name,
                    form_menu_description: elem.form_menu_description,
                    element_label: elem.element_label,
                    element_enum: el.element_enum,
                    element_note: el.element_note,
                    value: el.value,
                    description: el.description
                })

            }

        })
    })
    return surveyForm.value
})
</script>
<template>
    <div class="sm:px-6 lg:px-8">

        <!-- <pre>
            {{ eventData }}
            {{ metadata }}
             {{ selectedEvent }}
           {{ calculatedForm }} 
        </pre> -->
        <div v-if="selectedEvent">

            <div class="py-5 text-2xl font-semibold text-orange-500"> {{ selectedEvent }} </div>
            <div class="overflow-y-auto h-[900px]">
                <div>
                    <div class="grid grid-cols-2 gap-5 py-5 font-semibold text-green-500">
                        <div>
                            Field Name
                        </div>
                        <div>
                            Value
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-5 py-5 border-t" v-for="formdata in calculatedForm">
                        <div>
                            {{ formdata.field_name }}
                        </div>
                        <div>
                            {{ formdata.value }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-center text-center h-96" v-else>
            No event have been selected, please select an event on the <span class="px-1 italic font-thin text-red-500"> survey events </span> panel on the left to view data.
        </div>

    </div>
</template>