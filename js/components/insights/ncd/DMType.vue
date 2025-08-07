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
    bar-chart-description="Number of respondents per DM group"
    pie-chart-description="Percentage of respondents per DM group"
    table-header-text="DM Group"
    diff-table-title="DM Group"
    diff-table-description="This table shows the extend to which each DM group has more or less respondents than the oher groups"
    :description="props.description"
    :text-inside="props.textInside"
    :item-metadata="props.itemMetadata"
    :project-data="props.projectData"
/>
</template>