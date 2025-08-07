<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import useCountSingleSelect from '@/composables/useCountSingleSelects';
import BarChart from '@/components/charts/apexcharts/BarChart.vue';
import PieChart from '@/components/charts/apexcharts/PieChart.vue';
import CountTable from '@/components/custom/tables/CountTable.vue';
import DifferenceTable from '@/components/custom/tables/DifferenceTable.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const props = defineProps<{
    barChartDescription: string,
    pieChartDescription: string,
    tableHeaderText: string,
    diffTableTitle: string,
    diffTableDescription: string
    description: string,
    textInside: string,
    itemMetadata: IMetadata | undefined,
    projectData: Array<IProjectData>
}>();

const downloadData = () => {
    useDownloadData(props.projectData, props.itemMetadata?.field_name);
};

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
                <pre>
                    {{ useCountSingleSelect(props.projectData) }}
                </pre>
                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <BarChart :chart-decription="props.barChartDescription"
                            :chart-data="useCountSingleSelect(props.projectData)" />
                    </div>
                    <div>
                        <PieChart :chart-decription="props.pieChartDescription"
                            :chart-data="useCountSingleSelect(props.projectData)" />
                    </div>
                    <div>
                        <CountTable :table-data="useCountSingleSelect(props.projectData)" :header-text="props.tableHeaderText" />
                    </div>
                </div>
                <div class="py-5" />
                <DifferenceTable :counts="useCountSingleSelect(props.projectData)" :diff-table-title="props.diffTableTitle" :diff-table-description="props.diffTableDescription"/>
            </div>
        </CardContent>
    </Card>
</template>