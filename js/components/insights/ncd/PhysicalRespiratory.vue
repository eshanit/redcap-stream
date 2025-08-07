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
    bar-chart-description="Number of respondents (Physical Eaxmination: Respiratory)"
    pie-chart-description="Percentage of respondents (Physical Eaxmination: Respiratory)"
    table-header-text="Finding"
    diff-table-title="Finding"
    diff-table-description=""
    :description="props.description"
    :text-inside="props.textInside"
    :item-metadata="props.itemMetadata"
    :project-data="props.projectData"
/>
</pre>
</template>