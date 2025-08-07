<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import DateBasedFew from '../shared/DateBasedFew.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()

</script>
<template>
<DateBasedFew
 current-card-title="Number of respondents (GERD)"
 most-card-title="Year where most respondents (Year - GERD)"
 line-chart-description="Year-on-Year (GERD)"
 :description="props.description"
 :text-inside="props.textInside"
 :item-metadata="props.itemMetadata"
 :project-data="props.projectData"
/>
</template>