<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Activity, ArrowRight, BarChart3, CircleAlert, Download, FileSpreadsheet, GitMerge, Lightbulb, Lock, MapPin, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { type BreadcrumbItem } from '@/types';
import { describeCategorical, describeTrend } from '@/composables/useChartInsights';
import { useTier } from '@/composables/useTier';

const { isPro, isProPlus, canDownload, canDownloadPdf } = useTier();

interface LabelCount { label: string; count: number; }
interface DistrictFacilityCount { district: string; facility: string; count: number; }
interface Summary {
    headline: {
        total: number; facilities: number; districts: number; female: number; male: number;
        adolescents: number; with_contact: number; first_date: string | null; last_date: string | null;
    };
    by_facility: LabelCount[];
    by_district: LabelCount[];
    by_district_facility: DistrictFacilityCount[];
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

// District > facility as a treemap: facility and district used to be two
// separate flat bars with no way to see how facilities group within a
// district. ApexCharts' multi-series treemap mode draws one categorical hue
// per district, shaded within it by facility size — the right form for
// hierarchical magnitude across many nominal categories.
const categoricalPalette = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300', '#4a3aa7', '#e34948'];
const districts = computed(() => Array.from(new Set(props.summary.by_district_facility.map((r) => r.district))));
const treemapSeries = computed(() => districts.value.map((d) => ({
    name: d,
    data: props.summary.by_district_facility.filter((r) => r.district === d).map((r) => ({ x: r.facility, y: r.count })),
})));
interface ApexChartHandle { dataURI(options?: { scale?: number }): Promise<{ imgURI?: string }>; }
const treemapChartRef = ref<ApexChartHandle | null>(null);
const treemapOptions = computed(() => ({
    chart: { type: 'treemap', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
    colors: categoricalPalette.slice(0, districts.value.length),
    plotOptions: { treemap: { distributed: false, enableShades: true, shadeIntensity: 0.35 } },
    legend: { show: true, position: 'top', horizontalAlign: 'left', fontSize: '12px', labels: { colors: inkSecondary }, markers: { size: 6 } },
    dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 600 } },
    tooltip: { y: { formatter: (v: number) => `${v.toLocaleString()} clients` } },
}));

// ---- shared download/table/insight plumbing, reused by every chart below
// (same pattern as IndicatorDeepDive.vue's PNG/JPG/CSV buttons and
// Insights.vue's "show as table" toggle) ---------------------------------
async function downloadChartImage(chartRef: { value: ApexChartHandle | null }, filename: string, format: 'png' | 'jpg'): Promise<void> {
    const result = await chartRef.value?.dataURI({ scale: 2 });
    const pngUri = result?.imgURI ?? null;
    if (!pngUri) return;
    const fullName = `${filename}.${format}`;
    if (format === 'png') {
        const link = document.createElement('a');
        link.href = pngUri;
        link.download = fullName;
        link.click();

        return;
    }
    const img = new Image();
    img.onload = () => {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        ctx.fillStyle = '#fcfcfb';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/jpeg', 0.95);
        link.download = fullName;
        link.click();
    };
    img.src = pngUri;
}
function downloadTreemap(format: 'png' | 'jpg'): Promise<void> { return downloadChartImage(treemapChartRef, 'overview_district_facility', format); }

function downloadCsv(headers: string[], rows: (string | number | null)[][], filename: string): void {
    const escape = (v: string | number | null): string => {
        if (v === null || v === undefined) return '';
        const s = String(v);

        return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
    };
    const lines = [headers, ...rows].map((r) => r.map(escape).join(','));
    const blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${filename}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
}
function downloadTreemapCsv(): void {
    downloadCsv(['District', 'Facility', 'Clients'], props.summary.by_district_facility.map((r) => [r.district, r.facility, r.count]), 'overview_district_facility');
}
const showTreemapTable = ref(false);
const districtTotals = computed(() => {
    const totals = new Map<string, number>();
    for (const r of props.summary.by_district_facility) totals.set(r.district, (totals.get(r.district) ?? 0) + r.count);

    return [...totals.entries()].map(([label, count]) => ({ label, count }));
});
const treemapInsight = computed(() => describeCategorical(districtTotals.value.map((d) => ({ label: d.label, value: d.count })), { unit: 'count', noun: 'clients', dimension: 'district' }));

const facilityChartRef = ref<ApexChartHandle | null>(null);
function downloadFacilityChart(format: 'png' | 'jpg'): Promise<void> { return downloadChartImage(facilityChartRef, 'overview_by_facility', format); }
function downloadFacilityCsv(): void { downloadCsv(['Facility', 'Clients'], props.summary.by_facility.map((f) => [f.label, f.count]), 'overview_by_facility'); }
const showFacilityTable = ref(false);
const facilityInsight = computed(() => describeCategorical(props.summary.by_facility.map((f) => ({ label: f.label, value: f.count })), { unit: 'count', noun: 'clients', dimension: 'facility' }));

const districtChartRef = ref<ApexChartHandle | null>(null);
function downloadDistrictChart(format: 'png' | 'jpg'): Promise<void> { return downloadChartImage(districtChartRef, 'overview_by_district', format); }
function downloadDistrictCsv(): void { downloadCsv(['District', 'Clients'], props.summary.by_district.map((d) => [d.label, d.count]), 'overview_by_district'); }
const showDistrictTable = ref(false);
const districtInsight = computed(() => describeCategorical(props.summary.by_district.map((d) => ({ label: d.label, value: d.count })), { unit: 'count', noun: 'clients', dimension: 'district' }));

const ageChartRef = ref<ApexChartHandle | null>(null);
function downloadAgeChart(format: 'png' | 'jpg'): Promise<void> { return downloadChartImage(ageChartRef, 'overview_age_bands', format); }
function downloadAgeCsv(): void { downloadCsv(['Age band', 'Clients'], props.summary.age_bands.map((a) => [a.label, a.count]), 'overview_age_bands'); }
const showAgeTable = ref(false);
const ageInsight = computed(() => describeCategorical(props.summary.age_bands.map((a) => ({ label: a.label, value: a.count })), { unit: 'count', noun: 'clients', dimension: 'age band' }));

const serviceChartRef = ref<ApexChartHandle | null>(null);
function downloadServiceChart(format: 'png' | 'jpg'): Promise<void> { return downloadChartImage(serviceChartRef, 'overview_service_utilisation', format); }
function downloadServiceCsv(): void { downloadCsv(['Service', 'Clients'], props.summary.service_utilisation.map((s) => [s.label, s.count]), 'overview_service_utilisation'); }
const showServiceTable = ref(false);
const serviceInsight = computed(() => describeCategorical(props.summary.service_utilisation.map((s) => ({ label: s.label, value: s.count })), { unit: 'count', noun: 'clients', dimension: 'service' }));

const profileChartRef = ref<ApexChartHandle | null>(null);
function downloadProfileChart(format: 'png' | 'jpg'): Promise<void> { return downloadChartImage(profileChartRef, 'overview_client_profile', format); }
function downloadProfileCsv(): void { downloadCsv(['Profile', 'Clients'], props.summary.by_profile.map((p) => [p.label, p.count]), 'overview_client_profile'); }
const showProfileTable = ref(false);
const profileInsight = computed(() => describeCategorical(props.summary.by_profile.map((p) => ({ label: p.label, value: p.count })), { unit: 'count', noun: 'clients', dimension: 'profile' }));

const trendChartRef = ref<ApexChartHandle | null>(null);
function downloadTrendChart(format: 'png' | 'jpg'): Promise<void> { return downloadChartImage(trendChartRef, 'overview_first_seen_trend', format); }
function downloadTrendCsv(): void { downloadCsv(['Month', 'New clients'], props.summary.first_seen_trend.map((t) => [t.month, t.count]), 'overview_first_seen_trend'); }
const showTrendTable = ref(false);
const trendInsight = computed(() => (props.summary.first_seen_trend.length < 2 ? '' : describeTrend(props.summary.first_seen_trend.map((t) => t.month), props.summary.first_seen_trend.map((t) => t.count), { unit: 'count', noun: 'new clients' })));
function insightParagraphs(text: string): string[] { return text.split('\n\n').filter(Boolean); }

// "Download PDF" = the browser's own print flow (Save as PDF as the print
// destination) rather than a generated file - print CSS below hides every
// interactive control and the app's nav/sidebar, and keeps each chart/table
// intact across page breaks (print:break-inside-avoid), so what prints is a
// clean report, not a screenshot of the dashboard chrome.
const generatedOn = new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
function downloadPdf(): void {
    window.print();
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
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b] print:bg-white print:text-black">
            <div class="mx-auto max-w-[1500px] px-5 py-7 sm:px-8 lg:px-10 print:max-w-none print:px-0 print:py-0">

                <header class="flex flex-col justify-between gap-5 border-b border-[#d9ded7] pb-6 lg:flex-row lg:items-end print:break-inside-avoid">
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
                        <p class="mt-2 hidden text-xs text-[#60716d] print:block">Report generated {{ generatedOn }}</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row print:hidden">
                        <button v-if="canDownloadPdf" class="group inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-5 py-2.5 text-xs font-bold text-[#3c605b] transition hover:bg-white" title="Opens the print dialog — choose &quot;Save as PDF&quot; as the destination" @click="downloadPdf">
                            <Download class="size-4" />Download PDF
                        </button>
                        <Link href="/data6/indicators" class="group inline-flex items-center gap-2 rounded-full bg-[#173b3b] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#285655]">
                            <BarChart3 class="size-4" />AHP indicator dashboard
                            <ArrowRight class="size-3.5 transition group-hover:translate-x-0.5" />
                        </Link>
                        <Link v-if="isPro" href="/data6/reports" class="group inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-5 py-2.5 text-xs font-bold text-[#3c605b] transition hover:bg-white">
                            <FileSpreadsheet class="size-4" />M&amp;E reports
                            <ArrowRight class="size-3.5 transition group-hover:translate-x-0.5" />
                        </Link>
                        <Link v-if="isPro" href="/data6/insights" class="group inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-5 py-2.5 text-xs font-bold text-[#3c605b] transition hover:bg-white">
                            <Lightbulb class="size-4" />Insights
                            <ArrowRight class="size-3.5 transition group-hover:translate-x-0.5" />
                        </Link>
                        <Link v-else href="/data6/plans" class="group inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-5 py-2.5 text-xs font-bold text-[#82908a] transition hover:bg-white" title="Insights is a Pro feature — view plans">
                            <Lock class="size-4" />Insights
                        </Link>
                        <Link v-if="isProPlus" href="/data6/flow" class="group inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-5 py-2.5 text-xs font-bold text-[#3c605b] transition hover:bg-white">
                            <GitMerge class="size-4" />Patient flow
                            <ArrowRight class="size-3.5 transition group-hover:translate-x-0.5" />
                        </Link>
                    </div>
                </header>

                <!-- Stat tiles -->
                <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4 print:break-inside-avoid">
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
                <section v-if="totalQualityIssues > 0" class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 border border-[#eadfc9] bg-[#fbf6ea] px-4 py-3 text-xs text-[#8f6115] print:break-inside-avoid">
                    <span class="inline-flex items-center gap-1.5 font-bold"><CircleAlert class="size-4" />Data quality</span>
                    <span v-for="q in qualityIssues" :key="q.label">{{ q.label }}: <strong>{{ q.count.toLocaleString() }}</strong></span>
                </section>

                <!-- District > facility hierarchy -->
                <section class="mt-6 border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-bold text-[#244847]">Clients by district and facility</h2>
                            <p class="mt-0.5 text-[11px] text-[#788681]">Each block is one district; its facilities are shaded by size within it.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 print:hidden">
                            <template v-if="canDownload">
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadTreemap('png')"><Download class="size-3" />PNG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadTreemap('jpg')"><Download class="size-3" />JPG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadTreemapCsv"><Download class="size-3" />CSV</button>
                            </template>
                            <button class="rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="showTreemapTable = !showTreemapTable">{{ showTreemapTable ? 'Show chart' : 'Show as table' }}</button>
                        </div>
                    </div>
                    <VueApexCharts v-if="!showTreemapTable" ref="treemapChartRef" type="treemap" height="320" :options="treemapOptions" :series="treemapSeries" />
                    <div v-else class="mt-3 max-h-[320px] overflow-y-auto">
                        <table class="w-full text-xs" style="font-variant-numeric: tabular-nums">
                            <thead class="sticky top-0 bg-[#fcfcfb] text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1.5 text-left">District</th><th class="pb-1.5 text-left">Facility</th><th class="pb-1.5 text-right">Clients</th></tr></thead>
                            <tbody><tr v-for="row in summary.by_district_facility" :key="`${row.district}-${row.facility}`" class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">{{ row.district }}</td><td class="py-1.5 text-[#52514e]">{{ row.facility }}</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ row.count.toLocaleString() }}</td></tr></tbody>
                        </table>
                    </div>
                    <div v-if="treemapInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                        <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                        <p v-for="(para, i) in insightParagraphs(treemapInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                    </div>
                </section>

                <!-- Charts -->
                <section class="mt-4 grid gap-4 xl:grid-cols-2">
                    <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-sm font-bold text-[#244847]">Clients by facility</h2>
                            <div class="flex flex-wrap items-center gap-2 print:hidden">
                                <template v-if="canDownload">
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityChart('png')"><Download class="size-3" />PNG</button>
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityChart('jpg')"><Download class="size-3" />JPG</button>
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityCsv"><Download class="size-3" />CSV</button>
                                </template>
                                <button class="rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="showFacilityTable = !showFacilityTable">{{ showFacilityTable ? 'Show chart' : 'Show as table' }}</button>
                            </div>
                        </div>
                        <VueApexCharts v-if="!showFacilityTable" ref="facilityChartRef" type="bar" :height="Math.max(220, summary.by_facility.length * 30 + 60)"
                            :options="hbarOptions(summary.by_facility.map((f) => f.label), 'clients')"
                            :series="[{ name: 'Clients', data: summary.by_facility.map((f) => f.count) }]" />
                        <div v-else class="mt-3 max-h-[280px] overflow-y-auto">
                            <table class="w-full text-xs" style="font-variant-numeric: tabular-nums">
                                <thead class="sticky top-0 bg-[#fcfcfb] text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1.5 text-left">Facility</th><th class="pb-1.5 text-right">Clients</th></tr></thead>
                                <tbody><tr v-for="f in summary.by_facility" :key="f.label" class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">{{ f.label }}</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ f.count.toLocaleString() }}</td></tr></tbody>
                            </table>
                        </div>
                        <div v-if="facilityInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                            <p v-for="(para, i) in insightParagraphs(facilityInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                        </div>
                        <div v-if="canDownload" class="mt-3 border-t border-[#eef0eb] pt-3 print:hidden">
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
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h2 class="text-sm font-bold text-[#244847]">Clients by district</h2>
                                <div class="flex flex-wrap items-center gap-2 print:hidden">
                                    <template v-if="canDownload">
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadDistrictChart('png')"><Download class="size-3" />PNG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadDistrictChart('jpg')"><Download class="size-3" />JPG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadDistrictCsv"><Download class="size-3" />CSV</button>
                                    </template>
                                    <button class="rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="showDistrictTable = !showDistrictTable">{{ showDistrictTable ? 'Show chart' : 'Show as table' }}</button>
                                </div>
                            </div>
                            <VueApexCharts v-if="!showDistrictTable" ref="districtChartRef" type="bar" :height="Math.max(140, summary.by_district.length * 34 + 60)"
                                :options="hbarOptions(summary.by_district.map((d) => d.label), 'clients')"
                                :series="[{ name: 'Clients', data: summary.by_district.map((d) => d.count) }]" />
                            <div v-else class="mt-3 max-h-[220px] overflow-y-auto">
                                <table class="w-full text-xs" style="font-variant-numeric: tabular-nums">
                                    <thead class="sticky top-0 bg-[#fcfcfb] text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1.5 text-left">District</th><th class="pb-1.5 text-right">Clients</th></tr></thead>
                                    <tbody><tr v-for="d in summary.by_district" :key="d.label" class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">{{ d.label }}</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ d.count.toLocaleString() }}</td></tr></tbody>
                                </table>
                            </div>
                            <div v-if="districtInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                                <p v-for="(para, i) in insightParagraphs(districtInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                            </div>
                            <div v-if="canDownload" class="mt-2 flex flex-wrap gap-1.5 border-t border-[#eef0eb] pt-2 print:hidden">
                                <a v-for="d in summary.by_district" :key="d.label" :href="exportUrl('district', d.label)"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-[#cbd3cd] bg-white px-2.5 py-1 text-[11px] font-semibold text-[#3c605b] transition hover:border-[#173b3b] hover:bg-[#173b3b] hover:text-white"
                                    :title="`Download the ${d.count.toLocaleString()} record IDs for ${d.label}`">
                                    <Download class="size-3" />{{ d.label }}
                                    <span class="font-normal opacity-70" style="font-variant-numeric: tabular-nums">{{ d.count.toLocaleString() }}</span>
                                </a>
                            </div>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h2 class="text-sm font-bold text-[#244847]">Age distribution (age today)</h2>
                                <div class="flex flex-wrap items-center gap-2 print:hidden">
                                    <template v-if="canDownload">
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadAgeChart('png')"><Download class="size-3" />PNG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadAgeChart('jpg')"><Download class="size-3" />JPG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadAgeCsv"><Download class="size-3" />CSV</button>
                                    </template>
                                    <button class="rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="showAgeTable = !showAgeTable">{{ showAgeTable ? 'Show chart' : 'Show as table' }}</button>
                                </div>
                            </div>
                            <VueApexCharts v-if="!showAgeTable" ref="ageChartRef" type="bar" height="200"
                                :options="colOptions(summary.age_bands.map((a) => a.label))"
                                :series="[{ name: 'Clients', data: summary.age_bands.map((a) => a.count) }]" />
                            <table v-else class="mt-3 w-full text-xs" style="font-variant-numeric: tabular-nums">
                                <thead class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1.5 text-left">Age band</th><th class="pb-1.5 text-right">Clients</th></tr></thead>
                                <tbody><tr v-for="a in summary.age_bands" :key="a.label" class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">{{ a.label }}</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ a.count.toLocaleString() }}</td></tr></tbody>
                            </table>
                            <div v-if="ageInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                                <p v-for="(para, i) in insightParagraphs(ageInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-sm font-bold text-[#244847]">Clients who accessed each service</h2>
                                <p class="mt-0.5 text-[11px] text-[#788681]">A client can appear under several services — this shows utilisation, not a total.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 print:hidden">
                                <template v-if="canDownload">
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadServiceChart('png')"><Download class="size-3" />PNG</button>
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadServiceChart('jpg')"><Download class="size-3" />JPG</button>
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadServiceCsv"><Download class="size-3" />CSV</button>
                                </template>
                                <button class="rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="showServiceTable = !showServiceTable">{{ showServiceTable ? 'Show chart' : 'Show as table' }}</button>
                            </div>
                        </div>
                        <VueApexCharts v-if="!showServiceTable" ref="serviceChartRef" type="bar" :height="Math.max(260, summary.service_utilisation.length * 26 + 60)"
                            :options="hbarOptions(summary.service_utilisation.map((s) => s.label), 'clients')"
                            :series="[{ name: 'Clients', data: summary.service_utilisation.map((s) => s.count) }]" />
                        <div v-else class="mt-3 max-h-[320px] overflow-y-auto">
                            <table class="w-full text-xs" style="font-variant-numeric: tabular-nums">
                                <thead class="sticky top-0 bg-[#fcfcfb] text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1.5 text-left">Service</th><th class="pb-1.5 text-right">Clients</th></tr></thead>
                                <tbody><tr v-for="s in summary.service_utilisation" :key="s.label" class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">{{ s.label }}</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ s.count.toLocaleString() }}</td></tr></tbody>
                            </table>
                        </div>
                        <div v-if="serviceInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                            <p v-for="(para, i) in insightParagraphs(serviceInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h2 class="text-sm font-bold text-[#244847]">New clients first seen, by month</h2>
                                <div v-if="summary.first_seen_trend.length" class="flex flex-wrap items-center gap-2 print:hidden">
                                    <template v-if="canDownload">
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadTrendChart('png')"><Download class="size-3" />PNG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadTrendChart('jpg')"><Download class="size-3" />JPG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadTrendCsv"><Download class="size-3" />CSV</button>
                                    </template>
                                    <button class="rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="showTrendTable = !showTrendTable">{{ showTrendTable ? 'Show chart' : 'Show as table' }}</button>
                                </div>
                            </div>
                            <template v-if="summary.first_seen_trend.length">
                                <VueApexCharts v-if="!showTrendTable" ref="trendChartRef" type="line" height="220" :options="trendOptions"
                                    :series="[{ name: 'New clients', data: summary.first_seen_trend.map((t) => t.count) }]" />
                                <table v-else class="mt-3 w-full text-xs" style="font-variant-numeric: tabular-nums">
                                    <thead class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1.5 text-left">Month</th><th class="pb-1.5 text-right">New clients</th></tr></thead>
                                    <tbody><tr v-for="t in summary.first_seen_trend" :key="t.month" class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">{{ t.month }}</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ t.count.toLocaleString() }}</td></tr></tbody>
                                </table>
                                <div v-if="trendInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                    <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                                    <p v-for="(para, i) in insightParagraphs(trendInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                                </div>
                            </template>
                            <p v-else class="py-10 text-center text-xs text-[#898781]">No dated encounters found.</p>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h2 class="text-sm font-bold text-[#244847]">Client profile</h2>
                                <div class="flex flex-wrap items-center gap-2 print:hidden">
                                    <template v-if="canDownload">
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadProfileChart('png')"><Download class="size-3" />PNG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadProfileChart('jpg')"><Download class="size-3" />JPG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadProfileCsv"><Download class="size-3" />CSV</button>
                                    </template>
                                    <button class="rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="showProfileTable = !showProfileTable">{{ showProfileTable ? 'Show chart' : 'Show as table' }}</button>
                                </div>
                            </div>
                            <VueApexCharts v-if="!showProfileTable" ref="profileChartRef" type="bar" :height="Math.max(140, summary.by_profile.length * 28 + 60)"
                                :options="hbarOptions(summary.by_profile.map((p) => p.label), 'clients')"
                                :series="[{ name: 'Clients', data: summary.by_profile.map((p) => p.count) }]" />
                            <table v-else class="mt-3 w-full text-xs" style="font-variant-numeric: tabular-nums">
                                <thead class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1.5 text-left">Profile</th><th class="pb-1.5 text-right">Clients</th></tr></thead>
                                <tbody><tr v-for="p in summary.by_profile" :key="p.label" class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">{{ p.label }}</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ p.count.toLocaleString() }}</td></tr></tbody>
                            </table>
                            <div v-if="profileInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                                <p v-for="(para, i) in insightParagraphs(profileInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Education & marital as compact tables -->
                <section class="mt-4 grid gap-4 xl:grid-cols-2">
                    <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
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
                    <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
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
                <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4 print:hidden">
                    <Link href="/data6/indicators" class="group flex items-center justify-between border border-[#d9ded7] bg-[#173b3b] p-5 text-white transition hover:bg-[#285655]">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e9a18e]">For the M&E officer</p>
                            <h3 class="mt-1 font-serif text-xl">The 45 AHP indicators</h3>
                            <p class="mt-1 text-xs text-[#abc1b9]">Filterable by period, district, facility, gender and age band — with CSV export.</p>
                        </div>
                        <ArrowRight class="size-5 shrink-0 text-[#e9a18e] transition group-hover:translate-x-1" />
                    </Link>
                    <Link v-if="isPro" href="/data6/insights" class="group flex items-center justify-between border border-[#d9ded7] bg-[#fcfcfb] p-5 transition hover:bg-white">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">For the programme manager</p>
                            <h3 class="mt-1 font-serif text-xl text-[#173b3b]">Cross-service insights</h3>
                            <p class="mt-1 text-xs text-[#788681]">Linkage, co-utilisation and journeys — e.g. how many HIV-positive clients reached ART.</p>
                        </div>
                        <ArrowRight class="size-5 shrink-0 text-[#a6b1aa] transition group-hover:translate-x-1" />
                    </Link>
                    <Link v-else href="/data6/plans" class="group flex items-center justify-between border border-[#d9ded7] bg-[#fcfcfb] p-5 opacity-70 transition hover:bg-white">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">Pro feature</p>
                            <h3 class="mt-1 font-serif text-xl text-[#173b3b]">Cross-service insights</h3>
                            <p class="mt-1 text-xs text-[#788681]">Linkage, co-utilisation and journeys. Upgrade to Pro to unlock this page.</p>
                        </div>
                        <Lock class="size-5 shrink-0 text-[#a6b1aa]" />
                    </Link>
                    <Link v-if="isProPlus" href="/data6/flow" class="group flex items-center justify-between border border-[#d9ded7] bg-[#fcfcfb] p-5 transition hover:bg-white">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">For investigation</p>
                            <h3 class="mt-1 font-serif text-xl text-[#173b3b]">Patient flow &amp; tracking</h3>
                            <p class="mt-1 text-xs text-[#788681]">Follow one client across services; review cross-project identity links.</p>
                        </div>
                        <ArrowRight class="size-5 shrink-0 text-[#a6b1aa] transition group-hover:translate-x-1" />
                    </Link>
                    <Link v-else href="/data6/plans" class="group flex items-center justify-between border border-[#d9ded7] bg-[#fcfcfb] p-5 opacity-70 transition hover:bg-white">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">Pro+ feature</p>
                            <h3 class="mt-1 font-serif text-xl text-[#173b3b]">Patient flow &amp; tracking</h3>
                            <p class="mt-1 text-xs text-[#788681]">Follow one client across services. Upgrade to Pro+ to unlock this page.</p>
                        </div>
                        <Lock class="size-5 shrink-0 text-[#a6b1aa]" />
                    </Link>
                    <Link v-if="isProPlus" href="/data6/outreach" class="group flex items-center justify-between border border-[#d9ded7] bg-[#fcfcfb] p-5 transition hover:bg-white">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">For outreach</p>
                            <h3 class="mt-1 font-serif text-xl text-[#173b3b]">Outreach worklist</h3>
                            <p class="mt-1 text-xs text-[#788681]">Who's overdue right now across ART, PrEP and PNC — ready to chase up.</p>
                        </div>
                        <ArrowRight class="size-5 shrink-0 text-[#a6b1aa] transition group-hover:translate-x-1" />
                    </Link>
                    <Link v-else href="/data6/plans" class="group flex items-center justify-between border border-[#d9ded7] bg-[#fcfcfb] p-5 opacity-70 transition hover:bg-white">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#e2644b]">Pro+ feature</p>
                            <h3 class="mt-1 font-serif text-xl text-[#173b3b]">Outreach worklist</h3>
                            <p class="mt-1 text-xs text-[#788681]">Who's overdue right now across ART, PrEP and PNC. Upgrade to Pro+ to unlock this page.</p>
                        </div>
                        <Lock class="size-5 shrink-0 text-[#a6b1aa]" />
                    </Link>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
