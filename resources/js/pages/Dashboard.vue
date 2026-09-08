<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Link
                href="/data6/plans"
                class="group flex items-center justify-between border border-[#d9ded7] bg-[#173b3b] p-6 text-white transition hover:bg-[#285655]">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e9a18e]">AHP reporting</p>
                    <h2 class="mt-1 font-serif text-2xl">Continue to the AHP reporting platform</h2>
                    <p class="mt-1 text-sm text-[#abc1b9]">View your plan and enter the dashboard, indicators, insights and reports.</p>
                </div>
                <ArrowRight class="size-6 shrink-0 text-[#e9a18e] transition group-hover:translate-x-1" />
            </Link>
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
