<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import InstanceDateBasedFew from '../shared/InstanceDateBasedFew.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()

</script>
<template>
    <div v-if="props.projectData?.length > 0">
        <InstanceDateBasedFew current-card-title="Visit Dates (Ongoing/FollowUps) analysed per year"
            most-card-title="Instances where most respondents did followups"
            line-chart-description="Year-on-Year show-ups per visit" :description="props.description"
            :text-inside="props.textInside" :item-metadata="props.itemMetadata" :project-data="props.projectData" />
    </div>

</template>