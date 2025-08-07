<script setup lang="ts">
import { Ref, ref, watch, reactive } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Link, Head, useForm } from '@inertiajs/vue3';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type BreadcrumbItem } from '@/types';
import { type IProject, type IMetadata } from '@/types/IProject';
import useGroupQuestionItems from '@/composables/useGroupQuestionItems';
import useGetFieldNameData from '@/composables/useGetFieldNameData';
import { TriangleAlert } from 'lucide-vue-next';
import dataItems from '@/data/ncdpplus/dataItems';
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue';

const props = defineProps<{
    project: IProject,
    metadata: Array<IMetadata>
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: props.project.app_title,
        href: '/ncd_pplus/project/' + props.project.project_id,
    },
    {
        title: 'Questionnaire Insights',
        href: ''
    }
];


const groupedArray = useGroupQuestionItems(props.metadata)

const form = useForm({
    fieldName: ''
})

// Reactive references for the composable
const projectId = ref(props.project.project_id);
const fieldName = ref(form.fieldName);

const { itemData, isLoading, error } = useGetFieldNameData(projectId, fieldName);

// Watch for fieldName changes if needed
watch(() => form.fieldName, (newVal) => {
    fieldName.value = newVal;
});


const projectInfo = (fieldName: string): IMetadata | undefined => {
    return props.metadata.find((m) => {
        return m.field_name == fieldName
    })
}


const selectedComponent = (fieldName: string) => {
    return dataItems.find(d => d.name == fieldName)
}

</script>

<template>

    <Head title="Project Questionnaire Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Heading :title="props.project.app_title" description="Questionnaire Insights" class="pt-5" />
       
            <form @submit.prevent="form.post('/login')">
                <Select v-model="form.fieldName">
                    <SelectTrigger class="sm:w-full lg:w-1/3 md:w-1/3">
                        <SelectValue placeholder="Select a field" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup v-for="(group, index) in groupedArray" :key="index">
                            <SelectLabel>
                                <div class="text-orange-500">
                                    {{ group.form_name }}
                                </div>
                            </SelectLabel>
                            <SelectItem v-for="field in group.fields" :key="field.field_name" :value="field.field_name">
                                <div class="py-2.5 cursor-pointer" v-html="field.element_label"></div>
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </form>
     <!-- <pre>
                {{ form.fieldName }}

                {{ itemData }}
            </pre> -->
            <div v-if="form.fieldName">
                <div v-if="isLoading" class="flex items-center justify-center h-32">
                    <LoadingSpinner />
                </div>
                <div v-else>
                    <component :is="selectedComponent(form.fieldName)?.component"
                        v-bind="selectedComponent(form.fieldName)?.props"
                        :description="selectedComponent(form.fieldName)?.description || '-'"
                        :text-inside="selectedComponent(form.fieldName)?.textInside || '-'"
                        :item-metadata="projectInfo(form.fieldName)" :project-data="itemData[form.fieldName]" />
                </div>
            </div>
            <div class=" border rounded-lg bg-gray-50 h-full  flex items-center justify-center" v-else>
                <div>
                    <div class="flex items-center justify-center pb-2.5">
                        <TriangleAlert class="size-18" color="gray" />
                    </div>
                    <div class="text-center">
                        Nothing to show. Please select a field/Questionnare Item to view insights on it.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>