<script setup lang="ts">
import { ChevronDown, CircleAlert, Download, HelpCircle, Info } from 'lucide-vue-next';
import { computed, ref, watch, type Ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import RateMeter from '@/components/data6/RateMeter.vue';
import { describeCategorical, describeTrend } from '@/composables/useChartInsights';

interface OutcomeBucket { label: string; value: number; pct: number; }
interface RetentionPoint { label: string; cohort_size: number; retained: number; pct: number | null; }
interface VlCoverage { active_total: number; tested_12mo: number; pct: number | null; }
interface ArtCascadeData {
    cohort_outcomes: { total: number; by: OutcomeBucket[] };
    retention_trend: RetentionPoint[];
    vl_coverage: VlCoverage;
}
interface ApexChartHandle {
    dataURI(options?: { scale?: number }): Promise<{ imgURI?: string }>;
}

const props = defineProps<{ code: string; from: string; to: string }>();

const expanded = ref(false);
const loading = ref(false);
const error = ref('');
const loaded = ref(false);
const data = ref<ArtCascadeData | null>(null);

// Lazy: fetch only the first time the section is opened for a given period.
watch(() => props.to, () => { loaded.value = false; data.value = null; });

async function onToggle(event: Event): Promise<void> {
    expanded.value = (event.target as HTMLDetailsElement).open;
    if (!expanded.value || loaded.value) return;

    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/api/data6/analysis/art-cascade?to=${props.to}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(`Request failed (${response.status})`);
        data.value = await response.json();
        loaded.value = true;
    } catch {
        error.value = 'Could not compute the ART cascade analysis — try again.';
    } finally {
        loading.value = false;
    }
}

const outcomeInsight = computed(() => (data.value ? describeCategorical(
    data.value.cohort_outcomes.by.map((b) => ({ label: b.label, value: b.value })),
    { unit: 'count', noun: 'clients', dimension: 'outcome' },
) : ''));
const retentionInsight = computed(() => (data.value && data.value.retention_trend.length >= 2
    ? describeTrend(data.value.retention_trend.map((r) => r.label), data.value.retention_trend.map((r) => r.pct), { unit: 'percent', noun: 'retention' })
    : ''));
function insightParagraphs(text: string): string[] {
    return text.split('\n\n').filter(Boolean);
}

// ---- charts (dataviz reference palette) ------------------------------------
const seriesBlue = '#2a78d6';
const inkMuted = '#898781';
const inkSecondary = '#52514e';
const gridHairline = '#e1e0d9';

const outcomeChartRef = ref<ApexChartHandle | null>(null);
const outcomeColors: Record<string, string> = {
    Active: '#0ca30c', LTFU: '#fab219', 'Transferred out': '#31577a', Died: '#d03b3b',
    'Opted out': '#8f6115', 'Other / status unclear': '#a6b1aa', 'No follow-up recorded': '#c3c2b7',
};
// A cohort outcome breakdown is one total split into parts, so it reads as
// ONE stacked horizontal bar with a colored segment per outcome, not seven
// separate bars — the "part-to-whole" job, not "compare seven magnitudes".
// Colors are the status palette (Active=good, LTFU=warning, Died=critical),
// not generic categorical hues, since these are genuine status categories.
const outcomeOptions = computed(() => {
    const items = data.value?.cohort_outcomes.by.filter((b) => b.value > 0) ?? [];

    return {
        chart: { type: 'bar', stacked: true, toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
        colors: items.map((b) => outcomeColors[b.label] ?? seriesBlue),
        plotOptions: { bar: { horizontal: true, barHeight: '55%' } },
        dataLabels: {
            enabled: true,
            style: { colors: ['#fcfcfb'], fontSize: '11px', fontWeight: 700 },
            formatter: (_v: number, o: { seriesIndex: number }) => (items[o.seriesIndex]?.pct >= 6 ? `${items[o.seriesIndex]?.pct}%` : ''),
        },
        grid: { show: false },
        xaxis: { categories: ['Cohort'], labels: { show: false }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { show: false } },
        legend: { show: true, position: 'bottom', fontSize: '12px', labels: { colors: '#52514e' }, markers: { size: 6 } },
        tooltip: { y: { formatter: (v: number, o: { seriesIndex: number }) => `${items[o.seriesIndex]?.value} clients (${items[o.seriesIndex]?.pct}%)` } },
    };
});
const outcomeSeries = computed(() => (data.value?.cohort_outcomes.by.filter((b) => b.value > 0) ?? []).map((b) => ({ name: b.label, data: [b.value] })));

const retentionChartRef = ref<ApexChartHandle | null>(null);
const retentionOptions = computed(() => ({
    chart: { type: 'line', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
    colors: [seriesBlue],
    stroke: { width: 2.5, curve: 'straight' },
    markers: { size: 4, strokeWidth: 2, strokeColors: '#fcfcfb', hover: { size: 6 } },
    dataLabels: { enabled: true, offsetY: -14, style: { colors: [inkSecondary], fontSize: '11px' }, formatter: (v: number) => (v === null ? '—' : `${v}%`) },
    grid: { borderColor: gridHairline, xaxis: { lines: { show: false } } },
    xaxis: { categories: (data.value?.retention_trend ?? []).map((r) => r.label), labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false } },
    yaxis: { labels: { style: { colors: inkMuted, fontSize: '11px' } }, min: 0, max: 100 },
    legend: { show: false },
    tooltip: { y: { formatter: (v: number, o: { dataPointIndex: number }) => {
        const r = data.value?.retention_trend[o.dataPointIndex];

        return r ? `${v}% (${r.retained}/${r.cohort_size} retained)` : `${v}%`;
    } } },
}));
const retentionSeries = computed(() => [{ name: 'Retention', data: (data.value?.retention_trend ?? []).map((r) => r.pct ?? 0) }]);

async function downloadChartImage(chartRef: Ref<ApexChartHandle | null>, suffix: string, format: 'png' | 'jpg'): Promise<void> {
    const result = await chartRef.value?.dataURI({ scale: 2 });
    const pngUri = result?.imgURI ?? null;
    if (!pngUri) return;

    const filename = `${props.code}_art_cascade_${suffix}_${props.from}_${props.to}.${format}`;
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
function downloadOutcomeChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(outcomeChartRef, 'cohort_outcomes', format);
}
function downloadRetentionChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(retentionChartRef, 'retention_trend', format);
}
function downloadCsv(headers: string[], rows: (string | number | null)[][], suffix: string): void {
    const escape = (v: string | number | null): string => {
        if (v === null || v === undefined) return '';
        const s = String(v);

        return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
    };
    const lines = [headers, ...rows].map((r) => r.map(escape).join(','));
    const blob = new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${props.code}_art_cascade_${suffix}_${props.from}_${props.to}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
}
function downloadOutcomesCsv(): void {
    if (!data.value) return;
    downloadCsv(['Outcome', 'Clients', 'Share (%)'], data.value.cohort_outcomes.by.map((b) => [b.label, b.value, b.pct]), 'cohort_outcomes');
}
function downloadRetentionCsv(): void {
    if (!data.value) return;
    downloadCsv(['Initiation month', 'Cohort size', 'Retained', 'Retention (%)'],
        data.value.retention_trend.map((r) => [r.label, r.cohort_size, r.retained, r.pct]), 'retention_trend');
}
</script>

<template>
    <section class="mt-6 border border-[#d9ded7] bg-[#fcfcfb]">
        <details class="group" @toggle="onToggle">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#e2644b]">ART cascade analysis</p>
                    <h2 class="mt-0.5 font-serif text-lg text-[#173b3b]">What happens to everyone who ever started ART care</h2>
                </div>
                <ChevronDown class="size-5 shrink-0 text-[#a6b1aa] transition group-open:rotate-180" />
            </summary>

            <div class="border-t border-[#d9ded7] px-5 py-5">
                <div v-if="error" class="flex items-center gap-2 bg-[#fff1ed] px-4 py-3 text-sm text-[#b74f3d]">
                    <CircleAlert class="size-4 shrink-0" />{{ error }}
                </div>
                <div v-else-if="loading" class="py-10 text-center text-sm text-[#788681]">Computing the ART cascade — this looks across every client's full history, so it takes a little longer…</div>

                <template v-else-if="data">
                    <!-- 1. Cohort outcome breakdown -->
                    <div class="border border-[#d9ded7] bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-[#244847]">Cohort outcome breakdown</h3>
                                <p class="mt-0.5 text-[11px] text-[#788681]">All {{ data.cohort_outcomes.total.toLocaleString() }} clients ever in ART care, by current status as of {{ to }}.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadOutcomeChart('png')"><Download class="size-3" />PNG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadOutcomeChart('jpg')"><Download class="size-3" />JPG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadOutcomesCsv"><Download class="size-3" />CSV</button>
                            </div>
                        </div>
                        <VueApexCharts ref="outcomeChartRef" type="bar" height="150" :options="outcomeOptions" :series="outcomeSeries" />
                        <div v-if="outcomeInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p v-for="(para, i) in insightParagraphs(outcomeInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                        </div>
                    </div>

                    <!-- 2. Retention trend -->
                    <div class="mt-4 border border-[#d9ded7] bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-[#244847]">12-month retention across recent cohorts</h3>
                                <p class="mt-0.5 text-[11px] text-[#788681]">Each bar is the group initiated in that month; retention is measured once they reach the 12-month mark.</p>
                            </div>
                            <div v-if="data.retention_trend.length" class="flex flex-wrap items-center gap-2">
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadRetentionChart('png')"><Download class="size-3" />PNG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadRetentionChart('jpg')"><Download class="size-3" />JPG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadRetentionCsv"><Download class="size-3" />CSV</button>
                            </div>
                        </div>
                        <VueApexCharts v-if="data.retention_trend.length" ref="retentionChartRef" type="line" height="240" :options="retentionOptions" :series="retentionSeries" />
                        <p v-else class="flex items-start gap-2 py-6 text-xs leading-5 text-[#788681]">
                            <Info class="mt-0.5 size-3.5 shrink-0" />
                            No initiation cohort has reached its 12-month mark yet as of {{ to }} — HTS-recorded ART initiations in this dataset only begin in January 2026, so the earliest eligible cohort matures in January 2027.
                        </p>
                        <div v-if="retentionInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p v-for="(para, i) in insightParagraphs(retentionInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                        </div>
                    </div>

                    <!-- 3. VL coverage among the active cohort -->
                    <div class="mt-4 border border-[#d9ded7] bg-white px-5 py-4">
                        <h3 class="text-sm font-bold text-[#244847]">VL testing coverage, active cohort</h3>
                        <p class="mt-0.5 text-[11px] text-[#788681]">A ratio against the eligible population, not a raw count — the right form is a meter, not a bar.</p>
                        <div class="mt-3">
                            <RateMeter
                                label="VL coverage" :value="data.vl_coverage.pct" color="#2a78d6"
                                :caption="`${data.vl_coverage.tested_12mo} of ${data.vl_coverage.active_total} currently-active clients tested for VL in the trailing 12 months`"
                                :filename="`${code}_art_cascade_vl_coverage_${from}_${to}`" />
                        </div>
                    </div>

                    <!-- How this analysis was done -->
                    <details class="mt-4 border border-[#d9ded7] bg-[#fbfaf7] px-4 py-3">
                        <summary class="flex cursor-pointer items-center gap-2 text-xs font-bold text-[#244847]">
                            <HelpCircle class="size-4 text-[#e2644b]" />How this analysis was done, and why
                        </summary>
                        <div class="mt-3 space-y-3 text-[12px] leading-5 text-[#52655f]">
                            <p>
                                <strong class="text-[#244847]">1. Cohort outcome breakdown.</strong> Fields: <code class="font-mono text-[11px]">artr_access</code>, <code class="font-mono text-[11px]">art_review_date</code>, <code class="font-mono text-[11px]">art_final_outcome</code>, <code class="font-mono text-[11px]">art_next_review_date</code>.
                                Every client with an ART registration or any ART follow-up is taken as one cohort, then classified once by their latest visit on or before the selected date: Died/Transferred out/Opted out from <code class="font-mono text-[11px]">art_final_outcome</code>; otherwise Active or LTFU from whether <code class="font-mono text-[11px]">art_next_review_date</code> is more than 28 days overdue — the exact rule AHP008 and AHP010 already use, so those two buckets are built to equal AHP008 and AHP010 for the same date exactly.
                                <strong>Why a cohort snapshot instead of the indicator's own period counts:</strong> AHP008/010/012/013 each count events or status <em>within one reporting period</em> in isolation, so they can't show what share of the whole programme ends up in each outcome, and a client who transferred out before the period even started is invisible to AHP012 that period. A full-cohort, point-in-time snapshot answers "of everyone we've ever put on ART, where do they stand today" — the question a manager actually asks. It also surfaces groups AHP008/010 silently drop from both of their counts: clients "Opted out," and clients whose latest visit doesn't cleanly fit Active or LTFU — most often because no next-appointment date was recorded — grouped honestly as "Other / status unclear" rather than guessed into one bucket or the other.
                            </p>
                            <p>
                                <strong class="text-[#244847]">2. Retention across recent cohorts.</strong> Fields: <code class="font-mono text-[11px]">hts_art_init</code>, <code class="font-mono text-[11px]">hts_hiv_date</code>, <code class="font-mono text-[11px]">art_review_date</code>, <code class="font-mono text-[11px]">art_final_outcome</code>.
                                For each of the last 6 calendar months whose 12-month mark has already passed, the cohort is everyone initiated that month (<code class="font-mono text-[11px]">hts_art_init = 'Y'</code>); a client counts as retained if they have an ART visit 9–15 months after initiation and were not recorded Died or Transferred out within the first 12 months — the same formula AHP009 already uses for one month, extended across six.
                                <strong>Why a trend instead of AHP009's single number:</strong> one retention percentage can't say whether the programme is improving or slipping; six consecutive cohorts side by side can. A single point also hides whether a good/bad number is one unusual month or a genuine pattern.
                            </p>
                            <p>
                                <strong class="text-[#244847]">3. VL testing coverage.</strong> Fields: <code class="font-mono text-[11px]">art_viral_load</code>, <code class="font-mono text-[11px]">art_vl_collect_date</code>.
                                Denominator = the Active bucket from (1); numerator = how many of them have a VL sample collected in the trailing 12 months.
                                <strong>Why a coverage ratio instead of AHP014's raw count:</strong> AHP014 counts VL tests done in the period with no denominator, so a rising count could mean better testing or just a bigger active cohort. Dividing by who is actually eligible to be tested turns it into a real coverage measure — the number that tells a manager whether testing is keeping pace with the people who need it.
                            </p>
                        </div>
                    </details>
                </template>
            </div>
        </details>
    </section>
</template>
