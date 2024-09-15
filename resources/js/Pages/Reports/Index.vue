<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import FormSection from '@/Components/FormSection.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import WarningOutlineButton from '@/Components/WarningOutlineButton.vue';
import ChevronRightIcon from 'vue-material-design-icons/ChevronRight.vue'
import sumArray from '@/Utilities/sumArray';
import { computed, Ref, ref } from 'vue';
import PrimaryOutlineButton from '@/Components/PrimaryOutlineButton.vue';



const tableData: Ref<Array<any>> = ref([])

const props = defineProps<{
    'metadata': any[],
    'actualData': any[],
    'projectForms': any[],
    'appTitle': string,
}>()

const form = useForm({
    recordId: '',
});

props.metadata.forEach((meta) => {
    props.actualData.forEach((data) => {
        if (meta.field_name === data.field_name) {
            tableData.value.push({
                project_id: meta.project_id,
                field_name: meta.field_name,
                form_name: meta.form_name,
                element_label: meta.element_label,
                element_enum: meta.element_enum,
                element_note: meta.element_note,
                field_order: meta.field_order,
                _field_name: data.field_name,
                count_entries: data.value
            })
        }

    })
})

const numberWithSpaces = (x: number) => {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}


const instrumentSums = computed(() => {
    const instrumentSum: any[] = [];
    props.projectForms.forEach((form) => {
        instrumentSum.push({
            form_name: form.form_name,
            aggregate: sumArray(tableData.value.filter((el) => el.form_name == form.form_name), 'count_entries')
        });
    })
    return instrumentSum
})

const fetchRecord = () => {
    router.get('/project/' + props.metadata[0].project_id + '/record/' + form.recordId)
};

const trackRecord = () => {
    router.get('/project/' + props.metadata[0].project_id + '/record/' + form.recordId + '/tracking')
};



</script>
<template>
    <AppLayout :title="appTitle">
        <template #header>
            <div class="flex items-center space-x-2">
                <h2 class="text-xl font-light leading-tight text-gray-400 hover:text-orange-500">
                    <Link :href="`/projects/${props.metadata[0].project_id}`">{{ appTitle }}</Link>
                </h2>
                <ChevronRightIcon class="text-green-500" :size="25" />
                <div class="text-xl text-orange-500">MetaData</div>
            </div>
        </template>
        
        <div class="pt-12 pb-6 border-b bg-p sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="col-span-1 p-6 bg-white rounded-lg shadow">
                    <h3 class="text-3xl font-bold text-rose-500">Customized Section</h3>
                    <p class="py-5">This section contains customized analysis and reports customized for <span class="font-bold text-green-500">{{ appTitle }}</span>. Click the button below to enter.</p>
                    <Link :href="`/project/${props.metadata[0].project_id}/custom-pages`">
                        <WarningOutlineButton>I'm Feeling Lucky</WarningOutlineButton>
                    </Link>
                </div>

                <div class="col-span-1">
                    <FormSection @submitted="">
                        <template #form>
                            <div class="col-span-6 sm:col-span-4 ">
                                <h3 class="text-3xl font-semibold">Search Details</h3>
                                <p>Search individual respondent responses by using the record id:</p>
                                <TextInput id="recordId" v-model="form.recordId" type="text" class="block w-full mt-1" autofocus />
                                <InputError :message="form.errors.recordId" class="mt-2" />
                            </div>
                        </template>
                        <template #actions>
                            <div class="flex space-x-5">
                                <WarningOutlineButton :disabled="form.processing" @click="trackRecord()">Track Record</WarningOutlineButton>
                                <PrimaryButton :disabled="form.processing" @click="fetchRecord()">Fetch Record</PrimaryButton>
                            </div>
                        </template>
                    </FormSection>
                </div>

                <div class="col-span-1">
                    <h3 class="text-3xl font-bold text-sky-500">Need more search options?</h3>
                    <p class="py-5">Explore a range of ways to filter and pull the data you really need from your survey.</p>
                    <div class="flex gap-5">
                        <Link :href="`/project/${props.metadata[0].project_id}/search`">
                            <WarningOutlineButton>Data Portal</WarningOutlineButton>
                        </Link>
                        <Link :href="`/project/${props.metadata[0].project_id}/filtering`">
                            <PrimaryOutlineButton>Filter Portal</PrimaryOutlineButton>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="sm:px-6 lg:px-8">
            <h2 class="pt-6 text-3xl font-semibold">Instruments</h2>
            <div class="grid gap-5 pt-12 lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1">
                <div class="flex items-center justify-between px-8 py-5 bg-green-500 rounded shadow-lg" v-for="instrument in instrumentSums" :key="instrument.form_name">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-white fill-current" viewBox="0 0 20 20">
                            <path d="M0 11l2-2 5 5L18 3l2 2L7 18z" />
                        </svg>
                        <span class="ml-3 font-medium text-white">{{ instrument.form_name }}</span>
                    </div>
                    <span class="ml-3 text-2xl font-medium text-white">{{ numberWithSpaces(instrument.aggregate) }}</span>
                </div>
            </div>

            <h2 class="pt-12 text-3xl font-semibold">Survey Fields</h2>
            <div class="py-12">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <table class="min-w-full">
                        <thead>
                            <tr class="font-extrabold text-left text-teal-500">
                                <th class="px-6 py-4">Field</th>
                                <th class="px-6 py-4">Possible Answers</th>
                                <th class="px-6 py-4">Form/Instrument</th>
                                <th class="px-6 py-4">Number of Entries/Records</th>
                                <th class="px-6 py-4">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="field in tableData" :key="field.id" class="hover:bg-gray-100">
                                <td class="px-2 py-5 border-t">
                                    <div v-html="field.element_label"></div>
                                    <div class="text-xs font-light text-gray-500" v-html="field.element_note"></div>
                                </td>
                                <td class="py-5 border-t" v-html="field.element_enum"></td>
                                <td class="border-t">{{ field.form_name }}</td>
                                <td class="py-5 text-center border-t">{{ numberWithSpaces(field.count_entries) }}</td>
                                <td class="py-5 border-t">
                                    <Link :href="`/project/${field.project_id}/fieldname/${field.field_name}`">
                                        <PrimaryButton>View</PrimaryButton>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>