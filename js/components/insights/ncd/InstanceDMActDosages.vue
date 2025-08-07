<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import InstanceSingleSelect from '../shared/InstanceSingleSelect.vue';

const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>()

</script>
<template>
<InstanceSingleSelect
 :description="props.description"
 :text-inside="props.textInside"
 :item-metadata="props.itemMetadata"
 :project-data="props.projectData"
/>
</template>