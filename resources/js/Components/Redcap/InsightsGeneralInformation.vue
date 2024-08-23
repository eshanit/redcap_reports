<script setup lang="ts">
defineProps<{
    reportData: any
}>()

</script>
<template>
     <div>
            <div class="font-bold text-orange-500 border-b py-2.5">
                General Information
            </div>
            <div class="grid grid-cols-2 gap-1 pb-5">
                <div class=" py-2.5 font-semibold ">Form Name: </div>
                <div class=" py-2.5">{{ reportData.form_name }}</div>
                <div class=" py-2.5 font-semibold ">Response Key: </div>
                <div class=" py-2.5">
                    <div v-if="reportData.element_enum">
                        <span v-html="reportData.element_enum" />
                    </div>
                    <div class="text-red-500 " v-else>
                        Not Applicable
                    </div>
                </div>
                <div class=" py-2.5 font-semibold " v-if="reportData.element_type">
                    Validation Type:
                </div>
                <div class=" py-2.5" v-if="reportData.element_type">
                    {{ reportData.element_type }}
                </div>
                <div class=" py-2.5 font-semibold " v-if="reportData.element_validation_min">
                    Element
                    Minimum: </div>
                <div class=" py-2.5" v-if="reportData.element_validation_min">{{
                    reportData.element_validation_min }}</div>
                <div class=" py-2.5 font-semibold " v-if="reportData.element_validation_max">
                    Element
                    Maximum: </div>
                <div class=" py-2.5" v-if="reportData.element_validation_max">{{
                    reportData.element_validation_max }}</div>
                <div class=" py-2.5 font-semibold " v-if="reportData.element_note">
                    Element
                    Note:
                </div>
                <div class=" py-2.5" v-if="reportData.element_note">
                    <span v-html="reportData.element_note" />
                </div>
            </div>
        </div>
</template>