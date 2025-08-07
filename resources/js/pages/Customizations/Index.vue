<script setup lang='ts'>
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Sparkles } from 'lucide-vue-next';

const props = defineProps<{
    project: IProject,
    customProjectData: any[]
}>()

const packageRoot = computed(() => {
    if (props.project.project_id == 39) {
        return 'ncd_pplus'
    }
     if (props.project.project_id == 50) {
        return 'ahp'
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
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-2">
                <Heading 
                    :title="props.project.app_title" 
                    description="Customized Packages" 
                    class="pt-2"
                />
                <p class="text-gray-600 dark:text-gray-400 max-w-3xl">
                    Explore your customized packages and access detailed reports for each solution
                </p>
            </div>

            <div v-if="props.customProjectData.length > 0">
                <!-- Grid layout -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <Card 
                        v-for="customProject in customProjectData"
                        :key="customProject.path"
                        class="group relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:hover:shadow-xl dark:hover:shadow-gray-900/20 border border-gray-200 dark:border-gray-700"
                    >
                        <!-- Decorative elements -->
                        <div class="absolute top-0 right-0 w-16 h-16 rounded-bl-full bg-blue-500/10 dark:bg-blue-400/10 transition-all duration-500 group-hover:bg-blue-500/20"></div>
                        
                        <CardHeader class="pb-3">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-300">
                                    <Sparkles class="w-5 h-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg font-semibold text-gray-800 dark:text-white">
                                        {{ customProject.customization_name }}
                                    </CardTitle>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ customProject.description ? 'Package' : 'Custom solution' }}
                                    </p>
                                </div>
                            </div>
                        </CardHeader>
                        
                        <CardContent>
                            <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm min-h-[3rem]">
                                <span v-html="customProject.description || 'Detailed reports and analytics'"></span>
                            </p>
                            
                            <Link 
                                :href="`/packages/project/${project.project_id}/${customProject.path}`"
                                class="block"
                            >
                                <Button 
                                    variant="outline"
                                    class="w-full transition-all group-hover:bg-blue-600 group-hover:text-white border-gray-300 dark:border-gray-600 hover:border-blue-500"
                                >
                                    View Reports
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-full mb-5">
                    <Sparkles class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto" />
                </div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    No Custom Packages Yet
                </h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md">
                    You haven't created any custom packages yet. Start by creating your first customized solution.
                </p>
                <Button class="mt-6 px-6 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white shadow-md transition-all">
                    Create Package
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.card-enter-active {
  transition: all 0.3s ease-out;
}

.card-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

/* Smooth transitions for interactive elements */
a, button {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .grid {
    gap: 1rem;
  }
  
  .card {
    border-radius: 12px;
  }
}
</style>