<script setup lang="ts">
import { type IMetadata, type IProjectData } from '@/types/IProject';
import Button from '@/components/ui/button/Button.vue';
import { X, Check } from 'lucide-vue-next';
import useDownloadData from '@/composables/useDownloadData';
import useCountYesNo from '@/composables/useCountYesNo';
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


                <div class=" grid grid-cols-2 gap-5">
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>Yes</CardTitle>
                                <CardDescription>
                                    Number of respondents who answered yes.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>


                                <div class="flex gap-5">
                                    <div>
                                        <Check class="ml-auto size-18" color="red" />
                                    </div>
                                    <div class="flex gap-1">
                                        <div class="text-sky-500 text-4xl font-semibold">
                                            {{ useCountYesNo(props.projectData).yes }}
                                        </div>
                                        <div class="border-r" />
                                        <div class="text-orange-500 text-xl font-semibold">
                                            {{ useCountYesNo(props.projectData).pyes }}%
                                        </div>
                                    </div>

                                </div>
                            </CardContent>
                            <!-- <CardFooter>
                        Card Footer
                    </CardFooter> -->
                        </Card>
                    </div>
                    <div>
                        <Card>
                            <CardHeader>
                                <CardTitle>No</CardTitle>
                                <CardDescription>Number of respondents who answered no.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>


                                <div class="flex gap-5">
                                    <div>
                                        <X class="ml-auto size-18" color="red" />
                                    </div>
                                    <div class="flex gap-1">
                                        <div class="text-sky-500 text-4xl font-semibold">
                                            {{ useCountYesNo(props.projectData).no }}
                                        </div>
                                        <div class="border-r" />
                                        <div class="text-orange-500 text-xl font-semibold">
                                            {{ useCountYesNo(props.projectData).pno }}%
                                        </div>
                                    </div>

                                </div>
                            </CardContent>
                            <!-- <CardFooter>
                        Card Footer
                    </CardFooter> -->
                        </Card>
                    </div>
                </div>

            </div>
        </CardContent>
    </Card>
</template>