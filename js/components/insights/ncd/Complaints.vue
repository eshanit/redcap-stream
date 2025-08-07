<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import MultiSelectAnswer from '../shared/MultiSelectAnswer.vue'

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()


</script>
<template>
    <pre v-if="props.projectData?.length > 0">
<MultiSelectAnswer
    bar-chart-description="Number of respondents (Other Complaints)"
    pie-chart-description="Percentage of respondents per Complaint"
    table-header-text="Complaint"
    diff-table-title="Complaint"
    diff-table-description=""
    :description="props.description"
    :text-inside="props.textInside"
    :item-metadata="props.itemMetadata"
    :project-data="props.projectData"
/>
</pre>
</template>