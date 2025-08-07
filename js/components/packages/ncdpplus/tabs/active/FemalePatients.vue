<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type IProject } from '@/types/IProject';
import { TriangleAlert, FileSearch, Users } from 'lucide-vue-next';
import useGetPatientsData from '@/composables/useGetPatientsData';
import LoadingSpinner from '@/components/custom/LoadingSpinner.vue';
import dataFemaleActivePatients from '@/data/ncdpplus/dataFemaleActivePatients';


const props = defineProps<{
    project: IProject,
    conditions: Array<Record<string, string>>
}>();


const form = useForm({
    condition: ''
});

const projectId = ref(props.project.project_id);
const condition = ref(form.condition);
const type = ref('femalepatients')
const tag = ref()



// Watch for condition changes if needed
watch(() => form.condition, (newVal) => {
    condition.value = newVal;
    tag.value = props.conditions.find(r => r.name == newVal)?.tag
});

const { itemData, isLoading, error } = useGetPatientsData(projectId,type ,condition, tag);

const selectedComponent = (condition: string) => {
    return dataFemaleActivePatients.find(d => d.name == condition)
}

</script>

<template>
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Condition Report <span class="text-pink-600">(Female Active Patients)</span></h2>
            <p class="text-gray-600">To view the report, please select a condition from the dropdown below.</p>
            
            <form @submit.prevent="form.post('/login')" class="mt-4">
                <Select v-model="form.condition">
                    <SelectTrigger class="w-full max-w-md">
                        <SelectValue placeholder="Select a condition" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="condition in props.conditions" :key="condition.name" :value="condition.name">
                            <div class="py-2.5 cursor-pointer flex items-center gap-2" v-html="condition.label"></div>
                        </SelectItem>
                    </SelectContent>
                </Select>
            </form>
        </div>

        <div v-if="form.condition" class="bg-white p-6 rounded-xl shadow-sm border">
            <div v-if="isLoading" class="flex items-center justify-center py-12">
                <LoadingSpinner />
            </div>

            <div v-else>
                <div v-if="itemData[form.condition] && itemData[form.condition].length > 0" class="space-y-6">
                    <!-- Respondent Count Card -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-100">
                        <div class="flex items-center gap-4">
                            <div class="bg-blue-100 p-3 rounded-full">
                                <Users class="text-blue-600 w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-600">Total Respondents</h3>
                                <p class="text-2xl font-bold text-gray-800">
                                    {{ itemData[form.condition].length }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Main Component -->
                    <div class="border rounded-lg p-4 bg-white">
                        <component 
                            :is="selectedComponent(form.condition)?.component"
                            v-bind="selectedComponent(form.condition)?.props"
                            :description="selectedComponent(form.condition)?.description || '-'"
                            :text-inside="selectedComponent(form.condition)?.textInside || '-'"
                            :patient-data="itemData[form.condition]" 
                            indicator="active"
                        />
                    </div>
                </div>

                <!-- No Data Section -->
                <div v-else class="flex flex-col items-center justify-center p-8 border-2 border-dashed rounded-xl bg-gray-50">
                    <div class="bg-red-100 p-4 rounded-full mb-4">
                        <FileSearch class="w-8 h-8 text-red-500" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-1">No Data Found</h3>
                    <p class="text-gray-600 text-center max-w-md">
                        There was no data matching your selected condition. Try selecting a different condition.
                    </p>
                </div>
            </div>
        </div>

        <div v-if="!form.condition" class="bg-white p-8 rounded-xl shadow-sm border border-dashed flex flex-col items-center justify-center text-center">
            <div class="bg-yellow-100 p-4 rounded-full mb-4">
                <TriangleAlert class="w-8 h-8 text-yellow-600" />
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">No Condition Selected</h3>
            <p class="text-gray-600 max-w-md">
                Please select a condition from the dropdown above to view insights and analytics.
            </p>
        </div>
    </div>
</template>