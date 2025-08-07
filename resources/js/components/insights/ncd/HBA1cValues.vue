<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import HBA1c from '../shared/HBA1c.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()

</script>
<template>
    <div v-if="props.projectData?.length > 0">
        <HBA1c :description="props.description" :text-inside="props.textInside" :item-metadata="props.itemMetadata"
            :project-data="props.projectData" />
    </div>
</template>