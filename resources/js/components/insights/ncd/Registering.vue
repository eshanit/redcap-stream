<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import DateBased from '../shared/DateBased.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()

</script>
<template>
<DateBased
 current-card-title="Number of respondents who registered this year"
 most-card-title="Year where most respondents registered"
 line-chart-description="Year-on-Year register counts"
 :description="props.description"
 :text-inside="props.textInside"
 :item-metadata="props.itemMetadata"
 :project-data="props.projectData"
/>
</template>