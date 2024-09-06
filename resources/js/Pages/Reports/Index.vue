<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
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
    <AppLayout title="field Metadta">
        <template #header>
            <div class="flex gap-1.5">
                <h2 class="text-xl font-light leading-tight text-teal-400 hover:text-orange-500">
                    <Link class="btn-indigo" :href="`/projects/${props.metadata[0].project_id}`">
                    {{ appTitle }}
                    </Link>
                </h2>
                <div class="font-thin text-green-500 ">
                    <ChevronRightIcon :size="25" />
                </div>
                <div class="text-xl text-orange-500">
                    MetaData
                </div>
            </div>

        </template>
        <div class="pt-12 pb-6 border-b bg-p">
            <div class="flex gap-1 sm:px-6 lg:px-8">

                <div class="flex col-span-6 gap-1 pr-5 sm:col-span-4">
                    <FormSection @submitted="">

                        <template #title>
                            Search Details
                        </template>

                        <template #description>
                            Search individual respondent responses by typing in the record id in the search box :
                        </template>
                        <template #form>

                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="name" value="Record Id" />
                                <TextInput id="name" v-model="form.recordId" type="text" class="block w-full mt-1"
                                    autofocus />
                                <InputError :message="form.errors.recordId" class="mt-2" />
                            </div>
                        </template>

                        <template #actions>
                            <div class="flex gap-5">
                                <WarningOutlineButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="trackRecord()">
                                    Track Record
                                </WarningOutlineButton>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="fetchRecord()">
                                    Fetch Record
                                </PrimaryButton>
                            </div>

                        </template>
                    </FormSection>
                </div>
                <div class="lg:border-l " />
                <div class="pl-5 ">
                    <div class="text-3xl font-bold text-sky-500">
                        Need more search options?
                    </div>
                    <div class="py-5 ">
                        Explore a range of ways to filter and pull the data you reaaly need from your survey.
                    </div>
                    <div class=" flex gap-2.5">
                        <div>
                            <Link :href="`/project/${props.metadata[0].project_id}/search`">
                            <WarningOutlineButton>
                                Data Portal
                            </WarningOutlineButton>
                            </Link>
                        </div>
                        <div>
                            <Link :href="`/project/${props.metadata[0].project_id}/filtering`">
                            <PrimaryOutlineButton>
                                Filter Portal
                            </PrimaryOutlineButton>
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="sm:px-6 lg:px-8">

            <!-- <pre>
                        {{ tableData }}
                        {{ projectForms }}
                        {{ instrumentSums }}
                        {{ tableData }}
                        {{ actualData }}
                    </pre> -->


            <div class="pt-6 text-3xl font-semibold">
                Instruments
            </div>
            <div class="grid gap-5 pt-12 lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1">
                <div class="flex items-center justify-between px-8 py-5 bg-green-500 rounded shadow-lg"
                    v-for="instrument in instrumentSums">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-white fill-current" view="0 0 20 20">
                            <path d="M0 11l2-2 5 5L18 3l2 2L7 18z" />
                        </svg>
                        <div class="ml-3 font-medium text-white">{{ instrument.form_name }}</div>
                    </div>
                    <div class="ml-3 text-2xl font-medium text-white text-3"> {{ numberWithSpaces(instrument.aggregate)
                        }}
                    </div>
                </div>
            </div>
            <div class="pt-12 text-3xl font-semibold">
                Survey Fields
            </div>
            <div class="py-12">
                <div class="max-w-full mx-auto ">
                    <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                        <div>
                            <div class="p-20">
                                <div class="overflow-x-auto bg-white rounded-md shadow">
                                    <table class="w-full whitespace-nowrap">
                                        <tr class="font-extrabold text-left text-teal-500">
                                            <th class="px-6 pt-6 pb-4">Field</th>
                                            <th class="px-6 pt-6 pb-4">Possible Answers</th>
                                            <th class="px-6 pt-6 pb-4">Form/Instrument</th>
                                            <th class="px-6 pt-6 pb-4">Number of Entries/ Records</th>
                                            <th class="px-6 pt-6 pb-4">View</th>
                                            <!--th class="px-6 pt-6 pb-4" colspan="2">Phone</th-->
                                        </tr>
                                        <tr v-for="field in tableData" :key="field.id"
                                            class=" hover:bg-gray-100 focus-within:bg-gray-100">
                                            <td class="px-2 py-5 border-t ">
                                                <div class="" v-html="field.element_label" />
                                                <div class="text-xs font-light text-gray-500"
                                                    v-html="field.element_note" />
                                            </td>
                                            <td class="py-5 border-t">
                                                <div v-html="field.element_enum" />
                                            </td>
                                            <td class="border-t y-5 ">
                                                {{ field.form_name }}
                                            </td>
                                            <td class="py-5 text-center border-t">
                                                {{ numberWithSpaces(field.count_entries) }}
                                            </td>
                                            <td class="py-5 border-t">
                                                <div>
                                                    <Link class="btn-indigo"
                                                        :href="`/project/${field.project_id}/fieldname/${field.field_name}`">
                                                    <PrimaryButton>
                                                        view
                                                    </PrimaryButton>
                                                    </Link>
                                                </div>
                                            </td>

                                        </tr>
                                        <!-- <tr v-if="fields.data.length === 0">
                                            <td class="px-6 py-4 border-t" colspan="4">No fields found.</td>
                                        </tr> -->
                                    </table>
                                    <!-- <Pagination class="mt-6" :links="fields.links" /> -->

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>