<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import useInstanceWeightGroups from '@/composables/useInstanceWeightGroups';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

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

const weightGroups = ['0-10', '11-20', '21-30', '31-40', '41-50', '51-60', '61-70', '70+'];
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
                <!-- <pre>
                    {{ useInstanceWeightGroups(props.projectData) }}
                </pre> -->

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto bg-white shadow-md rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-700">Instance</th>
                                <th v-for="label in weightGroups" :key="label" class="px-4 py-2 text-left text-gray-700">
                                    <span v-html="label" /> kgs
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(counts, instanceKey) in useInstanceWeightGroups(props.projectData).value"
                                :key="instanceKey" class="hover:bg-gray-50">
                                <td class="border px-4 py-2">{{ getInstanceLabel(instanceKey) }}</td>
                                <td v-for="label in weightGroups" :key="label" class="border px-4 py-2">
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