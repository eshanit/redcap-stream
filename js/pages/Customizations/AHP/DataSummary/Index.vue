<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Button from '@/components/ui/button/Button.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { type IProject } from '@/types/IProject';

const props = defineProps<{
  project: IProject,
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: props.project.app_title,
    href: '/project/' + props.project.project_id,
  },
  {
    title: 'Custom Packages',
    href: '/packages/project/' + props.project.project_id + '/dashboard',
  },
  {
    title: 'Service Based Indicators',
    href: ''
  }
];

</script>
<template>
    
  <Head title="AHP Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
     <div class="max-w-7xl mx-auto mt-10">
      <!-- Header -->
      <div class="mb-10 text-center">
        <h1
          class="text-4xl md:text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600 mb-3">
          Data management report
        </h1>
        <p class="text-gray-600 max-w-3xl mx-auto">
          Providing a management report to help monitoring the overall performance/progress of data collection and quality of all the study’s teams and regions
        </p>
      </div>
      </div>
  </AppLayout>
</template>