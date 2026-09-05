<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, CircleAlert, Download, FileSpreadsheet, Info, RefreshCw } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { type BreadcrumbItem } from '@/types';

interface IndicatorMeta {
    id: number;
    code?: string;
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
    registry: { groups: GroupMeta[]; indicators: IndicatorMeta[] };
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

const statusBadges: Record<string, { label: string; cls: string }> = {
    provisional: { label: 'Definition pending', cls: 'bg-[#e7eef4] text-[#31577a]' },
    proxy: { label: 'Proxy', cls: 'bg-[#f7efdd] text-[#8f6115]' },
    blocked: { label: 'Not computable', cls: 'bg-[#f0efec] text-[#898781]' },
};

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

// ---- CSV export ----------------------------------------------------------
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
</script>

<template>
    <Head title="AHP Indicators" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b]">
            <div class="mx-auto max-w-[1500px] px-5 py-7 sm:px-8 lg:px-10">

                <header class="flex flex-col justify-between gap-4 border-b border-[#d9ded7] pb-6 lg:flex-row lg:items-end">
                    <div>
                        <div class="mb-3 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-[#e2644b]">
                            <span class="h-2 w-2 rounded-full bg-[#e2644b]" />{{ appTitle }}
                        </div>
                        <h1 class="font-serif text-4xl leading-tight tracking-tight">AHP indicator dashboard</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-[#60716d]">
                            The 45 programme indicators, computed across FCH, OI/ART and OPD with cross-project
                            deduplication. Unique clients are counted once however many services they use.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link href="/data6/reports" class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#3c605b] transition hover:bg-white">
                            <FileSpreadsheet class="size-3.5" />M&amp;E reports
                        </Link>
                        <button class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#3c605b] transition hover:bg-white" @click="exportCsv">
                            <Download class="size-3.5" />Export CSV
                        </button>
                        <button class="inline-flex items-center gap-2 rounded-full bg-[#173b3b] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#285655]" @click="load">
                            <RefreshCw class="size-3.5" :class="loading ? 'animate-spin' : ''" />Refresh
                        </button>
                    </div>
                </header>

                <!-- Filters: one row above the charts -->
                <section class="mt-5 flex flex-wrap items-end gap-3">
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
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Adolescents and visits by month</h2>
                            <VueApexCharts v-if="trend.length" type="line" height="240" :options="trendOptions" :series="trendSeries" />
                            <p v-else class="py-12 text-center text-xs text-[#898781]">No dated encounters in this period.</p>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Unique adolescents by facility</h2>
                            <VueApexCharts v-if="facilityBreakdown.length" type="bar" :height="Math.max(200, facilityBreakdown.length * 32 + 60)"
                                :options="{ ...facilityOptions, xaxis: { ...facilityOptions.xaxis, categories: facilityCategories } }"
                                :series="facilitySeries" />
                            <p v-else class="py-12 text-center text-xs text-[#898781]">No facility data in this period.</p>
                        </div>
                    </section>

                    <!-- Group tabs -->
                    <nav class="mt-8 flex flex-wrap gap-2 border-b border-[#d9ded7] pb-3">
                        <button v-for="group in groupTabs" :key="group.key"
                            class="rounded-full border px-4 py-2 text-xs font-bold transition"
                            :class="activeGroup === group.key ? 'border-[#173b3b] bg-[#173b3b] text-white' : 'border-[#cbd3cd] text-[#55706a] hover:bg-white'"
                            @click="activeGroup = group.key">{{ group.label }}</button>
                    </nav>

                    <!-- Indicator cards -->
                    <section class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        <article v-for="meta in indicatorsInGroup" :key="meta.key"
                            class="flex flex-col justify-between border border-[#d9ded7] bg-[#fcfcfb] p-4"
                            :class="meta.status === 'blocked' ? 'opacity-70' : ''">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-[13px] font-bold leading-snug text-[#244847]">
                                        <span class="mr-1.5 font-mono text-[10px] text-[#898781]">{{ meta.code ?? meta.id }}</span>{{ meta.label }}
                                    </h3>
                                    <span v-if="statusBadges[meta.status]"
                                        class="shrink-0 rounded-full px-2 py-0.5 text-[9.5px] font-bold uppercase tracking-wide"
                                        :class="statusBadges[meta.status].cls">{{ statusBadges[meta.status].label }}</span>
                                </div>
                                <div class="mt-3 flex items-baseline gap-2">
                                    <span class="text-3xl font-semibold text-[#0b2c2c]">
                                        {{ meta.type === 'percent' && valueFor(meta.key)?.value !== null && valueFor(meta.key) ? `${fmt(valueFor(meta.key)?.value)}%` : fmt(valueFor(meta.key)?.value) }}
                                    </span>
                                    <span v-if="meta.type === 'percent' && valueFor(meta.key)?.denominator !== undefined"
                                        class="text-xs text-[#788681]" style="font-variant-numeric: tabular-nums">
                                        {{ fmt(valueFor(meta.key)?.numerator) }} / {{ fmt(valueFor(meta.key)?.denominator) }}
                                    </span>
                                    <span v-if="valueFor(meta.key)?.extra?.bba !== undefined" class="text-xs text-[#788681]">
                                        incl. {{ valueFor(meta.key)?.extra?.bba }} BBA
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3 space-y-1 border-t border-[#eef0eb] pt-2">
                                <p class="flex items-start gap-1.5 text-[11px] leading-4 text-[#7d8b85]">
                                    <Info class="mt-0.5 size-3 shrink-0" />{{ meta.definition }}
                                </p>
                                <p v-if="meta.note" class="flex items-start gap-1.5 text-[11px] leading-4 text-[#a87524]">
                                    <AlertTriangle class="mt-0.5 size-3 shrink-0" />{{ meta.note }}
                                </p>
                                <p v-if="meta.no_period" class="flex items-start gap-1.5 text-[11px] leading-4 text-[#7d8b85]">
                                    <AlertTriangle class="mt-0.5 size-3 shrink-0 text-[#a87524]" />No date field on this instrument — value is all-time, the period filter does not apply.
                                </p>
                            </div>
                        </article>
                    </section>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
