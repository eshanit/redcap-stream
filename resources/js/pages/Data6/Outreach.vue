<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, ArrowRight, CircleAlert, Download, HelpCircle, Lightbulb, RefreshCw, Users } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { type BreadcrumbItem } from '@/types';
import { describeCategorical } from '@/composables/useChartInsights';

interface WorklistRow {
    record: string;
    program: string;
    reason: string;
    facility: string | null;
    district: string | null;
    last_visit: string | null;
    next_appointment: string | null;
    days_overdue: number;
}
interface Worklist {
    as_of: string;
    art: WorklistRow[];
    prep: WorklistRow[];
    pnc_mother: WorklistRow[];
    pnc_baby: WorklistRow[];
}

defineProps<{ appTitle: string }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'AHP overview', href: '/data6' },
    { title: 'Outreach worklist', href: '/data6/outreach' },
];

// ---- data -------------------------------------------------------------
const loading = ref(false);
const error = ref('');
const data = ref<Worklist | null>(null);

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const response = await fetch('/api/data6/outreach', { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(`Request failed (${response.status})`);
        data.value = await response.json();
    } catch {
        error.value = 'Could not compute the outreach worklist — try Refresh.';
    } finally {
        loading.value = false;
    }
}
onMounted(load);

// ---- combined rows + client-side filters -------------------------------
const allRows = computed<WorklistRow[]>(() => (data.value ? [...data.value.art, ...data.value.prep, ...data.value.pnc_mother, ...data.value.pnc_baby] : []));
const programs = computed(() => ['All', ...new Set(allRows.value.map((r) => r.program))]);
const activeProgram = ref('All');
const facilities = computed(() => [...new Set(allRows.value.map((r) => r.facility).filter((f): f is string => !!f))].sort());
const activeFacility = ref('');

const filteredRows = computed(() => allRows.value
    .filter((r) => activeProgram.value === 'All' || r.program === activeProgram.value)
    .filter((r) => !activeFacility.value || r.facility === activeFacility.value)
    .sort((a, b) => b.days_overdue - a.days_overdue));

const uniqueClients = computed(() => new Set(allRows.value.map((r) => r.record)).size);

// ---- "overdue by facility" chart (combined, top 15) --------------------
const seriesBlue = '#2a78d6';
const inkMuted = '#898781';
const inkSecondary = '#52514e';
const gridHairline = '#e1e0d9';

const byFacility = computed(() => {
    const counts = new Map<string, number>();
    for (const r of allRows.value) {
        if (!r.facility) continue;
        counts.set(r.facility, (counts.get(r.facility) ?? 0) + 1);
    }

    return [...counts.entries()]
        .map(([label, value]) => ({ label, value }))
        .sort((a, b) => b.value - a.value)
        .slice(0, 15);
});
const facilityOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
    colors: [seriesBlue],
    plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4, borderRadiusApplication: 'end' } },
    dataLabels: { enabled: true, offsetX: 26, style: { colors: [inkSecondary], fontSize: '11px' }, formatter: (v: number) => v.toLocaleString() },
    grid: { borderColor: gridHairline, yaxis: { lines: { show: false } } },
    xaxis: { categories: byFacility.value.map((f) => f.label), labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false } },
    yaxis: { labels: { style: { colors: inkSecondary, fontSize: '12px' } } },
    legend: { show: false },
    tooltip: { y: { formatter: (v: number) => `${v.toLocaleString()} flagged clients` } },
}));
const facilitySeries = computed(() => [{ name: 'Flagged clients', data: byFacility.value.map((f) => f.value) }]);
const facilityInsight = computed(() => describeCategorical(byFacility.value, { unit: 'count', noun: 'flagged clients', dimension: 'facility' }));
function insightParagraphs(text: string): string[] { return text.split('\n\n').filter(Boolean); }

interface ApexChartHandle { dataURI(options?: { scale?: number }): Promise<{ imgURI?: string }>; }
const facilityChartRef = ref<ApexChartHandle | null>(null);
async function downloadFacilityChart(format: 'png' | 'jpg'): Promise<void> {
    const result = await facilityChartRef.value?.dataURI({ scale: 2 });
    const pngUri = result?.imgURI ?? null;
    if (!pngUri) return;
    const filename = `outreach_by_facility.${format}`;
    if (format === 'png') {
        const link = document.createElement('a');
        link.href = pngUri;
        link.download = filename;
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
        link.download = filename;
        link.click();
    };
    img.src = pngUri;
}
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
function downloadFacilityCsv(): void {
    downloadCsv(['Facility', 'Flagged clients'], byFacility.value.map((f) => [f.label, f.value]), 'outreach_by_facility');
}
function downloadWorklistCsv(): void {
    downloadCsv(
        ['Record', 'Program', 'Reason', 'Facility', 'District', 'Last visit', 'Next appointment', 'Days overdue'],
        allRows.value.map((r) => [r.record, r.program, r.reason, r.facility, r.district, r.last_visit, r.next_appointment, r.days_overdue]),
        'outreach_worklist',
    );
}

// "Download PDF" = browser print, same mechanism as every other Data6 page.
// No per-button tier check needed here - the whole /data6/outreach route
// already requires Pro+, so every viewer already qualifies for every
// download on this page (same reasoning as Index.vue/PatientFlow.vue).
const generatedOn = new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
function downloadPdf(): void {
    window.print();
}
</script>

<template>
    <Head title="AHP Outreach Worklist" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b] print:bg-white print:text-black">
            <div class="mx-auto max-w-[1500px] px-5 py-7 sm:px-8 lg:px-10 print:max-w-none print:px-0 print:py-0">

                <Link href="/data6" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#55706a] transition hover:text-[#173b3b] print:hidden">
                    <ArrowLeft class="size-3.5" />AHP overview
                </Link>

                <header class="mt-3 flex flex-col justify-between gap-4 border-b border-[#d9ded7] pb-6 lg:flex-row lg:items-end print:break-inside-avoid">
                    <div>
                        <div class="mb-3 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-[#e2644b]">
                            <span class="h-2 w-2 rounded-full bg-[#e2644b]" />{{ appTitle }}
                        </div>
                        <h1 class="font-serif text-4xl leading-tight tracking-tight">Who needs to be chased up</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-[#60716d]">
                            Clients flagged overdue right now across OI/ART, PrEP and PNC — each program by its own
                            recorded signal, not a generic guess. ANC is not shown; see "How this list is built" below.
                        </p>
                        <p v-if="data" class="mt-2 text-xs text-[#60716d] print:block">As of {{ data.as_of }}</p>
                    </div>
                    <div class="flex items-center gap-2 print:hidden">
                        <button class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-5 py-2.5 text-xs font-bold text-[#3c605b] transition hover:bg-white" title="Opens the print dialog — choose &quot;Save as PDF&quot; as the destination" @click="downloadPdf">
                            <Download class="size-4" />Download PDF
                        </button>
                        <button class="rounded-full border border-[#bdc9c3] p-2.5 text-[#3c605b] transition hover:bg-white" title="Refresh" @click="load">
                            <RefreshCw class="size-4" :class="loading ? 'animate-spin' : ''" />
                        </button>
                    </div>
                </header>
                <p class="mt-2 hidden text-xs text-[#60716d] print:block">Report generated {{ generatedOn }}</p>

                <div v-if="error" class="mt-5 flex items-center gap-2 bg-[#fff1ed] px-4 py-3 text-sm text-[#b74f3d]">
                    <CircleAlert class="size-4 shrink-0" />{{ error }}
                </div>
                <div v-else-if="loading" class="mt-8 py-16 text-center text-sm text-[#788681]">Computing the outreach worklist…</div>

                <template v-else-if="data">
                    <!-- Stat tiles -->
                    <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5 print:break-inside-avoid">
                        <div class="border-l-4 border-[#e86d52] bg-[#fcfcfb] px-5 py-4">
                            <div class="flex items-center justify-between text-[#76827e]"><span class="text-xs font-bold uppercase tracking-wider">Unique clients</span><Users class="size-4" /></div>
                            <div class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ uniqueClients.toLocaleString() }}</div>
                            <div class="mt-1 text-xs text-[#788681]">flagged in at least one program</div>
                        </div>
                        <div class="border-l-4 border-[#1f7a73] bg-[#fcfcfb] px-5 py-4">
                            <div class="text-xs font-bold uppercase tracking-wider text-[#76827e]">OI/ART</div>
                            <div class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ data.art.length.toLocaleString() }}</div>
                            <div class="mt-1 text-xs text-[#788681]">next appointment overdue</div>
                        </div>
                        <div class="border-l-4 border-[#c58a32] bg-[#fcfcfb] px-5 py-4">
                            <div class="text-xs font-bold uppercase tracking-wider text-[#76827e]">PrEP</div>
                            <div class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ data.prep.length.toLocaleString() }}</div>
                            <div class="mt-1 text-xs text-[#788681]">overdue or recorded lost</div>
                        </div>
                        <div class="border-l-4 border-[#3c6e91] bg-[#fcfcfb] px-5 py-4">
                            <div class="text-xs font-bold uppercase tracking-wider text-[#76827e]">PNC — mother</div>
                            <div class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ data.pnc_mother.length.toLocaleString() }}</div>
                            <div class="mt-1 text-xs text-[#788681]">missed / lost to follow-up</div>
                        </div>
                        <div class="border-l-4 border-[#8f6115] bg-[#fcfcfb] px-5 py-4">
                            <div class="text-xs font-bold uppercase tracking-wider text-[#76827e]">PNC — baby</div>
                            <div class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ data.pnc_baby.length.toLocaleString() }}</div>
                            <div class="mt-1 text-xs text-[#788681]">infant lost to follow-up</div>
                        </div>
                    </section>

                    <!-- Overdue by facility -->
                    <section class="mt-4 border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-bold text-[#244847]">Flagged clients by facility</h2>
                                <p class="mt-0.5 text-[11px] text-[#788681]">Combined across all four programs, top 15 facilities.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 print:hidden">
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityChart('png')"><Download class="size-3" />PNG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityChart('jpg')"><Download class="size-3" />JPG</button>
                                <button class="inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white" @click="downloadFacilityCsv"><Download class="size-3" />CSV</button>
                            </div>
                        </div>
                        <VueApexCharts v-if="byFacility.length" ref="facilityChartRef" type="bar" :height="Math.max(160, byFacility.length * 30 + 60)" :options="facilityOptions" :series="facilitySeries" />
                        <p v-else class="py-8 text-center text-xs text-[#898781]">Nobody is currently flagged — nothing to chase up.</p>
                        <div v-if="facilityInsight" class="mt-3 border-t border-[#eef0eb] pt-3">
                            <p class="mb-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><Lightbulb class="size-3 text-[#e2644b]" />Insights</p>
                            <p v-for="(para, i) in insightParagraphs(facilityInsight)" :key="i" class="text-[12.5px] leading-5 text-[#52655f]" :class="i > 0 ? 'mt-2' : ''">{{ para }}</p>
                        </div>
                    </section>

                    <!-- Worklist table -->
                    <section class="mt-4">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-3 print:hidden">
                            <div class="flex flex-wrap gap-1 rounded-full border border-[#cbd3cd] bg-white p-1">
                                <button v-for="p in programs" :key="p"
                                    class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                                    :class="activeProgram === p ? 'bg-[#173b3b] text-white' : 'text-[#55706a] hover:bg-[#eef0eb]'"
                                    @click="activeProgram = p">{{ p }}</button>
                            </div>
                            <div class="flex items-center gap-2">
                                <select v-model="activeFacility" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]">
                                    <option value="">All facilities</option>
                                    <option v-for="f in facilities" :key="f" :value="f">{{ f }}</option>
                                </select>
                                <button class="inline-flex items-center gap-1.5 rounded-full bg-[#173b3b] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#285655]" @click="downloadWorklistCsv">
                                    <Download class="size-3.5" />Download worklist (CSV)
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto border border-[#d9ded7] bg-[#fcfcfb] print:overflow-visible">
                            <table class="w-full min-w-[900px] text-left text-xs" style="font-variant-numeric: tabular-nums">
                                <thead class="border-b border-[#d9ded7] bg-[#f0f2ec] text-[10px] font-bold uppercase tracking-wider text-[#5a6f69]">
                                    <tr>
                                        <th class="px-3 py-2.5">Record</th>
                                        <th class="px-3 py-2.5">Program</th>
                                        <th class="px-3 py-2.5">Reason</th>
                                        <th class="px-3 py-2.5">Facility</th>
                                        <th class="px-3 py-2.5">District</th>
                                        <th class="px-3 py-2.5">Last visit</th>
                                        <th class="px-3 py-2.5">Next appointment</th>
                                        <th class="px-3 py-2.5 text-right">Days overdue</th>
                                        <th class="px-3 py-2.5"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in filteredRows" :key="`${row.program}-${row.record}`" class="border-b border-[#eef0eb] text-[#365652] last:border-0 print:break-inside-avoid">
                                        <td class="px-3 py-2 font-mono text-[11px] text-[#244847]">{{ row.record }}</td>
                                        <td class="px-3 py-2">{{ row.program }}</td>
                                        <td class="px-3 py-2">{{ row.reason }}</td>
                                        <td class="px-3 py-2 text-[#52514e]">{{ row.facility ?? '—' }}</td>
                                        <td class="px-3 py-2 text-[#52514e]">{{ row.district ?? '—' }}</td>
                                        <td class="px-3 py-2 text-[#52514e]">{{ row.last_visit ?? '—' }}</td>
                                        <td class="px-3 py-2 text-[#52514e]">{{ row.next_appointment ?? '—' }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-[#0b2c2c]">{{ row.days_overdue.toLocaleString() }}</td>
                                        <td class="px-3 py-2 print:hidden">
                                            <Link :href="`/data6/flow/${row.record}`" class="group inline-flex items-center gap-1 text-[11px] font-bold text-[#3c605b] hover:text-[#173b3b]">
                                                Flow<ArrowRight class="size-3 transition group-hover:translate-x-0.5" />
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="!filteredRows.length"><td colspan="9" class="px-3 py-6 text-center text-[#898781]">No clients match this filter.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <details class="mt-5 border border-[#d9ded7] bg-[#fcfcfb] px-4 py-3 print:break-inside-avoid">
                        <summary class="flex cursor-pointer items-center gap-2 text-xs font-bold text-[#244847]">
                            <HelpCircle class="size-4 text-[#e2644b]" />How this list is built
                        </summary>
                        <div class="mt-2 space-y-2 text-[11.5px] leading-5 text-[#52655f]">
                            <p><strong class="text-[#244847]">OI/ART.</strong> Latest <code class="font-mono text-[11px]">art_next_review_date</code> is more than 28 days overdue, and the client isn't recorded Transferred out / Died / Opted out — the exact rule AHP010 and the ART cascade analysis already use.</p>
                            <p><strong class="text-[#244847]">PrEP.</strong> Either the client's latest <code class="font-mono text-[11px]">prep_follow_up_status</code> is directly recorded "Lost", or their <code class="font-mono text-[11px]">prep_next_visit_date</code> is more than 28 days overdue and they aren't already Opted out / Withdrawn / Transferred out / Died.</p>
                            <p><strong class="text-[#244847]">PNC (mother / baby).</strong> PNC has no next-appointment field in the register at all — flagged only when the clinician's own recorded follow-up status at the last visit is "Missed appointment" or "Lost to follow-up" (mother), or "Infant lost to follow-up" (baby).</p>
                            <p class="flex items-start gap-1.5 text-[#a87524]"><AlertTriangle class="mt-0.5 size-3 shrink-0" />ANC is not included: it has no next-appointment date, and its only follow-up status field is gated to a narrow ART-initiated-ANC subset — not enough to build a reliable list without guessing.</p>
                        </div>
                    </details>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
