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
    bar-chart-description="Number of respondents per Facility"
    pie-chart-description="Percentage of respondents per Facility"
    table-header-text="Facility"
    diff-table-title="Facility"
    diff-table-description="This table shows the extend to which each Facility has more or less respondents than the oher groups"
    :description="props.description"
    :text-inside="props.textInside"
    :item-metadata="props.itemMetadata"
    :project-data="props.projectData"
/>
</template>