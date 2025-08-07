<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import useCountMultiSelect from '@/composables/useCountMultiSelect';
import BarChart from '@/components/charts/apexcharts/BarChart.vue';
import PieChart from '@/components/charts/apexcharts/PieChart.vue';
import CountTable from '@/components/custom/tables/CountTable.vue';
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

const data = useCountMultiSelect(props.projectData)

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

            <div class="" v-if="data.countsPerValue">
                <div class="pt-2.5 pb-5 border-t">
                    Single Select Report
                </div>
                <div class="grid grid-cols-3 gap-5">
                    <div>
                        <BarChart :chart-decription="props.barChartDescription"
                            :chart-data="data.countsPerValue.value" />
                    </div>
                    <div>
                        <PieChart :chart-decription="props.pieChartDescription"
                            :chart-data="data.countsPerValue.value" />
                    </div>
                    <div>
                        <CountTable :table-data="data.countsPerValue.value" :header-text="props.tableHeaderText" />
                    </div>
                </div>
                <div class="py-5" />
                <!-- <DifferenceTable :counts="useCountSingleSelect(props.projectData)" :diff-table-title="props.diffTableTitle" :diff-table-description="props.diffTableDescription"/> -->
            </div>
            <div class="" v-if="data.countsPerCombination">
                <div class="pt-2.5 pb-5 border-t">
                    Multiple Select Report
                </div>
                <div class="">
                    <BarChart chart-decription="Number of respondents with a combo"
                        :chart-data="data.countsPerCombination.value" />
                </div>
                <div class="py-5" />
                <div class="w-1/2">
                    <PieChart chart-decription="% of respondents with a combo"
                        :chart-data="data.countsPerCombination.value" />
                </div>
                <div class="py-5" />
                <div>
                    <CountTable :table-data="data.countsPerCombination.value" :header-text="props.tableHeaderText" />
                </div>
                <div class="py-5" />
                <!-- <DifferenceTable :counts="useCountSingleSelect(props.projectData)" :diff-table-title="props.diffTableTitle" :diff-table-description="props.diffTableDescription"/> -->
            </div>
        </CardContent>
    </Card>
</template>