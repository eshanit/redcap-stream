<script setup lang='ts'>
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    project: IProject,
    customProjectData: any[]
}>()

const packageRoot = computed(() => {
    if (props.project.project_id == 39) {
        return 'ncd_pplus'
    }
    return 'ncd'
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: props.project.app_title,
        href: '/' + packageRoot.value + '/project/' + props.project.project_id,
    },
    {
        title: 'Custom Packages',
        href: ''
    }
];

</script>
<template>

    <Head title="Customized Packaged Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Heading :title="props.project.app_title" description="Customized Packages" class="pt-5" />
            <div class="relative grid grid-cols-4 gap-5 py-12" v-if="props.customProjectData.length > 0">
                <div v-for="customProject in customProjectData">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ customProject.customization_name }}</CardTitle>
                            <!-- <CardDescription>Number of respondents in this project</CardDescription> -->
                        </CardHeader>
                        <CardContent>
                            <p class="text-gray-600"><span v-html="customProject.description" /></p>
                            <div class="py-2.5" />
                            <Link :href="`/packages/project/${project.project_id}/${customProject.path}`">
                            <Button
                                class="w-full cursor-pointer bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700">
                                View Reports
                            </Button>
                            </Link>
                        </CardContent>
                        <!-- <CardFooter>
                        Card Footer
                    </CardFooter> -->
                    </Card>
                </div>
            </div>

        </div>
    </AppLayout>
</template>