<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CircleAlert, Download, FileSpreadsheet, Lightbulb, Lock, RefreshCw } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { type BreadcrumbItem } from '@/types';
import { useTier } from '@/composables/useTier';
import IndicatorCard from './IndicatorCard.vue';

const { isPro, canDownload, canDownloadPdf, canExportAll } = useTier();

interface IndicatorMeta {
    id: number;
    code?: string;
    /** Set only on an interim ("...a") proxy: the code of the real
     *  indicator it stands in for, e.g. AHP023a -> AHP023. Drives the
     *  side-by-side pairing on this page - see displayItems below. */
    pairs_with?: string | null;
    key: string;
    group: string;
    type: 'count' | 'percent' | 'sum';
    status: 'active' | 'provisional' | 'proxy' | 'blocked';
    label: string;
    definition: string;
    note: string | null;
    no_period?: boolean;
}
interface GroupMeta { key: string; label: string; }
interface IndicatorValue { value: number | null; numerator?: number; denominator?: number; extra?: Record<string, number>; }
interface TrendPoint { month: string; clients: number; visits: number; }
interface FacilityRow { facility: string; clients: number; }

const props = defineProps<{
    appTitle: string;
    registry: { groups: GroupMeta[]; indicators: (IndicatorMeta & { variables?: string | null })[]; methods?: Record<string, string> };
    filterOptions: { districts: string[]; facilities: string[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'AHP overview', href: '/data6' },
    { title: 'Indicators', href: '/data6/indicators' },
];

// ---- filters -------------------------------------------------------------
function quarterRange(offset = 0): { from: string; to: string } {
    const now = new Date();
    const q = Math.floor(now.getMonth() / 3) + offset;
    const from = new Date(now.getFullYear(), q * 3, 1);
    const to = new Date(now.getFullYear(), q * 3 + 3, 0);
    const fmt = (d: Date) => d.toISOString().slice(0, 10);
    return { from: fmt(from), to: fmt(to) };
}

const presets = [
    { key: 'this_quarter', label: 'This quarter', range: () => quarterRange(0) },
    { key: 'last_quarter', label: 'Last quarter', range: () => quarterRange(-1) },
    { key: 'ytd', label: 'Year to date', range: () => ({ from: `${new Date().getFullYear()}-01-01`, to: new Date().toISOString().slice(0, 10) }) },
    { key: 'custom', label: 'Custom', range: null },
];
const activePreset = ref('this_quarter');
const from = ref(quarterRange(0).from);
const to = ref(quarterRange(0).to);
const district = ref('');
const facility = ref('');
const gender = ref('');
const ageBand = ref('10_19');

function applyPreset(key: string): void {
    activePreset.value = key;
    const preset = presets.find((p) => p.key === key);
    if (preset?.range) {
        const r = preset.range();
        from.value = r.from;
        to.value = r.to;
        load();
    }
}

// ---- data ----------------------------------------------------------------
const loading = ref(false);
const error = ref('');
const values = ref<Record<string, IndicatorValue>>({});
const trend = ref<TrendPoint[]>([]);
const facilityBreakdown = ref<FacilityRow[]>([]);
const activeGroup = ref('access');

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    const params = new URLSearchParams({ from: from.value, to: to.value, age_band: ageBand.value });
    if (district.value) params.set('district', district.value);
    if (facility.value) params.set('facility', facility.value);
    if (gender.value) params.set('gender', gender.value);

    try {
        const response = await fetch(`/api/data6/indicators?${params.toString()}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(`Request failed (${response.status})`);
        const payload = await response.json();
        values.value = payload.values ?? {};
        trend.value = payload.trend ?? [];
        facilityBreakdown.value = payload.facility_breakdown ?? [];
    } catch {
        error.value = 'Could not compute the indicators. Check the database connection and try again.';
    } finally {
        loading.value = false;
    }
}
onMounted(load);

// ---- derived -------------------------------------------------------------
const groupTabs = computed(() => props.registry.groups);
const indicatorsInGroup = computed(() =>
    props.registry.indicators.filter((meta) => meta.group === activeGroup.value),
);
function valueFor(key: string): IndicatorValue | undefined {
    return values.value[key];
}
function fmt(n: number | null | undefined): string {
    if (n === null || n === undefined) return '—';
    return n.toLocaleString();
}

// ---- pairing: an "...a" indicator shown side by side with the real one it's
// linked to via `pairs_with`, so the two are never confused for one card.
// Two kinds share this mechanism, told apart by the linked indicator's own
// `status` (see variantFor below):
//  - interim proxy (status proxy/blocked, e.g. AHP023a -> AHP023): a
//    temporary pre-go-live stand-in - amber, dashed styling in IndicatorCard.
//  - supplementary (status active, e.g. AHP004a -> AHP004): a permanent,
//    equally-valid alternate definition shown alongside the official one,
//    not a stand-in - blue, solid styling in IndicatorCard.
type DisplayItem =
    | { type: 'pair'; real: IndicatorMeta; linked: IndicatorMeta }
    | { type: 'single'; meta: IndicatorMeta };

type PairVariant = 'proxy' | 'supplementary';
function variantFor(linked: IndicatorMeta): PairVariant {
    return linked.status === 'proxy' || linked.status === 'blocked' ? 'proxy' : 'supplementary';
}
function pairHeaderFor(variant: PairVariant): string {
    return variant === 'proxy' ? 'Official figure vs. interim proxy — side by side' : 'Official figure vs. supplementary definition — side by side';
}

const byCode = computed(() => new Map(props.registry.indicators.map((m) => [m.code, m])));
const linkedForReal = computed(() => {
    const map = new Map<string, IndicatorMeta>();
    for (const m of props.registry.indicators) {
        if (m.pairs_with) map.set(m.pairs_with, m);
    }
    return map;
});

const displayItems = computed<DisplayItem[]>(() => {
    const items: DisplayItem[] = [];
    const consumed = new Set<string>();
    for (const meta of indicatorsInGroup.value) {
        if (!meta.code || consumed.has(meta.code)) continue;
        const real = meta.pairs_with ? byCode.value.get(meta.pairs_with) : undefined;
        const linked = linkedForReal.value.get(meta.code);
        if (real) {
            items.push({ type: 'pair', real, linked: meta });
            consumed.add(meta.code);
            consumed.add(real.code!);
        } else if (linked) {
            items.push({ type: 'pair', real: meta, linked });
            consumed.add(meta.code);
            consumed.add(linked.code!);
        } else {
            items.push({ type: 'single', meta });
        }
    }
    return items;
});

// ---- charts (dataviz reference palette; slots 1-2, documented order) ----
const inkMuted = '#898781';
const gridHairline = '#e1e0d9';

const trendOptions = computed(() => ({
    chart: { type: 'line', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
    colors: ['#2a78d6', '#eb6834'],
    stroke: { width: 2, curve: 'straight' },
    markers: { size: 4, strokeWidth: 2, strokeColors: '#fcfcfb', hover: { size: 6 } },
    grid: { borderColor: gridHairline, strokeDashArray: 0, xaxis: { lines: { show: false } } },
    xaxis: {
        categories: trend.value.map((t) => t.month),
        labels: { style: { colors: inkMuted, fontSize: '11px' } },
        axisBorder: { color: '#c3c2b7' },
        axisTicks: { show: false },
    },
    yaxis: { labels: { style: { colors: inkMuted, fontSize: '11px' } }, forceNiceScale: true, min: 0 },
    legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', labels: { colors: '#52514e' }, markers: { size: 5 } },
    tooltip: { shared: true, intersect: false },
}));
const trendSeries = computed(() => [
    { name: 'Unique adolescents', data: trend.value.map((t) => t.clients) },
    { name: 'Visits', data: trend.value.map((t) => t.visits) },
]);

const facilityOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
    colors: ['#2a78d6'],
    plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4, borderRadiusApplication: 'end' } },
    dataLabels: { enabled: true, style: { colors: ['#52514e'], fontSize: '11px' }, offsetX: 26, formatter: (v: number) => v.toLocaleString() },
    grid: { borderColor: gridHairline, yaxis: { lines: { show: false } } },
    xaxis: { labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false } },
    yaxis: { labels: { style: { colors: '#52514e', fontSize: '12px' } } },
    legend: { show: false },
    tooltip: { y: { formatter: (v: number) => `${v.toLocaleString()} adolescents` } },
}));
const facilitySeries = computed(() => [
    { name: 'Unique adolescents', data: facilityBreakdown.value.map((f) => f.clients) },
]);
const facilityCategories = computed(() => facilityBreakdown.value.map((f) => f.facility));

// ---- per-chart downloads (Pro) ------------------------------------------------
const chartBtn = 'inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white';
interface ApexChartHandle { dataURI(options?: { scale?: number }): Promise<{ imgURI?: string }>; }
const trendChartRef = ref<ApexChartHandle | null>(null);
const facilityChartRef = ref<ApexChartHandle | null>(null);
const fileScope = computed(() => [district.value, facility.value].filter(Boolean).map((v) => `_${v}`).join(''));

function triggerDownload(href: string, filename: string): void {
    const link = document.createElement('a');
    link.href = href;
    link.download = filename;
    link.click();
}
async function downloadChartImage(chartRef: { value: ApexChartHandle | null }, name: string, format: 'png' | 'jpg'): Promise<void> {
    const pngUri = (await chartRef.value?.dataURI({ scale: 2 }))?.imgURI;
    if (!pngUri) return;
    const filename = `ahp_${name}${fileScope.value}_${from.value}_${to.value}.${format}`;
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
function downloadChartCsv(headers: string[], rows: (string | number)[][], name: string): void {
    const escape = (v: string | number): string => (/[",\n]/.test(String(v)) ? `"${String(v).replace(/"/g, '""')}"` : String(v));
    const url = URL.createObjectURL(new Blob([[headers, ...rows].map((r) => r.map(escape).join(',')).join('\r\n')], { type: 'text/csv;charset=utf-8;' }));
    triggerDownload(url, `ahp_${name}${fileScope.value}_${from.value}_${to.value}.csv`);
    URL.revokeObjectURL(url);
}
// Called from script (not the template) so the refs arrive un-unwrapped.
const downloadTrendImage = (format: 'png' | 'jpg') => downloadChartImage(trendChartRef, 'monthly_trend', format);
const downloadFacilityImage = (format: 'png' | 'jpg') => downloadChartImage(facilityChartRef, 'by_facility', format);
const downloadTrendCsv = () => downloadChartCsv(['Month', 'Unique adolescents', 'Visits'], trend.value.map((t) => [t.month, t.clients, t.visits]), 'monthly_trend');
const downloadFacilityCsv = () => downloadChartCsv(['Facility', 'Unique adolescents'], facilityBreakdown.value.map((f) => [f.facility, f.clients]), 'by_facility');

// ---- all-indicator CSV export (Pro+: a page-wide export) ------------------------
function exportCsv(): void {
    const rows: string[] = ['id,indicator,group,status,value,numerator,denominator'];
    for (const meta of props.registry.indicators) {
        const v = valueFor(meta.key);
        rows.push([
            meta.id,
            `"${meta.label.replace(/"/g, '""')}"`,
            meta.group,
            meta.status,
            v?.value ?? '',
            v?.numerator ?? '',
            v?.denominator ?? '',
        ].join(','));
    }
    const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `ahp_indicators_${from.value}_${to.value}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
}

// ---- PDF export (print-driven) -------------------------------------------
const generatedOn = computed(() => new Date().toLocaleString());
function downloadPdf(): void {
    window.print();
}
</script>

<template>
    <Head title="AHP Indicators" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b] print:bg-white print:text-black">
            <div class="mx-auto max-w-[1500px] px-5 py-7 sm:px-8 lg:px-10 print:max-w-none print:px-0 print:py-0">

                <header class="flex flex-col justify-between gap-4 border-b border-[#d9ded7] pb-6 lg:flex-row lg:items-end print:break-inside-avoid">
                    <div>
                        <div class="mb-3 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-[#e2644b]">
                            <span class="h-2 w-2 rounded-full bg-[#e2644b]" />{{ appTitle }}
                        </div>
                        <h1 class="font-serif text-4xl leading-tight tracking-tight">AHP indicator dashboard</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-[#60716d]">
                            The 45 programme indicators, computed across FCH, OI/ART and OPD with cross-project
                            deduplication. Unique clients are counted once however many services they use.
                        </p>
                        <p class="mt-2 hidden text-xs text-[#60716d] print:block">
                            Period: {{ from }} → {{ to }} · Report generated {{ generatedOn }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 print:hidden">
                        <Link v-if="isPro" href="/data6/insights" class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#3c605b] transition hover:bg-white">
                            <Lightbulb class="size-3.5" />Insights
                        </Link>
                        <Link v-else href="/data6/plans" class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#82908a] transition hover:bg-white" title="Insights is a Pro feature — view plans">
                            <Lock class="size-3.5" />Insights
                        </Link>
                        <Link v-if="isPro" href="/data6/reports" class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#3c605b] transition hover:bg-white">
                            <FileSpreadsheet class="size-3.5" />M&amp;E reports
                        </Link>
                        <button v-if="canExportAll" class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#3c605b] transition hover:bg-white" @click="exportCsv">
                            <Download class="size-3.5" />Export CSV
                        </button>
                        <button
                            v-if="canDownloadPdf"
                            class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#3c605b] transition hover:bg-white"
                            title="Opens the print dialog — choose &quot;Save as PDF&quot; as the destination"
                            @click="downloadPdf">
                            <Download class="size-3.5" />Download PDF
                        </button>
                        <button class="inline-flex items-center gap-2 rounded-full bg-[#173b3b] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#285655]" @click="load">
                            <RefreshCw class="size-3.5" :class="loading ? 'animate-spin' : ''" />Refresh
                        </button>
                    </div>
                </header>

                <!-- Filters: one row above the charts -->
                <section class="mt-5 flex flex-wrap items-end gap-3 print:hidden">
                    <div class="flex gap-1 rounded-full border border-[#cbd3cd] bg-white p-1">
                        <button v-for="preset in presets" :key="preset.key"
                            class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                            :class="activePreset === preset.key ? 'bg-[#173b3b] text-white' : 'text-[#55706a] hover:bg-[#eef0eb]'"
                            @click="applyPreset(preset.key)">{{ preset.label }}</button>
                    </div>
                    <template v-if="activePreset === 'custom'">
                        <label class="text-xs text-[#55706a]">From
                            <input v-model="from" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" />
                        </label>
                        <label class="text-xs text-[#55706a]">To
                            <input v-model="to" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" />
                        </label>
                    </template>
                    <select v-model="district" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]" @change="load">
                        <option value="">All districts</option>
                        <option v-for="d in filterOptions.districts" :key="d" :value="d">{{ d }}</option>
                    </select>
                    <select v-model="facility" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]" @change="load">
                        <option value="">All facilities</option>
                        <option v-for="f in filterOptions.facilities" :key="f" :value="f">{{ f }}</option>
                    </select>
                    <select v-model="gender" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]" @change="load">
                        <option value="">All genders</option>
                        <option value="1">Male</option>
                        <option value="2">Female</option>
                    </select>
                    <select v-model="ageBand" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]" @change="load">
                        <option value="10_19">Age 10–19</option>
                        <option value="10_14">Age 10–14</option>
                        <option value="15_19">Age 15–19</option>
                        <option value="all">All ages (QA)</option>
                    </select>
                </section>

                <div v-if="error" class="mt-5 flex items-center gap-2 bg-[#fff1ed] px-4 py-3 text-sm text-[#b74f3d]">
                    <CircleAlert class="size-4 shrink-0" />{{ error }}
                </div>
                <div v-else-if="loading" class="mt-8 py-16 text-center text-sm text-[#788681]">
                    Computing indicators for {{ from }} → {{ to }}…
                </div>

                <template v-else>
                    <!-- Overview charts -->
                    <section class="mt-6 grid gap-4 xl:grid-cols-2">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h2 class="text-sm font-bold text-[#244847]">Adolescents and visits by month</h2>
                                <div v-if="canDownload && trend.length" class="flex items-center gap-2 print:hidden">
                                    <button :class="chartBtn" @click="downloadTrendImage('png')"><Download class="size-3" />PNG</button>
                                    <button :class="chartBtn" @click="downloadTrendImage('jpg')"><Download class="size-3" />JPG</button>
                                    <button :class="chartBtn" @click="downloadTrendCsv"><Download class="size-3" />CSV</button>
                                </div>
                            </div>
                            <VueApexCharts v-if="trend.length" ref="trendChartRef" type="line" height="240" :options="trendOptions" :series="trendSeries" />
                            <p v-else class="py-12 text-center text-xs text-[#898781]">No dated encounters in this period.</p>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h2 class="text-sm font-bold text-[#244847]">Unique adolescents by facility</h2>
                                <div v-if="canDownload && facilityBreakdown.length" class="flex items-center gap-2 print:hidden">
                                    <button :class="chartBtn" @click="downloadFacilityImage('png')"><Download class="size-3" />PNG</button>
                                    <button :class="chartBtn" @click="downloadFacilityImage('jpg')"><Download class="size-3" />JPG</button>
                                    <button :class="chartBtn" @click="downloadFacilityCsv"><Download class="size-3" />CSV</button>
                                </div>
                            </div>
                            <VueApexCharts v-if="facilityBreakdown.length" ref="facilityChartRef" type="bar" :height="Math.max(200, facilityBreakdown.length * 32 + 60)"
                                :options="{ ...facilityOptions, xaxis: { ...facilityOptions.xaxis, categories: facilityCategories } }"
                                :series="facilitySeries" />
                            <p v-else class="py-12 text-center text-xs text-[#898781]">No facility data in this period.</p>
                        </div>
                    </section>

                    <!-- Group tabs -->
                    <nav class="mt-8 flex flex-wrap gap-2 border-b border-[#d9ded7] pb-3 print:hidden">
                        <button v-for="group in groupTabs" :key="group.key"
                            class="rounded-full border px-4 py-2 text-xs font-bold transition"
                            :class="activeGroup === group.key ? 'border-[#173b3b] bg-[#173b3b] text-white' : 'border-[#cbd3cd] text-[#55706a] hover:bg-white'"
                            @click="activeGroup = group.key">{{ group.label }}</button>
                    </nav>
                    <p class="mt-6 hidden text-xs font-bold uppercase tracking-wide text-[#55706a] print:block">
                        {{ groupTabs.find((g) => g.key === activeGroup)?.label }} indicators
                    </p>

                    <!-- Indicator cards -->
                    <section class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        <template v-for="item in displayItems" :key="item.type === 'pair' ? item.real.code : item.meta.code">
                            <!-- Paired indicator: the official figure and its linked interim
                                 proxy or supplementary definition, shown side by side under
                                 one shared header so they read as a comparison, not two
                                 unrelated cards. -->
                            <div v-if="item.type === 'pair'" class="col-span-full overflow-hidden border-2 border-[#173b3b] print:break-inside-avoid">
                                <div class="flex flex-wrap items-center justify-between gap-1 bg-[#173b3b] px-4 py-1.5">
                                    <span class="text-[11px] font-bold uppercase tracking-wide text-white">{{ item.real.label }}</span>
                                    <span class="text-[10px] font-semibold text-[#a9c7c2]">{{ pairHeaderFor(variantFor(item.linked)) }}</span>
                                </div>
                                <div class="grid divide-y divide-[#d9ded7] sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                                    <IndicatorCard :meta="item.real" :value="valueFor(item.real.key)" :method="registry.methods?.[item.real.key]" variant="default" />
                                    <IndicatorCard :meta="item.linked" :value="valueFor(item.linked.key)" :method="registry.methods?.[item.linked.key]" :variant="variantFor(item.linked)" />
                                </div>
                            </div>
                            <IndicatorCard v-else :meta="item.meta" :value="valueFor(item.meta.key)" :method="registry.methods?.[item.meta.key]" />
                        </template>
                    </section>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
