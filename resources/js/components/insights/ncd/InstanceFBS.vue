<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import InstanceFBSGroups from '../shared/InstanceFBSGroups.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()

</script>
<template>
<InstanceFBSGroups
 :description="props.description"
 :text-inside="props.textInside"
 :item-metadata="props.itemMetadata"
 :project-data="props.projectData"
/>
</template>