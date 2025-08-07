<script setup lang="ts">
import { computed } from 'vue';
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import useInstanceSingleSelect from '@/composables/useInstanceSingleSelect';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import useTransformSelect from '@/composables/useTransformSelect';



const props = defineProps<{
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>();

const downloadData = () => {
    useDownloadData(props.projectData, props.itemMetadata?.field_name);
};

const getInstanceLabel = (instance: any): string => {
    return `Visit-${parseInt(instance)}`;
}

const labels = computed(() => {
    if (props.projectData.length > 0 && props.projectData[0].element_enum) {
        // Assuming useTransformSelect is available in this component or imported
        const transformedLabels = useTransformSelect(props.projectData[0].element_enum);
        return transformedLabels.map(label => label.name); // Extract just the names
    }
    return [];
});

</script>
<template>
    <Card>
        <CardHeader>
            <CardTitle>
                <div v-html="props.itemMetadata?.element_label" />
            </CardTitle>
            <CardDescription>{{ props.description }}</CardDescription>
        </CardHeader>
        <CardContent>
            <div>{{ props.textInside }}</div>
            <div class="py-5">
                <Button color="green" class="cursor-pointer" @click="downloadData">
                    Download Data
                </Button>
            </div>
            <div class="" v-if="props.projectData?.length">
            
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto bg-white shadow-md rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-700">Instance</th>
                                <th v-for="label in labels" :key="label" class="px-4 py-2 text-left text-gray-700">
                                    <span v-html="label" />
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(counts, instanceKey) in useInstanceSingleSelect(props.projectData).value" :key="instanceKey" class="hover:bg-gray-50">
                                <td class="border px-4 py-2">{{ getInstanceLabel(instanceKey) }}</td>
                                <td v-for="label in labels" :key="label" class="border px-4 py-2">
                                    {{ counts[label] || 0 }} <!-- Display count or 0 if not found -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </CardContent>
    </Card>
</template>