<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import SingleSelectAnswer from '../shared/SingleSelectAnswer.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()

</script>
<template>
<SingleSelectAnswer 
    bar-chart-description="Sub Diagnosis (Count Respondents)"
    pie-chart-description="Sub Diagnosis (Count %)"
    table-header-text="Sub Diagnosis"
    diff-table-title="Sub Diagnosis"
    diff-table-description="This table shows the extend to which each main diagnosis group has more or less respondents than the oher groups"
    :description="props.description"
    :text-inside="props.textInside"
    :item-metadata="props.itemMetadata"
    :project-data="props.projectData"
/>
</template>