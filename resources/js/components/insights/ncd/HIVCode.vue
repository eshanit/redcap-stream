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
    bar-chart-description="Number of respondents per HIV Code"
    pie-chart-description="Percentage of respondents per HIV Code"
    table-header-text="HIV Code"
    diff-table-title="HIV Code"
    diff-table-description="This table shows the extend to which each HIV Code group has more or less respondents than the oher group(s)"
    :description="props.description"
    :text-inside="props.textInside"
    :item-metadata="props.itemMetadata"
    :project-data="props.projectData"
/>
</template>