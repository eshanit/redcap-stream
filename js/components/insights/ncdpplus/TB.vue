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
    bar-chart-description="Number of respondents per TB type"
    pie-chart-description="Percentage of respondents per TB type"
    table-header-text="TB"
    diff-table-title="TB"
    diff-table-description="This table shows TB type group has more or less respondents than the oher groups"
    :description="props.description"
    :text-inside="props.textInside"
    :item-metadata="props.itemMetadata"
    :project-data="props.projectData"
/>
</template>