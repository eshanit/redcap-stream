<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Activity, ArrowRight, BarChart3, CircleAlert, Download, FileSpreadsheet, GitMerge, MapPin, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { type BreadcrumbItem } from '@/types';

interface LabelCount { label: string; count: number; }
interface Summary {
    headline: {
        total: number; facilities: number; districts: number; female: number; male: number;
        adolescents: number; with_contact: number; first_date: string | null; last_date: string | null;
    };
    by_facility: LabelCount[];
    by_district: LabelCount[];
    age_bands: LabelCount[];
    by_profile: LabelCount[];
    by_education: LabelCount[];
    by_marital: LabelCount[];
    service_utilisation: LabelCount[];
    first_seen_trend: { month: string; count: number }[];
    data_quality: LabelCount[];
}

const props = defineProps<{ appTitle: string; summary: Summary }>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }, { title: 'AHP overview', href: '/data6' }];

const h = computed(() => props.summary.headline);
const femaleShare = computed(() => {
    const known = h.value.female + h.value.male;
    return known > 0 ? Math.round((h.value.female / known) * 100) : 0;
});
const adolescentShare = computed(() => (h.value.total > 0 ? Math.round((h.value.adolescents / h.value.total) * 100) : 0));
const qualityIssues = computed(() => props.summary.data_quality.filter((q) => q.count > 0));
const totalQualityIssues = computed(() => qualityIssues.value.reduce((s, q) => s + q.count, 0));

// dataviz reference palette: single-hue magnitude bars, chrome in ink tokens
const seriesBlue = '#2a78d6';
const inkMuted = '#898781';
const inkSecondary = '#52514e';
const gridHairline = '#e1e0d9';

function hbarOptions(categories: string[], tooltipLabel: string) {
    return {
        chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
        colors: [seriesBlue],
        plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4, borderRadiusApplication: 'end' } },
        dataLabels: { enabled: true, style: { colors: [inkSecondary], fontSize: '11px' }, offsetX: 28, formatter: (v: number) => v.toLocaleString() },
        grid: { borderColor: gridHairline, yaxis: { lines: { show: false } } },
        xaxis: { categories, labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: inkSecondary, fontSize: '12px' }, maxWidth: 180 } },
        legend: { show: false },
        tooltip: { y: { formatter: (v: number) => `${v.toLocaleString()} ${tooltipLabel}` } },
    };
}

function colOptions(categories: string[]) {
    return {
        chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
        colors: [seriesBlue],
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, borderRadiusApplication: 'end' } },
        dataLabels: { enabled: true, style: { colors: [inkSecondary], fontSize: '11px' }, offsetY: -18, formatter: (v: number) => v.toLocaleString() },
        grid: { borderColor: gridHairline, xaxis: { lines: { show: false } } },
        xaxis: { categories, labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: inkMuted, fontSize: '11px' } }, forceNiceScale: true },
        legend: { show: false },
        tooltip: { y: { formatter: (v: number) => `${v.toLocaleString()} clients` } },
    };
}

function exportUrl(dimension: 'facility' | 'district', value: string): string {
    return `/api/data6/records-export?dimension=${dimension}&value=${encodeURIComponent(value)}`;
}

const trendOptions = computed(() => ({
    chart: { type: 'line', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
    colors: [seriesBlue],
    stroke: { width: 2, curve: 'straight' },
    markers: { size: 3, strokeWidth: 2, strokeColors: '#fcfcfb', hover: { size: 6 } },
    grid: { borderColor: gridHairline, xaxis: { lines: { show: false } } },
    xaxis: {
        categories: props.summary.first_seen_trend.map((t) => t.month),
        labels: { style: { colors: inkMuted, fontSize: '10px' }, rotate: -45 },
        axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false },
    },
    yaxis: { labels: { style: { colors: inkMuted, fontSize: '11px' } }, min: 0, forceNiceScale: true },
    legend: { show: false },
    tooltip: { y: { formatter: (v: number) => `${v.toLocaleString()} new clients` } },
}));
</script>

<template>
    <Head title="AHP Overview" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b]">
            <div class="mx-auto max-w-[1500px] px-5 py-7 sm:px-8 lg:px-10">

                <header class="flex flex-col justify-between gap-5 border-b border-[#d9ded7] pb-6 lg:flex-row lg:items-end">
                    <div>
                        <div class="mb-3 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-[#e2644b]">
                            <span class="h-2 w-2 rounded-full bg-[#e2644b]" />{{ appTitle }}
                        </div>
                        <h1 class="font-serif text-4xl leading-tight tracking-tight">Who is in this data?</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-[#60716d]">
                            A descriptive overview of the FCH, OI/ART and OPD population — every count is unique
                            clients, deduplicated across the three projects.
                            <span v-if="h.first_date"> Service data runs <strong>{{ h.first_date }}</strong> to <strong>{{ h.last_date }}</strong>.</span>
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Link href="/data6/indicators" class="group inline-flex items-center gap-2 rounded-full bg-[#173b3b] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#285655]">
                            <BarChart3 class="size-4" />AHP indicator dashboard
                            <ArrowRight class="size-3.5 transition group-hover:translate-x-0.5" />
                        </Link>
                        <Link href="/data6/reports" class="group inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-5 py-2.5 text-xs font-bold text-[#3c605b] transition hover:bg-white">
                            <FileSpreadsheet class="size-4" />M&amp;E reports
                            <ArrowRight class="size-3.5 transition group-hover:translate-x-0.5" />
                        </Link>
                        <Link href="/data6/flow" class="group inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-5 py-2.5 text-xs font-bold text-[#3c605b] transition hover:bg-white">
                            <GitMerge class="size-4" />Patient flow
                            <ArrowRight class="size-3.5 transition group-hover:translate-x-0.5" />
                        </Link>
                    </div>
                </header>

                <!-- Stat tiles -->
                <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="border-l-4 border-[#e86d52] bg-[#fcfcfb] px-5 py-4">
                        <div class="flex items-center justify-between text-[#76827e]"><span class="text-xs font-bold uppercase tracking-wider">Registered clients</span><Users class="size-4" /></div>
                        <div class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ h.total.toLocaleString() }}</div>
                        <div class="mt-1 text-xs text-[#788681]">{{ h.with_contact.toLocaleString() }} with a contact number</div>
                    </div>
                    <div class="border-l-4 border-[#1f7a73] bg-[#fcfcfb] px-5 py-4">
                        <div class="flex items-center justify-between text-[#76827e]"><span class="text-xs font-bold uppercase tracking-wider">Adolescents 10–19</span><Activity class="size-4" /></div>
                        <div class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ h.adolescents.toLocaleString() }}</div>
                        <div class="mt-1 text-xs text-[#788681]">{{ adolescentShare }}% of registered clients (today's age)</div>
                    </div>
                    <div class="border-l-4 border-[#c58a32] bg-[#fcfcfb] px-5 py-4">
                        <div class="flex items-center justify-between text-[#76827e]"><span class="text-xs font-bold uppercase tracking-wider">Gender split</span><Users class="size-4" /></div>
                        <div class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ femaleShare }}% <span class="text-base font-normal text-[#60716d]">female</span></div>
                        <div class="mt-1 text-xs text-[#788681]">{{ h.female.toLocaleString() }} female · {{ h.male.toLocaleString() }} male</div>
                    </div>
                    <div class="border-l-4 border-[#3c6e91] bg-[#fcfcfb] px-5 py-4">
                        <div class="flex items-center justify-between text-[#76827e]"><span class="text-xs font-bold uppercase tracking-wider">Coverage</span><MapPin class="size-4" /></div>
                        <div class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ h.facilities }} <span class="text-base font-normal text-[#60716d]">facilities</span></div>
                        <div class="mt-1 text-xs text-[#788681]">across {{ h.districts }} districts</div>
                    </div>
                </section>

                <!-- Data quality strip -->
                <section v-if="totalQualityIssues > 0" class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 border border-[#eadfc9] bg-[#fbf6ea] px-4 py-3 text-xs text-[#8f6115]">
                    <span class="inline-flex items-center gap-1.5 font-bold"><CircleAlert class="size-4" />Data quality</span>
                    <span v-for="q in qualityIssues" :key="q.label">{{ q.label }}: <strong>{{ q.count.toLocaleString() }}</strong></span>
                </section>

                <!-- Charts -->
                <section class="mt-6 grid gap-4 xl:grid-cols-2">
                    <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <h2 class="text-sm font-bold text-[#244847]">Clients by facility</h2>
                        <VueApexCharts type="bar" :height="Math.max(220, summary.by_facility.length * 30 + 60)"
                            :options="hbarOptions(summary.by_facility.map((f) => f.label), 'clients')"
                            :series="[{ name: 'Clients', data: summary.by_facility.map((f) => f.count) }]" />
                        <div class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Download record IDs per facility (CSV)</p>
                            <div class="flex flex-wrap gap-1.5">
                                <a v-for="f in summary.by_facility" :key="f.label" :href="exportUrl('facility', f.label)"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-[#cbd3cd] bg-white px-2.5 py-1 text-[11px] font-semibold text-[#3c605b] transition hover:border-[#173b3b] hover:bg-[#173b3b] hover:text-white"
                                    :title="`Download the ${f.count.toLocaleString()} record IDs for ${f.label}`">
                                    <Download class="size-3" />{{ f.label }}
                                    <span class="font-normal opacity-70" style="font-variant-numeric: tabular-nums">{{ f.count.toLocaleString() }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Clients by district</h2>
                            <VueApexCharts type="bar" :height="Math.max(140, summary.by_district.length * 34 + 60)"
                                :options="hbarOptions(summary.by_district.map((d) => d.label), 'clients')"
                                :series="[{ name: 'Clients', data: summary.by_district.map((d) => d.count) }]" />
                            <div class="mt-2 flex flex-wrap gap-1.5 border-t border-[#eef0eb] pt-2">
                                <a v-for="d in summary.by_district" :key="d.label" :href="exportUrl('district', d.label)"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-[#cbd3cd] bg-white px-2.5 py-1 text-[11px] font-semibold text-[#3c605b] transition hover:border-[#173b3b] hover:bg-[#173b3b] hover:text-white"
                                    :title="`Download the ${d.count.toLocaleString()} record IDs for ${d.label}`">
                                    <Download class="size-3" />{{ d.label }}
                                    <span class="font-normal opacity-70" style="font-variant-numeric: tabular-nums">{{ d.count.toLocaleString() }}</span>
                                </a>
                            </div>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Age distribution (age today)</h2>
                            <VueApexCharts type="bar" height="200"
                                :options="colOptions(summary.age_bands.map((a) => a.label))"
                                :series="[{ name: 'Clients', data: summary.age_bands.map((a) => a.count) }]" />
                        </div>
                    </div>

                    <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <h2 class="text-sm font-bold text-[#244847]">Clients who accessed each service</h2>
                        <p class="mt-0.5 text-[11px] text-[#788681]">A client can appear under several services — this shows utilisation, not a total.</p>
                        <VueApexCharts type="bar" :height="Math.max(260, summary.service_utilisation.length * 26 + 60)"
                            :options="hbarOptions(summary.service_utilisation.map((s) => s.label), 'clients')"
                            :series="[{ name: 'Clients', data: summary.service_utilisation.map((s) => s.count) }]" />
                    </div>

                    <div class="flex flex-col gap-4">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">New clients first seen, by month</h2>
                            <VueApexCharts v-if="summary.first_seen_trend.length" type="line" height="220" :options="trendOptions"
                                :series="[{ name: 'New clients', data: summary.first_seen_trend.map((t) => t.count) }]" />
                            <p v-else class="py-10 text-center text-xs text-[#898781]">No dated encounters found.</p>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Client profile</h2>
                            <VueApexCharts type="bar" :height="Math.max(140, summary.by_profile.length * 28 + 60)"
                                :options="hbarOptions(summary.by_profile.map((p) => p.label), 'clients')"
                                :series="[{ name: 'Clients', data: summary.by_profile.map((p) => p.count) }]" />
                        </div>
                    </div>
                </section>

                <!-- Education & marital as compact tables -->
                <section class="mt-4 grid gap-4 xl:grid-cols-2">
                    <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <h2 class="text-sm font-bold text-[#244847]">Education level</h2>
                        <table class="mt-3 w-full text-xs">
                            <tbody>
                                <tr v-for="row in summary.by_education" :key="row.label" class="border-b border-[#eef0eb] last:border-0">
                                    <td class="py-1.5 text-[#52514e]">{{ row.label }}</td>
                                    <td class="py-1.5 text-right font-semibold text-[#0b2c2c]" style="font-variant-numeric: tabular-nums">{{ row.count.toLocaleString() }}</td>
                                    <td class="w-1/2 py-1.5 pl-3">
                                        <div class="h-2 rounded-sm bg-[#2a78d6]" :style="{ width: `${Math.max(2, (row.count / h.total) * 100)}%` }" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <h2 class="text-sm font-bold text-[#244847]">Marital status</h2>
                        <table class="mt-3 w-full text-xs">
                            <tbody>
                                <tr v-for="row in summary.by_marital" :key="row.label" class="border-b border-[#eef0eb] last:border-0">
                                    <td class="py-1.5 text-[#52514e]">{{ row.label }}</td>
                                    <td class="py-1.5 text-right font-semibold text-[#0b2c2c]" style="font-variant-numeric: tabular-nums">{{ row.count.toLocaleString() }}</td>
                                    <td class="w-1/2 py-1.5 pl-3">
                                        <div class="h-2 rounded-sm bg-[#2a78d6]" :style="{ width: `${Math.max(2, (row.count / h.total) * 100)}%` }" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Onward navigation -->
                <section class="mt-6 grid gap-3 sm:grid-cols-2">
                    <Link href="/data6/indicators" class="group flex items-center justify-between border border-[#d9ded7] bg-[#173b3b] p-5 text-white transition hover:bg-[#285655]">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e9a18e]">For the M&E officer</p>
                            <h3 class="mt-1 font-serif text-xl">The 45 AHP indicators</h3>
                            <p class="mt-1 text-xs text-[#abc1b9]">Filterable by period, district, facility, gender and age band — with CSV export.</p>
                        </div>
                        <ArrowRight class="size-5 shrink-0 text-[#e9a18e] transition group-hover:translate-x-1" />
                    </Link>
                    <Link href="/data6/flow" class="group flex items-center justify-between border border-[#d9ded7] bg-[#fcfcfb] p-5 transition hover:bg-white">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">For investigation</p>
                            <h3 class="mt-1 font-serif text-xl text-[#173b3b]">Patient flow &amp; tracking</h3>
                            <p class="mt-1 text-xs text-[#788681]">Follow one client across services; review cross-project identity links.</p>
                        </div>
                        <ArrowRight class="size-5 shrink-0 text-[#a6b1aa] transition group-hover:translate-x-1" />
                    </Link>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
