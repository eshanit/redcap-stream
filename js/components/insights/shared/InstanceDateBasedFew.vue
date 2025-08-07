<script setup lang="ts">
import { ref, computed } from 'vue';
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import useInstanceCountRecordsPerYear from '@/composables/useInstanceCountRecordsPerYear';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const props = defineProps<{
    mostCardTitle: string;
    currentCardTitle: string;
    lineChartDescription: string;
    description: string;
    textInside: string;
    itemMetadata: IMetadata | undefined;
    projectData: Array<IProjectData>;
}>();

// Call the composable directly to get the reactive instance counts
const instanceCounts = useInstanceCountRecordsPerYear(props.projectData);

// Function to download data
const downloadData = () => {
    useDownloadData(props.projectData, props.itemMetadata?.field_name);
};

const getInstanceLabel = (instance: string | number): string => {
    return `Visit-${instance}`;
};

// Get all unique years from the data
const years = computed(() => {
    const allYears = new Set<string>();
    const countsData = instanceCounts.value; // Access the ref's value
    
    if (countsData) {
        for (const instance in countsData) {
            if (Object.prototype.hasOwnProperty.call(countsData, instance)) {
                const instanceData = countsData[instance];
                if (instanceData) {
                    for (const year in instanceData) {
                        if (Object.prototype.hasOwnProperty.call(instanceData, year)) {
                            allYears.add(year);
                        }
                    }
                }
            }
        }
    }
    return Array.from(allYears).sort();
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
            <div>
                <div>{{ props.textInside }}</div>
                <div class="py-5">
                    <Button color="green" class="cursor-pointer" @click="downloadData">
                        Download Data
                    </Button>
                </div>

                <div class="" v-if="props.projectData?.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto bg-white shadow-md rounded-lg">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-gray-700">Instance</th>
                                    <th v-for="year in years" :key="year" class="px-4 py-2 text-left text-gray-700">
                                        {{ year }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(instanceData, instance) in instanceCounts" :key="instance" class="hover:bg-gray-50">
                                    <td class="border px-4 py-2">{{ getInstanceLabel(instance) }}</td>
                                    <td v-for="year in years" :key="year" class="border px-4 py-2">
                                        {{ instanceData ? instanceData[year] || 0 : 0 }}
                                        <!-- Display count or 0 if not found -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="py-2.5" />
                </div>
            </div>

        </CardContent>
    </Card>
</template>