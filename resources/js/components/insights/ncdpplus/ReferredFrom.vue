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
    bar-chart-description="Referred from (Count Respondents)"
    pie-chart-description="Referred from (Count %)"
    table-header-text="Referred from"
    diff-table-title="Referred from"
    diff-table-description="This table shows the extend to which each place respondents are referred from has more or less respondents than the oher places"
    :description="props.description"
    :text-inside="props.textInside"
    :item-metadata="props.itemMetadata"
    :project-data="props.projectData"
/>
</template>