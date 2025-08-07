<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import useDownloadData from '@/composables/useDownloadData';
import useCountMarital from '@/composables/useCountMarital';
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
                <!-- <pre>
                    {{ useCountMarital(props.projectData) }}
                </pre> -->
                <div class="py-5" />
                <div class="grid grid-cols-12 gap-5">
                    <div class="grid col-span-8">
                        <BarChart chart-decription="Martial Status counts"
                            :chart-data="useCountMarital(props.projectData)" />
                    </div>
                    <div class="grid col-span-4">
                        <PieChart chart-decription="% of Marital Status"
                            :chart-data="useCountMarital(props.projectData)" />
                    </div>
                </div>
                <div class="py-5" />
                <div>
                    <div>
                        Table showing the marital status distribution
                    </div>
                    <div class="py-2.5" />
                    <div>
                        <CountTable header-text="Marital Status" :table-data="useCountMarital(props.projectData)" />
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>