<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import useInstanceYesNo from '@/composables/useInstanceYesNo';
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
    return  `Visit-${parseInt(instance)}`;
}

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
                    {{ useInstanceYesNo(props.projectData) }}
                </pre> -->
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto bg-white shadow-md rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-700">Instance</th>
                                <th class="px-4 py-2 text-left text-gray-700">Yes</th>
                                <th class="px-4 py-2 text-left text-gray-700">No</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(counts, instance, index) in useInstanceYesNo(props.projectData).summary.value"
                                :key="index" class="hover:bg-gray-50">
                                <td class="border px-4 py-2">{{ getInstanceLabel(instance) }}</td>
                                <td class="border px-4 py-2">{{ counts.yes }}</td>
                                <td class="border px-4 py-2">{{ counts.no }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </CardContent>
    </Card>
</template>