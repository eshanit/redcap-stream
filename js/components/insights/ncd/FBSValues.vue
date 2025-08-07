<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import FBS from '../shared/FBS.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()

</script>
<template>
    <div v-if="props.projectData?.length > 0">
        <FBS :description="props.description" :text-inside="props.textInside" :item-metadata="props.itemMetadata"
            :project-data="props.projectData" />
    </div>
</template>