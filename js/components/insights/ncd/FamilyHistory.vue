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
    bar-chart-description="Counts (Family History-Diabetes/Hypertension)"
    pie-chart-description="Percentage (Family History-Diabetes/Hypertension)"
    table-header-text="Diabetes/Hypertension"
    diff-table-title="Diabetes/Hypertension"
    diff-table-description=""
    :description="props.description"
    :text-inside="props.textInside"
    :item-metadata="props.itemMetadata"
    :project-data="props.projectData"
/>
</pre>
</template>