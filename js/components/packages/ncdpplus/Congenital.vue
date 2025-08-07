<script setup lang="ts">
import { type IEnrollment } from "@/types/IProject";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import QuarterChart from "@/components/charts/apexcharts/ncdpplus/Heart/Congenital/QuarterChart.vue";
import FacilityQuarterChart from "@/components/charts/apexcharts/ncdpplus/Heart/Congenital/FacilityQuarterChart.vue";
import ConditionEnrolled from "@/components/tables/agtables/ncdpplus/coreinsights/ConditionEnrolled.vue";
import { Users, Activity, MapPin, Table } from 'lucide-vue-next';

const props = defineProps<{
    description: string
    textInside: string,
    patientData: IEnrollment[],
    indicator: string
}>()

const totalRespondents = props.patientData?.length || 0;
</script>
<template>
      <Card class="border-0 shadow-sm">
        <CardHeader class="pb-3 border-b">
            <div class="flex justify-between items-start">
                <div>
                    <CardTitle class="text-lg font-semibold text-gray-800">
                        {{ props.description }}
                    </CardTitle>
                    <CardDescription class="mt-1 text-gray-600">
                        {{ props.textInside }}
                    </CardDescription>
                </div>
                <div class="flex items-center gap-2 bg-blue-50 px-3 py-2 rounded-lg">
                    <Users class="w-4 h-4 text-blue-600" />
                    <span class="text-sm font-medium text-gray-700">
                        {{ totalRespondents }} {{ totalRespondents === 1 ? 'Respondent' : 'Respondents' }}
                    </span>
                </div>
            </div>
        </CardHeader>
        
        <CardContent class="pt-6 space-y-6">
            <!-- Overview Section -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <Activity class="w-5 h-5 text-blue-600" />
                    <h3 class="font-medium text-gray-800">Quarterly Patient Overview</h3>
                </div>
                <QuarterChart 
                    :data="props.patientData" 
                    :description="props.description"
                    class="border rounded-lg p-4 bg-gray-50"
                />
            </div>
            
            <!-- Facility Breakdown Section -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <MapPin class="w-5 h-5 text-blue-600" />
                    <h3 class="font-medium text-gray-800">Facility Distribution</h3>
                </div>
                <FacilityQuarterChart  
                    :data="props.patientData" 
                    :description="props.description"
                    class="border rounded-lg p-4 bg-gray-50"
                />
            </div>
                   <!-- Table Section -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <Table class="w-5 h-5 text-blue-600" />
                    <h3 class="font-medium text-gray-800">{{ props.description }}</h3>
                </div>
                <ConditionEnrolled :all-results="props.patientData" :indicator="props.indicator" />
            </div>
        </CardContent>
    </Card>
</template>