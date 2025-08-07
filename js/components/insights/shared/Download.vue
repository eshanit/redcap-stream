<script setup lang='ts'>
import { type IMetadata, type IProjectData } from '@/types/IProject'
import useDownloadData from '@/composables/useDownloadData';
import Button from '@/components/ui/button/Button.vue';
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
    itemMetadata: IMetadata | undefined
    projectData: Array<IProjectData>
}>()

</script>
<template>
    <Card>
        <CardHeader>
            <CardTitle> <div v-html="props.itemMetadata?.element_label" /></CardTitle>
            <CardDescription>{{ props.description }}</CardDescription>
        </CardHeader>
        <CardContent>


            <div class="">
                <div>
                    {{ props.textInside }}
                </div>
                <div class="py-5">
                    <Button color="green" class="cursor-pointer" @click="useDownloadData(props.projectData, props.itemMetadata?.field_name)">
                        Download Data
                    </Button>
                </div>


            </div>
        </CardContent>
        <!-- <CardFooter>
                        Card Footer
                    </CardFooter> -->
    </Card>
</template>