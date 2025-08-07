<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import HumanFemaleIcon from 'vue-material-design-icons/HumanFemale.vue';
import HumanMaleIcon from 'vue-material-design-icons/HumanMale.vue';
import useDownloadData from '@/composables/useDownloadData';
import useCountGender from '@/composables/useCountGender';
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

                <div class=" grid grid-cols-2 gap-5">
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Males</CardTitle>
                                <!-- <CardDescription></CardDescription> -->
                            </CardHeader>
                            <CardContent>
                                <div class="grid grid-cols-12 gap-5">
                                    <div class="grid col-span-1">
                                        <div>
                                            <HumanMaleIcon class="text-sky-500" :size="125" />
                                        </div>
                                
                                    
                                    </div> 
                                    <div class="grid-cols-11 ">
                                        <div class=" text-gray-500 font-bold flex">
                                            <div class="text-5xl ">
                                                {{ useCountGender(props.projectData).males }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Females</CardTitle>
                                <!-- <CardDescription>-</CardDescription> -->
                            </CardHeader>
                            <CardContent>
                                <div class="grid grid-cols-12 gap-5">
                                    <div class="grid col-span-1">
                                        <div>
                                            <HumanMaleIcon class="text-pink-500" :size="125" />
                                        </div>
                                
                                    
                                    </div> 
                                    <div class="grid-cols-11 ">
                                        <div class=" text-gray-500 font-bold flex">
                                            <div class="text-5xl ">
                                                {{ useCountGender(props.projectData).females }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>