<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import RBS from '../shared/RBS.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()

</script>
<template>
    <div v-if="props.projectData?.length > 0">
        <RBS :description="props.description" :text-inside="props.textInside" :item-metadata="props.itemMetadata"
            :project-data="props.projectData" />
    </div>
</template>