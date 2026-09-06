<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, CircleAlert, FlaskConical, HelpCircle, RefreshCw } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { type BreadcrumbItem } from '@/types';

interface Bucket { label: string; value: number | null; numerator?: number; denominator?: number; }
interface DeepDive {
    code: string; key: string; label: string; group: string; level: string;
    type: 'count' | 'percent' | 'sum'; status: string; note: string | null; no_period: boolean;
    total: { value: number | null; numerator?: number; denominator?: number } | null;
    by: Record<string, Bucket[]>;
}
interface Meta {
    id: number; code: string; key: string; group: string; level: string; type: string;
    status: string; label: string; definition: string; variables: string | null;
    disaggregation: string[]; note: string | null; no_period?: boolean;
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

const trendOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
    colors: [seriesBlue],
    plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, borderRadiusApplication: 'end' } },
    dataLabels: { enabled: true, offsetY: -18, style: { colors: [inkSecondary], fontSize: '11px' }, formatter: (v: number) => (isPercent.value ? `${v}%` : v.toLocaleString()) },
    grid: { borderColor: gridHairline, xaxis: { lines: { show: false } } },
    xaxis: { categories: monthTrend.value.map((b) => b.label), labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false } },
    yaxis: { labels: { style: { colors: inkMuted, fontSize: '11px' } }, forceNiceScale: true, max: isPercent.value ? 100 : undefined },
    legend: { show: false },
    tooltip: { y: { formatter: (v: number, opts: { dataPointIndex: number }) => {
        const b = monthTrend.value[opts.dataPointIndex];
        return isPercent.value && b?.numerator !== undefined ? `${v}% (${b.numerator}/${b.denominator})` : (isPercent.value ? `${v}%` : v.toLocaleString());
    } } },
}));

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
                        <h2 class="text-sm font-bold text-[#244847]">Monthly trend</h2>
                        <VueApexCharts type="bar" height="240" :options="trendOptions" :series="chartSeries(monthTrend)" />
                    </section>

                    <section class="mt-4 grid gap-4 lg:grid-cols-2">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">By facility</h2>
                            <VueApexCharts v-if="buckets('facility').length" type="bar" :height="Math.max(160, buckets('facility').length * 30 + 60)"
                                :options="hbarOptions(buckets('facility'))" :series="chartSeries(buckets('facility'))" />
                            <p v-else class="py-8 text-center text-xs text-[#898781]">No data in this period.</p>
                        </div>
                        <div class="flex flex-col gap-4">
                            <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                                <h2 class="text-sm font-bold text-[#244847]">By district</h2>
                                <VueApexCharts v-if="buckets('district').length" type="bar" :height="Math.max(120, buckets('district').length * 32 + 60)"
                                    :options="hbarOptions(buckets('district'))" :series="chartSeries(buckets('district'))" />
                                <p v-else class="py-6 text-center text-xs text-[#898781]">No data in this period.</p>
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
                        <h2 class="text-sm font-bold text-[#244847]">By service point</h2>
                        <VueApexCharts type="bar" :height="Math.max(160, buckets('service_point').length * 26 + 60)"
                            :options="hbarOptions(buckets('service_point'))" :series="chartSeries(buckets('service_point'))" />
                    </section>

                    <!-- Slot for indicator-specific analysis, built out per indicator -->
                    <section class="mt-6 border border-dashed border-[#c5b78f] bg-[#fbf6ea] px-5 py-4">
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
