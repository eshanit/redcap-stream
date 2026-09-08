<script setup lang="ts">
import { ChevronDown, CircleAlert, Download, HelpCircle } from 'lucide-vue-next';
import { computed, ref, watch, type Ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import RateMeter from '@/components/data6/RateMeter.vue';
import { describeCategorical } from '@/composables/useChartInsights';
import { useTier } from '@/composables/useTier';

const { canDownload } = useTier();

interface GapBucket { label: string; value: number; }
interface GapRecord { record: string; source: string; date: string; facility: string; district: string; result?: string; }
interface GapSection {
    evidenced_elsewhere: number; in_hts: number; gap: number; gap_pct: number | null;
    by_source: GapBucket[]; by_facility: GapBucket[]; by_district: GapBucket[]; records: GapRecord[];
}
interface ReconciliationData { hiv_testing: GapSection; art_initiation: GapSection; }
interface ApexChartHandle {
    dataURI(options?: { scale?: number }): Promise<{ imgURI?: string }>;
}

const props = defineProps<{ code: string; from: string; to: string }>();

const section = computed<'hiv_testing' | 'art_initiation'>(() => (props.code === 'AHP007' ? 'art_initiation' : 'hiv_testing'));
const sectionLabel = computed(() => (section.value === 'art_initiation' ? 'ART initiation' : 'HIV testing'));
const evidenceLabel = computed(() => (section.value === 'art_initiation' ? 'evidenced ART initiation' : 'evidenced HIV testing'));
const officialLabel = computed(() => (section.value === 'art_initiation' ? 'hts_art_init' : 'hts_tested'));

const expanded = ref(false);
const loading = ref(false);
const error = ref('');
const loaded = ref(false);
const data = ref<ReconciliationData | null>(null);
const current = computed<GapSection | null>(() => (data.value ? data.value[section.value] : null));

watch(() => props.to, () => { loaded.value = false; data.value = null; });

async function onToggle(event: Event): Promise<void> {
    expanded.value = (event.target as HTMLDetailsElement).open;
    if (!expanded.value || loaded.value) return;

    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/api/data6/analysis/hts-reconciliation?from=${props.from}&to=${props.to}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(`Request failed (${response.status})`);
        data.value = await response.json();
        loaded.value = true;
    } catch {
        error.value = 'Could not compute the reconciliation — try again.';
    } finally {
        loading.value = false;
    }
}

function insight(dimension: string, items: GapBucket[]): string {
    return items.length >= 2 ? describeCategorical(items, { unit: 'count', noun: 'clients', dimension }) : '';
}
function insightParagraphs(text: string): string[] {
    return text.split('\n\n').filter(Boolean);
}

// ---- charts (dataviz reference palette) ------------------------------------
const seriesBlue = '#2a78d6';
const inkMuted = '#898781';
const inkSecondary = '#52514e';
const gridHairline = '#e1e0d9';

function hbarOptions(items: GapBucket[]) {
    return {
        chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
        colors: [seriesBlue],
        plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 4, borderRadiusApplication: 'end' } },
        dataLabels: { enabled: true, offsetX: 26, style: { colors: [inkSecondary], fontSize: '11px' }, formatter: (v: number) => v.toLocaleString() },
        grid: { borderColor: gridHairline, yaxis: { lines: { show: false } } },
        xaxis: { categories: items.map((b) => b.label), labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: inkSecondary, fontSize: '12px' } } },
        legend: { show: false },
        tooltip: { y: { formatter: (v: number) => `${v.toLocaleString()} clients` } },
    };
}
const sourceSeries = computed(() => [{ name: 'Clients', data: (current.value?.by_source ?? []).map((b) => b.value) }]);
const facilitySeries = computed(() => [{ name: 'Clients', data: (current.value?.by_facility ?? []).map((b) => b.value) }]);
const sourceInsight = computed(() => insight('source register', current.value?.by_source ?? []));
const facilityInsight = computed(() => insight('facility', current.value?.by_facility ?? []));

const sourceChartRef = ref<ApexChartHandle | null>(null);
const facilityChartRef = ref<ApexChartHandle | null>(null);

async function downloadChartImage(chartRef: Ref<ApexChartHandle | null>, suffix: string, format: 'png' | 'jpg'): Promise<void> {
    const result = await chartRef.value?.dataURI({ scale: 2 });
    const pngUri = result?.imgURI ?? null;
    if (!pngUri) return;

    const filename = `${props.code}_reconciliation_${suffix}_${props.from}_${props.to}.${format}`;
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
function downloadSourceChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(sourceChartRef, 'by_source', format);
}
function downloadFacilityChart(format: 'png' | 'jpg'): Promise<void> {
    return downloadChartImage(facilityChartRef, 'by_facility', format);
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
    link.download = `${props.code}_reconciliation_${suffix}_${props.from}_${props.to}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
}
function downloadRecordsCsv(): void {
    if (!current.value) return;
    const hasResult = section.value === 'hiv_testing';
    const headers = hasResult
        ? ['Record', 'Source', 'Date', 'Facility', 'District', 'Result']
        : ['Record', 'Source', 'Date', 'Facility', 'District'];
    const rows = current.value.records.map((r) => (hasResult
        ? [r.record, r.source, r.date, r.facility, r.district, r.result ?? '']
        : [r.record, r.source, r.date, r.facility, r.district]));
    downloadCsv(headers, rows, 'records');
}
</script>

<template>
    <section class="mt-6 border border-[#d9ded7] bg-[#fcfcfb]">
        <details class="group" @toggle="onToggle">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#e2644b]">HTS reconciliation</p>
                    <h2 class="mt-0.5 font-serif text-lg text-[#173b3b]">Clients with {{ evidenceLabel }} evidence missing from the HTS register</h2>
                </div>
                <ChevronDown class="size-5 shrink-0 text-[#a6b1aa] transition group-open:rotate-180" />
            </summary>

            <div class="border-t border-[#d9ded7] px-5 py-5">
                <div v-if="error" class="flex items-center gap-2 bg-[#fff1ed] px-4 py-3 text-sm text-[#b74f3d]">
                    <CircleAlert class="size-4 shrink-0" />{{ error }}
                </div>
                <div v-else-if="loading" class="py-10 text-center text-sm text-[#788681]">Comparing the HTS register against every other evidence source for {{ from }} → {{ to }}…</div>

                <template v-else-if="current">
                    <!-- Headline: a ratio against a limit reads as a meter, not a bare number -->
                    <div class="flex flex-wrap items-center justify-between gap-6 border border-[#d9ded7] bg-white px-5 py-4 print:break-inside-avoid">
                        <div class="flex flex-wrap items-center gap-4">
                            <RateMeter
                                label="Missing from HTS" :value="current.gap_pct" color="#fab219"
                                :caption="`${current.gap.toLocaleString()} of ${current.evidenced_elsewhere.toLocaleString()} clients with evidence elsewhere`"
                                :filename="`${code}_reconciliation_gap_rate_${from}_${to}`" />
                            <p class="text-xs text-[#82908a]">{{ current.in_hts.toLocaleString() }} of them are also logged in {{ officialLabel }} this period.</p>
                        </div>
                        <button v-if="current.records.length && canDownload" class="inline-flex items-center gap-1.5 rounded-full bg-[#173b3b] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#285655] print:hidden" @click="downloadRecordsCsv">
                            <Download class="size-3.5" />Download reconciliation list (CSV)
                        </button>
                    </div>

                    <template v-if="current.gap > 0">
                        <!-- By source -->
                        <div class="mt-4 border border-[#d9ded7] bg-white p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <h3 class="text-sm font-bold text-[#244847]">Where the missing evidence comes from</h3>
                                <div v-if="canDownload" class="flex flex-wrap items-center gap-2 print:hidden">
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadSourceChart('png')"><Download class="size-3" />PNG</button>
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadSourceChart('jpg')"><Download class="size-3" />JPG</button>
                                </div>
                            </div>
                            <VueApexCharts ref="sourceChartRef" type="bar" :height="Math.max(140, current.by_source.length * 40 + 60)" :options="hbarOptions(current.by_source)" :series="sourceSeries" />
                            <div v-if="sourceInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                <p v-for="(para, i) in insightParagraphs(sourceInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                            </div>
                        </div>

                        <!-- By facility -->
                        <div class="mt-4 border border-[#d9ded7] bg-white p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <h3 class="text-sm font-bold text-[#244847]">Where the gap is concentrated</h3>
                                <div v-if="canDownload" class="flex flex-wrap items-center gap-2 print:hidden">
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityChart('png')"><Download class="size-3" />PNG</button>
                                    <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityChart('jpg')"><Download class="size-3" />JPG</button>
                                </div>
                            </div>
                            <VueApexCharts ref="facilityChartRef" type="bar" :height="Math.max(140, current.by_facility.length * 30 + 60)" :options="hbarOptions(current.by_facility)" :series="facilitySeries" />
                            <div v-if="facilityInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                                <p v-for="(para, i) in insightParagraphs(facilityInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                            </div>
                        </div>
                    </template>
                    <p v-else class="mt-4 border border-dashed border-[#c7d8ce] bg-[#e7f0e9] px-4 py-3 text-xs text-[#286057]">
                        No gap found for {{ from }} → {{ to }}: every client with {{ evidenceLabel }} evidence elsewhere is also logged in the HTS register this period.
                    </p>

                    <!-- How this analysis was done -->
                    <details class="mt-4 border border-[#d9ded7] bg-[#fbfaf7] px-4 py-3">
                        <summary class="flex cursor-pointer items-center gap-2 text-xs font-bold text-[#244847]">
                            <HelpCircle class="size-4 text-[#e2644b]" />Why this reconciliation, and why it's period-scoped
                        </summary>
                        <div class="mt-3 space-y-3 text-[12px] leading-5 text-[#52655f]">
                            <p v-if="section === 'hiv_testing'">
                                <strong class="text-[#244847]">Fields.</strong> Evidence of testing elsewhere: <code class="font-mono text-[11px]">sti_hiv_test</code>/<code class="font-mono text-[11px]">sti_visit_date</code>, <code class="font-mono text-[11px]">prep_hiv_test</code>/<code class="font-mono text-[11px]">prep_visit_date</code>, <code class="font-mono text-[11px]">anc_hiv_test_results</code>/<code class="font-mono text-[11px]">anc_date</code>, <code class="font-mono text-[11px]">artr_first_hiv_test</code>. Checked against <code class="font-mono text-[11px]">hts_tested</code>/<code class="font-mono text-[11px]">hts_hiv_date</code> in the HTS register.
                                A client counts as a gap when they have at least one of the above in the selected period but no <code class="font-mono text-[11px]">hts_tested = 'Y'</code> row of their own in that same period. Where a client has evidence from more than one alternate source, only their earliest is listed.
                            </p>
                            <p v-else>
                                <strong class="text-[#244847]">Fields.</strong> Evidence of initiation elsewhere: <code class="font-mono text-[11px]">art_arv_status = '2'</code> ("Start ARV") from an OI/ART follow-up visit, dated by <code class="font-mono text-[11px]">art_review_date</code>. Checked against <code class="font-mono text-[11px]">hts_art_init</code> in the HTS register.
                            </p>
                            <p>
                                <strong class="text-[#244847]">Why this isn't a new indicator.</strong> AHP004a/005a/006a already report the combined total across every entry point — that number is a programme metric. This is different: it's a data-quality worklist. A gap count alone tells the M&E officer a problem exists; the record list here (record ID, source, date, facility, district — no names) tells them exactly who to go find in the register.
                            </p>
                            <p>
                                <strong class="text-[#244847]">Why period-scoped on both sides.</strong> Both "evidenced elsewhere" and "in HTS" are checked within the <em>same</em> selected period, not against a client's full history. That answers the question a routine reconciliation actually needs — "of what I have other evidence of this period, how much also got logged in HTS this period" — rather than a lifetime check that would hide recent gaps behind an old HTS entry from years ago.
                            </p>
                        </div>
                    </details>
                </template>
            </div>
        </details>
    </section>
</template>
