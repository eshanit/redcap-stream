<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import HBa1cTrendLineChart from '@/components/charts/apexcharts/ncd/HBa1cTrendLineChart.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'

const props = defineProps<{
    respondentData: any
}>()
</script>
<template>
    <!-- <pre>
    {{ respondentData }}
</pre> -->
    <Card>
        <CardHeader>
            <CardTitle>{{ props.respondentData.record }} </CardTitle>
            <CardDescription> HBCa1c</CardDescription>
        </CardHeader>
        <CardContent>
            <HBa1cTrendLineChart :record-data="props.respondentData" />
        </CardContent>
    </Card>
</template>