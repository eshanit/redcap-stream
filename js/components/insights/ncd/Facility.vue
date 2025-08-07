<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import BarChart from '@/components/charts/apexcharts/BarChart.vue';
import useDownloadData from '@/composables/useDownloadData';
import useCountFacility from '@/composables/useCountFacility';
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

// Function to download data
const downloadData = () => {
    useDownloadData(props.projectData, props.itemMetadata?.field_name);
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>{{ props.itemMetadata?.element_label }}</CardTitle>
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
                <div class="grid grid-cols-12 gap-5">
                    <div class="grid col-span-8">
                        <BarChart chart-decription="Number of Respondents"
                            :chart-data="useCountFacility(props.projectData)" />
                    </div>
                    <div class="grid col-span-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead>
                                    <tr class="w-full bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                        <th class="py-3 px-6 text-left">Facility</th>
                                        <th class="py-3 px-6 text-left">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-200"
                                        v-for="(count, facility) in useCountFacility(props.projectData)"
                                        :key="facility">
                                        <td class="py-3 px-6 text-left">{{ facility }}</td>
                                        <td class="py-3 px-6 text-left">{{ count }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>