<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, CircleAlert, Download, FlaskConical, HelpCircle, Lightbulb, RefreshCw } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { type BreadcrumbItem } from '@/types';
import { describeCategorical, describeSexSplit, describeTrend, type Unit } from '@/composables/useChartInsights';
import ArtCascadeAnalysis from '@/pages/Data6/analysis/ArtCascadeAnalysis.vue';
import HtsReconciliationAnalysis from '@/pages/Data6/analysis/HtsReconciliationAnalysis.vue';

interface Bucket { label: string; value: number | null; numerator?: number; denominator?: number; cumulative?: number; }
interface SexBucket { label: string; male: number | null; female: number | null; }
interface DeepDive {
    code: string; key: string; label: string; group: string; level: string;
    type: 'count' | 'percent' | 'sum'; status: string; note: string | null; no_period: boolean;
    total: { value: number | null; numerator?: number; denominator?: number } | null;
    by: Record<string, Bucket[]> & {
        month_sex?: SexBucket[]; facility_sex?: SexBucket[]; district_sex?: SexBucket[]; service_point_sex?: SexBucket[];
    };
}
interface Meta {
    id: number; code: string; key: string; group: string; level: string; type: string;
    status: string; label: string; definition: string; variables: string | null;
    disaggregation: string[]; note: string | null; no_period?: boolean; analysis?: string[] | null;
}

const props = defineProps<{ appTitle: string; meta: Meta; method: string | null; methodCommon: string | null }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'AHP overview', href: '/data6' },
    { title: 'Indicators', href: '/data6/indicators' },
    { title: props.meta.code, href: `/data6/indicators/${props.meta.code}` },
];

// ---- period ---------------------------------------------------------------
function fmt(d: Date): string { return d.toISOString().slice(0, 10); }
function quarterRange(offset: number): { from: string; to: string } {
    const now = new Date();
    const q = Math.floor(now.getMonth() / 3) + offset;
    return { from: fmt(new Date(now.getFullYear(), q * 3, 1)), to: fmt(new Date(now.getFullYear(), q * 3 + 3, 0)) };
}
const presets = [
    { key: 'this_quarter', label: 'This quarter', range: () => quarterRange(0) },
    { key: 'last_quarter', label: 'Last quarter', range: () => quarterRange(-1) },
    { key: 'ytd', label: 'Year to date', range: () => ({ from: `${new Date().getFullYear()}-01-01`, to: fmt(new Date()) }) },
    { key: 'last_12m', label: 'Last 12 months', range: () => ({ from: fmt(new Date(Date.now() - 365 * 864e5)), to: fmt(new Date()) }) },
    { key: 'custom', label: 'Custom', range: null },
];
const activePreset = ref('ytd');
const from = ref(`${new Date().getFullYear()}-01-01`);
const to = ref(fmt(new Date()));
function applyPreset(key: string): void {
    activePreset.value = key;
    const preset = presets.find((p) => p.key === key);
    if (preset?.range) { const r = preset.range(); from.value = r.from; to.value = r.to; load(); }
}

// ---- data -----------------------------------------------------------------
const loading = ref(false);
const error = ref('');
const dive = ref<DeepDive | null>(null);

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/api/data6/indicators/${props.meta.code}/deep?from=${from.value}&to=${to.value}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(`Request failed (${response.status})`);
        dive.value = (await response.json()).indicator;
    } catch {
        error.value = 'Could not compute this indicator — try Refresh.';
    } finally {
        loading.value = false;
    }
}
onMounted(load);

// ---- derived --------------------------------------------------------------
const isPercent = computed(() => props.meta.type === 'percent');
function fmtVal(v: number | null | undefined): string {
    if (v === null || v === undefined) return '—';
    return isPercent.value ? `${v}%` : v.toLocaleString();
}
const totalText = computed(() => fmtVal(dive.value?.total?.value));
const monthTrend = computed(() => (dive.value?.by?.month ?? []).filter((b) => b.label !== 'all-time'));
const buckets = (dim: string) => dive.value?.by?.[dim] ?? [];

const seriesBlue = '#2a78d6';
const inkMuted = '#898781';
const inkSecondary = '#52514e';
const gridHairline = '#e1e0d9';

const goldLine = '#c58a32';

// Hybrid view: bars = the monthly value; line = a running total. For a
// count of unique clients ("distinct" indicators like AHP001) the backend
// computes cumulative as a running UNION of records seen so far, so it
// reconciles to the period total — summing each month's distinct count
// would double-count a client who returns in a later month. For a rate,
// the line is the running rate (cumulative numerator / denominator) so it
// stays on the same 0-100 scale as the bars.
const cumulativeLabel = computed(() => (isPercent.value ? 'Running rate' : 'Cumulative total'));
const cumulativeSeries = computed(() => {
    if (isPercent.value) {
        let runNum = 0;
        let runDen = 0;

        return monthTrend.value.map((b) => {
            runNum += b.numerator ?? 0;
            runDen += b.denominator ?? 0;

            return runDen > 0 ? Math.round((runNum / runDen) * 1000) / 10 : null;
        });
    }

    return monthTrend.value.map((b) => b.cumulative ?? 0);
});

// vue3-apexcharts' own typings don't satisfy InstanceType<typeof X>, so the
// template ref is typed against the handful of methods this page actually calls.
interface ApexChartHandle {
    toggleSeries(seriesName: string): void;
    dataURI(options?: { scale?: number }): Promise<{ imgURI?: string }>;
}
const trendChartRef = ref<ApexChartHandle | null>(null);
const barsVisible = ref(true);
const lineVisible = ref(true);

const trendOptions = computed(() => ({
    chart: {
        id: 'indicator-trend',
        stacked: false,
        toolbar: { show: false },
        fontFamily: 'system-ui, sans-serif',
        animations: { enabled: false },
    },
    colors: [seriesBlue, goldLine],
    stroke: { width: [0, 2.5], curve: 'straight' },
    markers: { size: [0, 4], strokeWidth: 2, strokeColors: '#fcfcfb', hover: { size: 6 } },
    plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, borderRadiusApplication: 'end' } },
    dataLabels: {
        enabled: true,
        enabledOnSeries: [0],
        offsetY: -18,
        style: { colors: [inkSecondary], fontSize: '11px' },
        formatter: (v: number) => (isPercent.value ? `${v}%` : v.toLocaleString()),
    },
    grid: { borderColor: gridHairline, xaxis: { lines: { show: false } } },
    xaxis: {
        categories: monthTrend.value.map((b) => b.label),
        labels: { style: { colors: inkMuted, fontSize: '11px' } },
        axisBorder: { color: '#c3c2b7' },
        axisTicks: { show: false },
    },
    yaxis: isPercent.value
        ? [{ labels: { style: { colors: inkMuted, fontSize: '11px' } }, min: 0, max: 100, forceNiceScale: true }]
        : [
              { seriesName: props.meta.label, labels: { style: { colors: inkMuted, fontSize: '11px' } }, forceNiceScale: true, min: 0 },
              { seriesName: cumulativeLabel.value, opposite: true, labels: { style: { colors: goldLine, fontSize: '11px' } }, forceNiceScale: true, min: 0 },
          ],
    legend: {
        show: true, position: 'top', horizontalAlign: 'left', fontSize: '12px',
        labels: { colors: '#52514e' }, markers: { size: 6 },
        onItemClick: { toggleDataSeries: true },
    },
    tooltip: {
        shared: true, intersect: false,
        y: {
            formatter: (v: number | null, opts: { seriesIndex: number; dataPointIndex: number }) => {
                if (v === null) return '—';
                if (opts.seriesIndex === 0) {
                    const b = monthTrend.value[opts.dataPointIndex];

                    return isPercent.value && b?.numerator !== undefined ? `${v}% (${b.numerator}/${b.denominator})` : (isPercent.value ? `${v}%` : v.toLocaleString());
                }

                return isPercent.value ? `${v}%` : v.toLocaleString();
            },
        },
    },
}));
const trendSeries = computed(() => [
    { name: props.meta.label, type: 'column', data: monthTrend.value.map((b) => b.value ?? 0) },
    { name: cumulativeLabel.value, type: 'line', data: cumulativeSeries.value },
]);

function toggleBars(): void {
    trendChartRef.value?.toggleSeries(props.meta.label);
    barsVisible.value = !barsVisible.value;
}
function toggleLine(): void {
    trendChartRef.value?.toggleSeries(cumulativeLabel.value);
    lineVisible.value = !lineVisible.value;
}

async function downloadChartImage(chartRef: typeof trendChartRef, suffix: string, format: 'png' | 'jpg'): Promise<void> {
    const result = await chartRef.value?.dataURI({ scale: 2 });
    const pngUri = result?.imgURI ?? null;
    if (!pngUri) return;

    const filename = `${props.meta.code}_${suffix}_${from.value}_${to.value}.${format}`;

    if (format === 'png') {
        triggerDownload(pngUri, filename);

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
        triggerDownload(canvas.toDataURL('image/jpeg', 0.95), filename);
    };
    img.src = pngUri;
}
function triggerDownload(dataUri: string, filename: string): void {
    const link = document.createElement('a');
    link.href = dataUri;
    link.download = filename;
    link.click();
}
function downloadChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(trendChartRef, 'trend', format);
}

// ---- CSV data export, one function per chart's underlying data ------------
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
    link.download = filename;
    link.click();
    URL.revokeObjectURL(link.href);
}
function csvFilename(suffix: string): string {
    return `${props.meta.code}_${suffix}_${from.value}_${to.value}.csv`;
}
function downloadTrendCsv(): void {
    const headers = isPercent.value
        ? ['Month', 'Value (%)', 'Numerator', 'Denominator', cumulativeLabel.value]
        : ['Month', 'Value', cumulativeLabel.value];
    const rows = monthTrend.value.map((b, i) => (isPercent.value
        ? [b.label, b.value, b.numerator ?? '', b.denominator ?? '', cumulativeSeries.value[i]]
        : [b.label, b.value, cumulativeSeries.value[i]]));
    downloadCsv(headers, rows, csvFilename('trend'));
}
function downloadBucketCsv(items: Bucket[], dimLabel: string, suffix: string): void {
    const headers = isPercent.value ? [dimLabel, 'Value (%)', 'Numerator', 'Denominator'] : [dimLabel, 'Value'];
    const rows = items.map((b) => (isPercent.value ? [b.label, b.value, b.numerator ?? '', b.denominator ?? ''] : [b.label, b.value]));
    downloadCsv(headers, rows, csvFilename(suffix));
}
function downloadSexBucketCsv(items: SexBucket[], dimLabel: string, suffix: string): void {
    downloadCsv([dimLabel, 'Male', 'Female'], items.map((b) => [b.label, b.male, b.female]), csvFilename(suffix));
}

// ---- sex-split charts (monthly trend, facility, district, service point) --
const seriesOrange = '#eb6834';
const hasSexDim = computed(() => props.meta.disaggregation.includes('sex'));
const monthBySex = computed(() => dive.value?.by?.month_sex ?? []);
const facilitySex = computed(() => dive.value?.by?.facility_sex ?? []);
const districtSex = computed(() => dive.value?.by?.district_sex ?? []);
const servicePointSex = computed(() => dive.value?.by?.service_point_sex ?? []);
const showSexTrend = computed(() => hasSexDim.value && monthBySex.value.length > 0);
const sexChartRef = ref<ApexChartHandle | null>(null);

const sexTrendOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
    colors: [seriesBlue, seriesOrange],
    plotOptions: { bar: { columnWidth: '65%', borderRadius: 4, borderRadiusApplication: 'end' } },
    dataLabels: { enabled: false },
    grid: { borderColor: gridHairline, xaxis: { lines: { show: false } } },
    xaxis: {
        categories: monthBySex.value.map((b) => b.label),
        labels: { style: { colors: inkMuted, fontSize: '11px' } },
        axisBorder: { color: '#c3c2b7' },
        axisTicks: { show: false },
    },
    yaxis: { labels: { style: { colors: inkMuted, fontSize: '11px' } }, forceNiceScale: true, min: 0, max: isPercent.value ? 100 : undefined },
    legend: { show: true, position: 'top', horizontalAlign: 'left', fontSize: '12px', labels: { colors: '#52514e' }, markers: { size: 6 } },
    tooltip: { shared: true, intersect: false, y: { formatter: (v: number | null) => (v === null ? '—' : isPercent.value ? `${v}%` : v.toLocaleString()) } },
}));
const sexTrendSeries = computed(() => [
    { name: 'Male', data: monthBySex.value.map((b) => b.male) },
    { name: 'Female', data: monthBySex.value.map((b) => b.female) },
]);
function downloadSexChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(sexChartRef, 'by_sex', format);
}

// ---- refs + image downloads for the facility/district/service-point charts
const facilityChartRef = ref<ApexChartHandle | null>(null);
const facilitySexChartRef = ref<ApexChartHandle | null>(null);
const districtChartRef = ref<ApexChartHandle | null>(null);
const districtSexChartRef = ref<ApexChartHandle | null>(null);
const servicePointChartRef = ref<ApexChartHandle | null>(null);
const servicePointSexChartRef = ref<ApexChartHandle | null>(null);
function downloadFacilityChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(facilityChartRef, 'facility', format);
}
function downloadFacilitySexChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(facilitySexChartRef, 'facility_by_sex', format);
}
function downloadDistrictChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(districtChartRef, 'district', format);
}
function downloadDistrictSexChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(districtSexChartRef, 'district_by_sex', format);
}
function downloadServicePointChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(servicePointChartRef, 'service_point', format);
}
function downloadServicePointSexChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(servicePointSexChartRef, 'service_point_by_sex', format);
}

// ---- narrative insights per chart (trend, averages, skewness, comparisons,
// naive projections) computed client-side from the data already loaded ----
const insightUnit = computed<Unit>(() => ({
    unit: isPercent.value ? 'percent' : 'count',
    noun: props.meta.type === 'sum' ? 'occurrences' : props.meta.type === 'percent' ? 'cases' : 'clients',
}));
const insightParagraphs = (text: string) => text.split('\n\n').filter(Boolean);

const trendInsight = computed(() => {
    if (monthTrend.value.length < 2) return '';
    const denominators = isPercent.value ? monthTrend.value.map((b) => b.denominator) : undefined;

    return describeTrend(monthTrend.value.map((b) => b.label), monthTrend.value.map((b) => b.value), insightUnit.value, denominators);
});
const sexTrendInsight = computed(() => (monthBySex.value.length < 2 ? '' : describeSexSplit(monthBySex.value, true, { ...insightUnit.value, dimension: 'month' })));
const facilityInsight = computed(() => describeCategorical(buckets('facility'), { ...insightUnit.value, dimension: 'facility' }));
const facilitySexInsight = computed(() => (facilitySex.value.length < 2 ? '' : describeSexSplit(facilitySex.value, false, { ...insightUnit.value, dimension: 'facility' })));
const districtInsight = computed(() => describeCategorical(buckets('district'), { ...insightUnit.value, dimension: 'district' }));
const districtSexInsight = computed(() => (districtSex.value.length < 2 ? '' : describeSexSplit(districtSex.value, false, { ...insightUnit.value, dimension: 'district' })));
const servicePointInsight = computed(() => describeCategorical(buckets('service_point'), { ...insightUnit.value, dimension: 'service point' }));
const servicePointSexInsight = computed(() => (servicePointSex.value.length < 2 ? '' : describeSexSplit(servicePointSex.value, false, { ...insightUnit.value, dimension: 'service point' })));

function hbarOptions(items: Bucket[]) {
    return {
        chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
        colors: [seriesBlue],
        plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4, borderRadiusApplication: 'end' } },
        dataLabels: { enabled: true, offsetX: 26, style: { colors: [inkSecondary], fontSize: '11px' }, formatter: (v: number) => (isPercent.value ? `${v}%` : v.toLocaleString()) },
        grid: { borderColor: gridHairline, yaxis: { lines: { show: false } } },
        xaxis: { categories: items.map((b) => b.label), labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false }, max: isPercent.value ? 100 : undefined },
        yaxis: { labels: { style: { colors: inkSecondary, fontSize: '12px' } } },
        legend: { show: false },
        tooltip: { y: { formatter: (v: number, opts: { dataPointIndex: number }) => {
            const b = items[opts.dataPointIndex];
            return isPercent.value && b?.numerator !== undefined ? `${v}% (${b.numerator}/${b.denominator})` : (isPercent.value ? `${v}%` : v.toLocaleString());
        } } },
    };
}
const chartSeries = (items: Bucket[]) => [{ name: props.meta.label, data: items.map((b) => b.value ?? 0) }];

function hbarSexOptions(items: SexBucket[]) {
    return {
        chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
        colors: [seriesBlue, seriesOrange],
        plotOptions: { bar: { horizontal: true, barHeight: '70%', borderRadius: 4, borderRadiusApplication: 'end' } },
        dataLabels: { enabled: false },
        grid: { borderColor: gridHairline, yaxis: { lines: { show: false } } },
        xaxis: { categories: items.map((b) => b.label), labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false }, max: isPercent.value ? 100 : undefined },
        yaxis: { labels: { style: { colors: inkSecondary, fontSize: '12px' } } },
        legend: { show: true, position: 'top', horizontalAlign: 'left', fontSize: '12px', labels: { colors: '#52514e' }, markers: { size: 6 } },
        tooltip: { shared: true, intersect: false, y: { formatter: (v: number | null) => (v === null ? '—' : isPercent.value ? `${v}%` : v.toLocaleString()) } },
    };
}
const sexSeries = (items: SexBucket[]) => [
    { name: 'Male', data: items.map((b) => b.male) },
    { name: 'Female', data: items.map((b) => b.female) },
];

const statusBadges: Record<string, { label: string; cls: string }> = {
    proxy: { label: 'Proxy', cls: 'bg-[#f7efdd] text-[#8f6115]' },
    blocked: { label: 'Not computable', cls: 'bg-[#f0efec] text-[#898781]' },
};
</script>

<template>
    <Head :title="`${meta.code} deep dive`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b]">
            <div class="mx-auto max-w-[1300px] px-5 py-7 sm:px-8 lg:px-10">

                <Link href="/data6/indicators" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#55706a] transition hover:text-[#173b3b]">
                    <ArrowLeft class="size-3.5" />All indicators
                </Link>

                <header class="mt-3 border-b border-[#d9ded7] pb-6">
                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] text-[#e2644b]">
                        <span class="font-mono">{{ meta.code }}</span>
                        <span class="text-[#a6b1aa]">·</span><span class="text-[#7b8984]">{{ meta.level }}</span>
                        <span v-if="statusBadges[meta.status]" class="rounded-full px-2 py-0.5 tracking-wide" :class="statusBadges[meta.status].cls">{{ statusBadges[meta.status].label }}</span>
                        <span v-if="meta.no_period" class="rounded-full bg-[#e7eef4] px-2 py-0.5 tracking-wide text-[#31577a]">All-time</span>
                    </div>
                    <h1 class="mt-2 max-w-3xl font-serif text-3xl leading-tight tracking-tight sm:text-4xl">{{ meta.label }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[#60716d]">{{ meta.definition }}</p>
                    <p v-if="meta.note" class="mt-1.5 flex max-w-3xl items-start gap-1.5 text-xs leading-5 text-[#a87524]">
                        <AlertTriangle class="mt-0.5 size-3.5 shrink-0" />{{ meta.note }}
                    </p>
                    <details v-if="method" class="mt-3 max-w-4xl border border-[#d9ded7] bg-[#fcfcfb] px-4 py-2.5">
                        <summary class="flex cursor-pointer items-center gap-2 text-xs font-bold text-[#244847]">
                            <HelpCircle class="size-4 text-[#e2644b]" />How we calculated this
                        </summary>
                        <p class="mt-2 text-xs leading-5 text-[#52655f]">{{ method }}</p>
                        <p v-if="meta.variables" class="mt-1.5 font-mono text-[10.5px] text-[#7b8984]">{{ meta.variables }}</p>
                        <p v-if="methodCommon" class="mt-2 border-t border-[#eef0eb] pt-2 text-[11px] leading-4.5 text-[#7d8b85]">{{ methodCommon }}</p>
                    </details>
                </header>

                <!-- Period + headline -->
                <section class="mt-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-end gap-3">
                        <div class="flex flex-wrap gap-1 rounded-full border border-[#cbd3cd] bg-white p-1">
                            <button v-for="preset in presets" :key="preset.key"
                                class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                                :class="activePreset === preset.key ? 'bg-[#173b3b] text-white' : 'text-[#55706a] hover:bg-[#eef0eb]'"
                                @click="applyPreset(preset.key)">{{ preset.label }}</button>
                        </div>
                        <template v-if="activePreset === 'custom'">
                            <label class="text-xs text-[#55706a]">From <input v-model="from" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" /></label>
                            <label class="text-xs text-[#55706a]">To <input v-model="to" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" /></label>
                        </template>
                        <span v-if="!meta.no_period" class="text-xs font-semibold text-[#7b8984]">{{ from }} → {{ to }}</span>
                        <span v-else class="text-xs font-semibold text-[#7b8984]">All-time value — no date field on this instrument</span>
                    </div>
                    <button class="rounded-full border border-[#bdc9c3] p-2.5 text-[#3c605b] transition hover:bg-white" title="Refresh" @click="load">
                        <RefreshCw class="size-4" :class="loading ? 'animate-spin' : ''" />
                    </button>
                </section>

                <div v-if="error" class="mt-5 flex items-center gap-2 bg-[#fff1ed] px-4 py-3 text-sm text-[#b74f3d]">
                    <CircleAlert class="size-4 shrink-0" />{{ error }}
                </div>
                <div v-else-if="loading" class="mt-8 py-14 text-center text-sm text-[#788681]">Computing {{ meta.code }} for {{ from }} → {{ to }}…</div>

                <template v-else-if="dive">
                    <section class="mt-6 flex flex-wrap items-end gap-6 border-l-4 border-[#e86d52] bg-[#fcfcfb] px-6 py-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#76827e]">{{ meta.no_period ? 'All-time value' : 'Value for the period' }}</p>
                            <p class="mt-1 font-serif text-5xl text-[#0b2c2c]">{{ totalText }}</p>
                        </div>
                        <p v-if="dive.total?.numerator !== undefined" class="pb-1.5 text-sm text-[#60716d]" style="font-variant-numeric: tabular-nums">
                            {{ dive.total.numerator.toLocaleString() }} / {{ dive.total.denominator?.toLocaleString() }}
                            <span class="block text-xs text-[#82908a]">numerator / denominator</span>
                        </p>
                    </section>

                    <section v-if="monthTrend.length" class="mt-4 border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-sm font-bold text-[#244847]">Monthly trend</h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <button class="rounded-full border px-3 py-1 text-[11px] font-bold transition"
                                    :class="barsVisible ? 'border-[#2a78d6] bg-[#e7eef4] text-[#2a78d6]' : 'border-[#cbd3cd] text-[#a6b1aa]'"
                                    @click="toggleBars">Monthly {{ isPercent ? 'rate' : 'value' }}</button>
                                <button class="rounded-full border px-3 py-1 text-[11px] font-bold transition"
                                    :class="lineVisible ? 'border-[#c58a32] bg-[#f7efdd] text-[#8f6115]' : 'border-[#cbd3cd] text-[#a6b1aa]'"
                                    @click="toggleLine">{{ cumulativeLabel }}</button>
                                <span class="mx-1 h-4 w-px bg-[#d9ded7]" />
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadChart('png')">
                                    <Download class="size-3" />PNG
                                </button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadChart('jpg')">
                                    <Download class="size-3" />JPG
                                </button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadTrendCsv">
                                    <Download class="size-3" />CSV
                                </button>
                            </div>
                        </div>
                        <p class="mt-1 text-[11px] text-[#788681]">Click a chip above, or a legend entry on the chart, to hide or show that series.</p>
                        <VueApexCharts ref="trendChartRef" type="line" height="280" :options="trendOptions" :series="trendSeries" />
                        <div v-if="trendInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                            <p v-for="(para, i) in insightParagraphs(trendInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                        </div>
                    </section>

                    <section v-if="showSexTrend" class="mt-4 border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-sm font-bold text-[#244847]">Monthly trend by sex</h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadSexChart('png')">
                                    <Download class="size-3" />PNG
                                </button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadSexChart('jpg')">
                                    <Download class="size-3" />JPG
                                </button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadSexBucketCsv(monthBySex, 'Month', 'by_sex')">
                                    <Download class="size-3" />CSV
                                </button>
                            </div>
                        </div>
                        <p class="mt-1 text-[11px] text-[#788681]">Male and female {{ isPercent ? 'rates' : 'counts' }} per month. Click a legend entry to hide or show a series.</p>
                        <VueApexCharts ref="sexChartRef" type="bar" height="260" :options="sexTrendOptions" :series="sexTrendSeries" />
                        <div v-if="sexTrendInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                            <p class="text-[12.5px] leading-5 text-[#52655f]">{{ sexTrendInsight }}</p>
                        </div>
                    </section>

                    <section class="mt-4 grid gap-4 lg:grid-cols-2">
                        <div class="flex flex-col gap-4">
                            <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h2 class="text-sm font-bold text-[#244847]">By facility</h2>
                                    <div v-if="buckets('facility').length" class="flex flex-wrap items-center gap-2">
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityChart('png')"><Download class="size-3" />PNG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityChart('jpg')"><Download class="size-3" />JPG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadBucketCsv(buckets('facility'), 'Facility', 'facility')"><Download class="size-3" />CSV</button>
                                    </div>
                                </div>
                                <VueApexCharts v-if="buckets('facility').length" ref="facilityChartRef" type="bar" :height="Math.max(160, buckets('facility').length * 30 + 60)"
                                    :options="hbarOptions(buckets('facility'))" :series="chartSeries(buckets('facility'))" />
                                <p v-else class="py-8 text-center text-xs text-[#898781]">No data in this period.</p>
                                <div v-if="facilityInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                    <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                                    <p v-for="(para, i) in insightParagraphs(facilityInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                                </div>
                            </div>
                            <div v-if="hasSexDim && facilitySex.length" class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h2 class="text-sm font-bold text-[#244847]">By facility, by sex</h2>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilitySexChart('png')"><Download class="size-3" />PNG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilitySexChart('jpg')"><Download class="size-3" />JPG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadSexBucketCsv(facilitySex, 'Facility', 'facility_by_sex')"><Download class="size-3" />CSV</button>
                                    </div>
                                </div>
                                <VueApexCharts ref="facilitySexChartRef" type="bar" :height="Math.max(180, facilitySex.length * 34 + 60)"
                                    :options="hbarSexOptions(facilitySex)" :series="sexSeries(facilitySex)" />
                                <div v-if="facilitySexInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                    <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                                    <p class="text-[12.5px] leading-5 text-[#52655f]">{{ facilitySexInsight }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h2 class="text-sm font-bold text-[#244847]">By district</h2>
                                    <div v-if="buckets('district').length" class="flex flex-wrap items-center gap-2">
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadDistrictChart('png')"><Download class="size-3" />PNG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadDistrictChart('jpg')"><Download class="size-3" />JPG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadBucketCsv(buckets('district'), 'District', 'district')"><Download class="size-3" />CSV</button>
                                    </div>
                                </div>
                                <VueApexCharts v-if="buckets('district').length" ref="districtChartRef" type="bar" :height="Math.max(120, buckets('district').length * 32 + 60)"
                                    :options="hbarOptions(buckets('district'))" :series="chartSeries(buckets('district'))" />
                                <p v-else class="py-6 text-center text-xs text-[#898781]">No data in this period.</p>
                                <div v-if="districtInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                    <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                                    <p v-for="(para, i) in insightParagraphs(districtInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                                </div>
                            </div>
                            <div v-if="hasSexDim && districtSex.length" class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h2 class="text-sm font-bold text-[#244847]">By district, by sex</h2>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadDistrictSexChart('png')"><Download class="size-3" />PNG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadDistrictSexChart('jpg')"><Download class="size-3" />JPG</button>
                                        <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadSexBucketCsv(districtSex, 'District', 'district_by_sex')"><Download class="size-3" />CSV</button>
                                    </div>
                                </div>
                                <VueApexCharts ref="districtSexChartRef" type="bar" :height="Math.max(140, districtSex.length * 36 + 60)"
                                    :options="hbarSexOptions(districtSex)" :series="sexSeries(districtSex)" />
                                <div v-if="districtSexInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                    <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                                    <p class="text-[12.5px] leading-5 text-[#52655f]">{{ districtSexInsight }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                                    <h2 class="text-sm font-bold text-[#244847]">By age band</h2>
                                    <table class="mt-2 w-full text-xs" style="font-variant-numeric: tabular-nums">
                                        <tbody>
                                            <tr v-for="b in buckets('age_band')" :key="b.label" class="border-b border-[#eef0eb] last:border-0">
                                                <td class="py-1.5 text-[#52514e]">{{ b.label }}</td>
                                                <td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ fmtVal(b.value) }}</td>
                                            </tr>
                                            <tr v-if="!buckets('age_band').length"><td class="py-3 text-center text-[#898781]" colspan="2">No data.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                                    <h2 class="text-sm font-bold text-[#244847]">By sex</h2>
                                    <table class="mt-2 w-full text-xs" style="font-variant-numeric: tabular-nums">
                                        <tbody>
                                            <tr v-for="b in buckets('sex')" :key="b.label" class="border-b border-[#eef0eb] last:border-0">
                                                <td class="py-1.5 text-[#52514e]">{{ b.label }}</td>
                                                <td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ fmtVal(b.value) }}</td>
                                            </tr>
                                            <tr v-if="!buckets('sex').length"><td class="py-3 text-center text-[#898781]" colspan="2">No data.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section v-if="buckets('service_point').length" class="mt-4 border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-sm font-bold text-[#244847]">By service point</h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadServicePointChart('png')"><Download class="size-3" />PNG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadServicePointChart('jpg')"><Download class="size-3" />JPG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadBucketCsv(buckets('service_point'), 'Service point', 'service_point')"><Download class="size-3" />CSV</button>
                            </div>
                        </div>
                        <VueApexCharts ref="servicePointChartRef" type="bar" :height="Math.max(160, buckets('service_point').length * 26 + 60)"
                            :options="hbarOptions(buckets('service_point'))" :series="chartSeries(buckets('service_point'))" />
                        <div v-if="servicePointInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                            <p v-for="(para, i) in insightParagraphs(servicePointInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                        </div>
                    </section>

                    <section v-if="hasSexDim && servicePointSex.length" class="mt-4 border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-sm font-bold text-[#244847]">By service point, by sex</h2>
                            <div class="flex flex-wrap items-center gap-2">
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadServicePointSexChart('png')"><Download class="size-3" />PNG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadServicePointSexChart('jpg')"><Download class="size-3" />JPG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadSexBucketCsv(servicePointSex, 'Service point', 'service_point_by_sex')"><Download class="size-3" />CSV</button>
                            </div>
                        </div>
                        <VueApexCharts ref="servicePointSexChartRef" type="bar" :height="Math.max(180, servicePointSex.length * 30 + 60)"
                            :options="hbarSexOptions(servicePointSex)" :series="sexSeries(servicePointSex)" />
                        <div v-if="servicePointSexInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                            <p class="text-[12.5px] leading-5 text-[#52655f]">{{ servicePointSexInsight }}</p>
                        </div>
                    </section>

                    <!-- Indicator-specific analysis: shared modules keyed by group, built out one at a time.
                         A code can carry more than one module (e.g. AHP007 sits in both the ART cascade
                         group and the HTS reconciliation pair) - each renders as its own independent,
                         independently-lazy accordion. -->
                    <ArtCascadeAnalysis v-if="meta.analysis?.includes('art_cascade')" :code="meta.code" :from="from" :to="to" />
                    <HtsReconciliationAnalysis v-if="meta.analysis?.includes('hts_reconciliation')" :code="meta.code" :from="from" :to="to" />
                    <section v-if="!meta.analysis?.length" class="mt-6 border border-dashed border-[#c5b78f] bg-[#fbf6ea] px-5 py-4">
                        <div class="flex items-start gap-3">
                            <FlaskConical class="mt-0.5 size-5 shrink-0 text-[#a87524]" />
                            <div>
                                <h2 class="text-sm font-bold text-[#6f5412]">Indicator-specific analysis — in development</h2>
                                <p class="mt-1 max-w-3xl text-xs leading-5 text-[#8f6115]">
                                    This section will carry the tailored deep dive for {{ meta.code }} (cascades, cohort views,
                                    data-quality drill-downs and comparisons specific to this indicator) as each indicator is
                                    tackled with the programme team. The breakdowns above are the standard analysis applied to
                                    every indicator.
                                </p>
                            </div>
                        </div>
                    </section>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
