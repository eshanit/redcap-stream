<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Check, Lock } from 'lucide-vue-next';
import { computed } from 'vue';
import { type BreadcrumbItem, type SharedData, type UserTier } from '@/types';
import { useTier } from '@/composables/useTier';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Plans', href: '/data6/plans' },
];

const { tier } = useTier();
const errorFlash = computed(() => usePage<SharedData>().props.flash.error);

interface PlanDef {
    key: UserTier;
    name: string;
    price: string;
    support: string;
    tagline: string;
    features: string[];
}

const plans: PlanDef[] = [
    {
        key: 'basic',
        name: 'Basic',
        price: '$250',
        support: '3 months support',
        tagline: 'The core descriptive picture of the programme.',
        features: [
            'Main dashboard (who is in the data)',
            'AHP indicator dashboard — all 45 indicators',
            'No download buttons (view-only)',
        ],
    },
    {
        key: 'pro',
        name: 'Pro',
        price: '$500',
        support: '6 months support',
        tagline: 'Everything in Basic, plus deeper analysis and exports.',
        features: [
            'Everything in Basic',
            'Cross-service insights (linkage, co-utilisation, journeys)',
            'M&E reports page (Excel-ready)',
            'PNG, JPG and CSV downloads on every chart and indicator',
            'No whole-page PDF export',
        ],
    },
    {
        key: 'pro_plus',
        name: 'Pro+',
        price: '$1000',
        support: '12 months support',
        tagline: 'Full access, including patient-level tracking.',
        features: [
            'Everything in Pro',
            'Patient flow & cross-project tracking',
            'Outreach worklist — who\'s overdue across ART, PrEP and PNC',
            'Whole-page PDF export on every report',
        ],
    },
];
</script>

<template>
    <Head title="AHP Plans" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b]">
            <div class="mx-auto max-w-[1200px] px-5 py-10 sm:px-8 lg:px-10">
                <div class="mb-3 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-[#e2644b]">
                    <span class="h-2 w-2 rounded-full bg-[#e2644b]" />AHP reporting platform
                </div>
                <h1 class="font-serif text-4xl leading-tight tracking-tight">Choose your plan</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#60716d]">
                    Three tiers of access to the AHP reporting platform. Your account is provisioned on one plan at a
                    time — the "Go" button is active on your current plan. To move to a different tier, contact us.
                </p>

                <div v-if="errorFlash" class="mt-5 max-w-2xl rounded-sm bg-[#fff1ed] px-4 py-3 text-sm text-[#b74f3d]">
                    {{ errorFlash }}
                </div>

                <section class="mt-8 grid gap-4 lg:grid-cols-3">
                    <div
                        v-for="plan in plans" :key="plan.key"
                        class="flex flex-col justify-between border bg-[#fcfcfb] p-6"
                        :class="plan.key === tier ? 'border-[#173b3b] ring-1 ring-[#173b3b]' : 'border-[#d9ded7]'">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <h2 class="font-serif text-2xl text-[#173b3b]">{{ plan.name }}</h2>
                                <span
                                    v-if="plan.key === tier"
                                    class="rounded-full bg-[#173b3b] px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">
                                    Your plan
                                </span>
                            </div>
                            <p class="mt-3 text-3xl font-semibold text-[#0b2c2c]">{{ plan.price }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-[#82908a]">{{ plan.support }}</p>
                            <p class="mt-3 text-xs leading-5 text-[#788681]">{{ plan.tagline }}</p>

                            <ul class="mt-5 space-y-2.5 border-t border-[#eef0eb] pt-5">
                                <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2 text-[13px] leading-5 text-[#365652]">
                                    <Check class="mt-0.5 size-3.5 shrink-0 text-[#1f7a73]" />{{ feature }}
                                </li>
                            </ul>
                        </div>

                        <div class="mt-6">
                            <Link
                                v-if="plan.key === tier"
                                href="/data6"
                                class="group flex w-full items-center justify-center gap-2 rounded-full bg-[#173b3b] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#285655]">
                                Go →
                            </Link>
                            <div v-else>
                                <button
                                    disabled
                                    class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-full border border-[#d9ded7] bg-[#f0efec] px-5 py-2.5 text-xs font-bold text-[#a6b1aa]">
                                    <Lock class="size-3.5" />Go →
                                </button>
                                <p class="mt-2 text-center text-[11px] leading-4 text-[#898781]">
                                    Your current plan is {{ plans.find((p) => p.key === tier)?.name }} — contact us to upgrade.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
