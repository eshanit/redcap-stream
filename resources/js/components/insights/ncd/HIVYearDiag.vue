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
 current-card-title="Number of respondents who diagonised with HIV this year"
 most-card-title="Year where most respondents where HIV diagonised"
 line-chart-description="Year-on-Year HIV diagnosis counts"
 :description="props.description"
 :text-inside="props.textInside"
 :item-metadata="props.itemMetadata"
 :project-data="props.projectData"
/>
</template>