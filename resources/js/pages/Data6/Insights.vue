<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CircleAlert, Info, Lightbulb, RefreshCw } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { type BreadcrumbItem } from '@/types';

interface LabelCount { label: string; count: number; pct?: number; dateless?: boolean; }
interface Step { label: string; count: number; pct_of_previous: number | null; }
interface Pathway { origin: string; clients: number; services: { label: string; count: number; pct: number; after_count: number; after_pct: number; dateless: boolean }[]; }
interface Insights {
    period: { from: string; to: string };
    overview: { clients: number; avg_services: number; multi_service_share: number; services_per_client: LabelCount[]; clients_per_service: LabelCount[] };
    co_utilisation: { services: string[]; rows: { service: string; clients: number; cells: { service: string; count: number; pct: number }[] }[] };
    uptake_by_group: { service: string; clients: number; female_pct: number; male_pct: number; young_pct: number; older_pct: number }[];
    entry_points: { new_clients: number; services: LabelCount[] };
    transitions: { clients_with_transition: number; top: LabelCount[] };
    engagement: { clients: number; returning: number; returning_pct: number; median_visits: number; total_visits: number; distribution: LabelCount[] };
    facility_integration: { facility: string; clients: number; avg_services: number; multi_service_pct: number }[];
    sti_pathways: Pathway; hts_pathways: Pathway; opd_pathways: Pathway;
    hiv_cascade: { steps: Step[]; linkage_pct: number | null; median_days_to_art: number | null; time_to_art: LabelCount[]; linkage_by: Record<string, { label: string; positives: number; linked: number; pct: number }[]>; note: string };
    prep_cascade: { steps: Step[]; continuing: number; discontinued: number; sti_screened_pct: number | null; note: string };
    anc_continuum: { bookings: number; bookings_hiv_known_pct: number | null; deliveries: number; institutional_pct: number | null; hiv_pos_mothers: number; hiv_pos_on_art_pct: number | null; anc_clients: number; anc_to_pnc_pct: number | null; anc_with_hts_pct: number | null; pnc_clients: number; pnc_to_fp_pct: number | null };
    mh_pathway: { screened: number; positive: number; positive_pct: number | null; positive_managed_pct: number | null; outcomes: LabelCount[]; substance: number; note: string };
}

defineProps<{ appTitle: string }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'AHP overview', href: '/data6' },
    { title: 'Insights', href: '/data6/insights' },
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
const data = ref<Insights | null>(null);
async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/api/data6/insights?from=${from.value}&to=${to.value}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(`Request failed (${response.status})`);
        data.value = await response.json();
    } catch {
        error.value = 'Could not compute the insights — try Refresh.';
    } finally {
        loading.value = false;
    }
}
onMounted(load);

// ---- chart helpers (dataviz reference palette; single-hue magnitude) --------
const seriesBlue = '#2a78d6';
const inkMuted = '#898781';
const inkSecondary = '#52514e';
const gridHairline = '#e1e0d9';
function hbar(categories: string[], suffix = '') {
    return {
        chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'system-ui, sans-serif', animations: { enabled: false } },
        colors: [seriesBlue],
        plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4, borderRadiusApplication: 'end' } },
        dataLabels: { enabled: true, offsetX: 28, style: { colors: [inkSecondary], fontSize: '11px' }, formatter: (v: number) => `${v.toLocaleString()}${suffix}` },
        grid: { borderColor: gridHairline, yaxis: { lines: { show: false } } },
        xaxis: { categories, labels: { style: { colors: inkMuted, fontSize: '11px' } }, axisBorder: { color: '#c3c2b7' }, axisTicks: { show: false }, max: suffix === '%' ? 100 : undefined },
        yaxis: { labels: { style: { colors: inkSecondary, fontSize: '12px' }, maxWidth: 200 } },
        legend: { show: false },
        tooltip: { y: { formatter: (v: number) => `${v.toLocaleString()}${suffix}` } },
    };
}
const series = (name: string, items: { count?: number; pct?: number }[], key: 'count' | 'pct' = 'count') => [{ name, data: items.map((i) => (i[key] ?? 0)) }];

/** sequential blue for the co-utilisation heatmap cells */
function heatColor(pct: number): string {
    if (pct >= 80) return '#1c5cab';
    if (pct >= 60) return '#2a78d6';
    if (pct >= 40) return '#5598e7';
    if (pct >= 20) return '#9ec5f4';
    if (pct > 0) return '#cde2fb';
    return 'transparent';
}
function heatInk(pct: number): string { return pct >= 60 ? '#ffffff' : '#0b0b0b'; }

const pctText = (v: number | null | undefined) => (v === null || v === undefined ? '—' : `${v}%`);

const headlineFindings = computed(() => {
    const d = data.value;
    if (!d) return [];
    const out: string[] = [];
    const cas = d.hiv_cascade.steps;
    if (cas[1].count > 0) out.push(`Of ${cas[1].count} adolescents who tested HIV positive, ${cas[2].count} (${pctText(d.hiv_cascade.linkage_pct)}) were linked to ART/HIV care${d.hiv_cascade.median_days_to_art !== null ? `, median ${d.hiv_cascade.median_days_to_art} days to linkage` : ''}.`);
    const stiFp = d.sti_pathways.services.find((s) => s.label === 'Family planning');
    const stiHts = d.sti_pathways.services.find((s) => s.label === 'HIV testing');
    if (d.sti_pathways.clients > 0) out.push(`Of ${d.sti_pathways.clients} STI clients, ${pctText(stiHts?.pct ?? 0)} were tested for HIV and ${pctText(stiFp?.pct ?? 0)} also used family planning.`);
    const top = d.entry_points.services[0];
    if (top) out.push(`${top.label} is the most common entry point: ${top.pct}% of the ${d.entry_points.new_clients} adolescents first seen in this period started there.`);
    out.push(`${d.overview.multi_service_share}% of adolescents used more than one service (average ${d.overview.avg_services} per client); ${d.engagement.returning_pct}% came back for a second visit.`);
    if (d.anc_continuum.anc_clients > 0) out.push(`${pctText(d.anc_continuum.anc_with_hts_pct)} of ANC clients also appear in the HIV testing register; ${pctText(d.anc_continuum.anc_to_pnc_pct)} continued into PNC.`);
    return out;
});
</script>

<template>
    <Head title="AHP Insights" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b]">
            <div class="mx-auto max-w-[1400px] px-5 py-7 sm:px-8 lg:px-10">

                <Link href="/data6" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#55706a] transition hover:text-[#173b3b]"><ArrowLeft class="size-3.5" />AHP overview</Link>

                <header class="mt-3 flex flex-col justify-between gap-4 border-b border-[#d9ded7] pb-6 lg:flex-row lg:items-end">
                    <div>
                        <div class="mb-3 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-[#e2644b]"><span class="h-2 w-2 rounded-full bg-[#e2644b]" />{{ appTitle }}</div>
                        <h1 class="font-serif text-4xl leading-tight tracking-tight">How adolescents move through services</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-[#60716d]">
                            Linkage, co-utilisation, journeys and cascades across FCH, OI/ART and OPD — the questions a
                            manager asks that no single indicator answers. Adolescents aged 10–19; each client counted once
                            across projects.
                        </p>
                    </div>
                    <button class="rounded-full border border-[#bdc9c3] p-2.5 text-[#3c605b] transition hover:bg-white" title="Refresh" @click="load"><RefreshCw class="size-4" :class="loading ? 'animate-spin' : ''" /></button>
                </header>

                <section class="mt-5 flex flex-wrap items-end gap-3">
                    <div class="flex flex-wrap gap-1 rounded-full border border-[#cbd3cd] bg-white p-1">
                        <button v-for="preset in presets" :key="preset.key" class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                            :class="activePreset === preset.key ? 'bg-[#173b3b] text-white' : 'text-[#55706a] hover:bg-[#eef0eb]'" @click="applyPreset(preset.key)">{{ preset.label }}</button>
                    </div>
                    <template v-if="activePreset === 'custom'">
                        <label class="text-xs text-[#55706a]">From <input v-model="from" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" /></label>
                        <label class="text-xs text-[#55706a]">To <input v-model="to" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" /></label>
                    </template>
                    <span class="text-xs font-semibold text-[#7b8984]">{{ from }} → {{ to }}</span>
                </section>

                <div v-if="error" class="mt-5 flex items-center gap-2 bg-[#fff1ed] px-4 py-3 text-sm text-[#b74f3d]"><CircleAlert class="size-4 shrink-0" />{{ error }}</div>
                <div v-else-if="loading" class="mt-8 py-14 text-center text-sm text-[#788681]">Building the service-journey picture for {{ from }} → {{ to }}…</div>

                <template v-else-if="data">
                    <!-- Headline findings -->
                    <section class="mt-6 border-l-4 border-[#e86d52] bg-[#fcfcfb] px-6 py-5">
                        <h2 class="flex items-center gap-2 text-sm font-bold text-[#244847]"><Lightbulb class="size-4 text-[#e2644b]" />What stands out</h2>
                        <ul class="mt-3 space-y-2 text-sm leading-6 text-[#365652]">
                            <li v-for="(f, i) in headlineFindings" :key="i" class="flex gap-2"><span class="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-[#e86d52]" />{{ f }}</li>
                        </ul>
                    </section>

                    <!-- HIV cascade -->
                    <section class="mt-6 border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <h2 class="font-serif text-xl text-[#173b3b]">HIV: from testing to suppression</h2>
                        <p class="mt-1 text-xs text-[#788681]">Answers: of adolescents who tested positive, how many got into the ART programme — and how fast?</p>
                        <div class="mt-4 grid gap-5 lg:grid-cols-[1.3fr_1fr]">
                            <div>
                                <div v-for="(step, i) in data.hiv_cascade.steps" :key="step.label" class="mb-2">
                                    <div class="flex items-baseline justify-between text-xs">
                                        <span class="font-semibold text-[#244847]">{{ step.label }}</span>
                                        <span class="text-[#788681]" style="font-variant-numeric: tabular-nums">
                                            <strong class="text-sm text-[#0b2c2c]">{{ step.count.toLocaleString() }}</strong>
                                            <span v-if="step.pct_of_previous !== null" class="ml-1.5">{{ step.pct_of_previous }}% of previous</span>
                                        </span>
                                    </div>
                                    <div class="mt-1 h-3 w-full bg-[#eef0eb]">
                                        <div class="h-3 bg-[#2a78d6]" :style="{ width: `${data.hiv_cascade.steps[0].count ? Math.max(1, (step.count / data.hiv_cascade.steps[0].count) * 100) : 0}%`, opacity: 1 - i * 0.12 }" />
                                    </div>
                                </div>
                                <p class="mt-3 flex items-start gap-1.5 text-[11px] leading-4 text-[#7d8b85]"><Info class="mt-0.5 size-3 shrink-0" />{{ data.hiv_cascade.note }}</p>
                            </div>
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="border border-[#d9ded7] px-4 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Linked to care</p><p class="mt-1 text-2xl font-semibold text-[#0b2c2c]">{{ pctText(data.hiv_cascade.linkage_pct) }}</p></div>
                                    <div class="border border-[#d9ded7] px-4 py-3"><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Median days to ART</p><p class="mt-1 text-2xl font-semibold text-[#0b2c2c]">{{ data.hiv_cascade.median_days_to_art ?? '—' }}</p></div>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-[#244847]">Time from positive test to ART/HIV care</h3>
                                    <VueApexCharts type="bar" height="190" :options="hbar(data.hiv_cascade.time_to_art.map((t) => t.label))" :series="series('Adolescents', data.hiv_cascade.time_to_art)" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div v-for="(rows, dim) in data.hiv_cascade.linkage_by" :key="dim" class="border border-[#eef0eb] px-3 py-2">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Linkage by {{ String(dim).replace('_', ' ') }}</p>
                                <table class="mt-1 w-full text-xs" style="font-variant-numeric: tabular-nums">
                                    <tbody><tr v-for="r in rows" :key="r.label" class="border-b border-[#eef0eb] last:border-0"><td class="py-1 text-[#52514e]">{{ r.label }}</td><td class="py-1 text-right text-[#788681]">{{ r.linked }}/{{ r.positives }}</td><td class="py-1 text-right font-bold text-[#0b2c2c]">{{ r.pct }}%</td></tr></tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <!-- Pathways from a service -->
                    <section class="mt-6 grid gap-4 xl:grid-cols-3">
                        <div v-for="pw in [data.sti_pathways, data.hts_pathways, data.opd_pathways]" :key="pw.origin" class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">After {{ pw.origin }}: what else did they use?</h2>
                            <p class="mt-0.5 text-[11px] text-[#788681]">{{ pw.clients.toLocaleString() }} adolescents used {{ pw.origin }} in the period. Share who also used each service; “after” = dated on/after their first {{ pw.origin }} visit.</p>
                            <table v-if="pw.services.length" class="mt-3 w-full text-xs" style="font-variant-numeric: tabular-nums">
                                <thead class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1 text-left">Service</th><th class="pb-1 text-right">Also used</th><th class="pb-1 text-right">After</th></tr></thead>
                                <tbody>
                                    <tr v-for="s in pw.services" :key="s.label" class="border-t border-[#eef0eb]">
                                        <td class="py-1.5 text-[#52514e]">{{ s.label }}<span v-if="s.dateless" class="ml-1 text-[9px] text-[#a87524]" title="No date field — all-time flag">all-time</span></td>
                                        <td class="py-1.5 text-right"><span class="font-bold text-[#0b2c2c]">{{ s.pct }}%</span> <span class="text-[#788681]">({{ s.count }})</span></td>
                                        <td class="py-1.5 text-right text-[#52514e]">{{ s.dateless ? '—' : `${s.after_pct}%` }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p v-else class="py-6 text-center text-xs text-[#898781]">No {{ pw.origin }} clients in this period.</p>
                        </div>
                    </section>

                    <!-- Co-utilisation heatmap -->
                    <section class="mt-6 border border-[#d9ded7] bg-[#fcfcfb] p-5">
                        <h2 class="font-serif text-xl text-[#173b3b]">Service co-utilisation</h2>
                        <p class="mt-1 text-xs text-[#788681]">Read across a row: of adolescents who used the row service, the % who also used each column service. Mental health, health education and counselling are all-time access flags (no date on those forms).</p>
                        <div class="mt-4 overflow-x-auto">
                            <table class="text-[11px]" style="font-variant-numeric: tabular-nums">
                                <thead><tr><th class="sticky left-0 bg-[#fcfcfb] px-2 py-1 text-left font-bold text-[#244847]">Used…</th><th class="px-1 py-1 text-right text-[#82908a]">n</th><th v-for="s in data.co_utilisation.services" :key="s" class="max-w-[64px] px-1 py-1 text-center align-bottom text-[10px] font-semibold leading-3 text-[#52514e]">{{ s }}</th></tr></thead>
                                <tbody>
                                    <tr v-for="row in data.co_utilisation.rows" :key="row.service">
                                        <td class="sticky left-0 bg-[#fcfcfb] px-2 py-0.5 font-semibold text-[#244847]">{{ row.service }}</td>
                                        <td class="px-1 py-0.5 text-right text-[#788681]">{{ row.clients }}</td>
                                        <td v-for="c in row.cells" :key="c.service" class="p-0.5">
                                            <div class="flex h-8 w-14 items-center justify-center border border-[#fcfcfb]" :style="{ background: c.service === row.service ? '#f0f2ec' : heatColor(c.pct), color: c.service === row.service ? '#898781' : heatInk(c.pct) }" :title="`${row.service} → ${c.service}: ${c.count} of ${row.clients} (${c.pct}%)`">
                                                {{ c.service === row.service ? '·' : `${c.pct}%` }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Journeys: entry points, transitions, engagement -->
                    <section class="mt-6 grid gap-4 xl:grid-cols-3">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Entry points for new adolescents</h2>
                            <p class="mt-0.5 text-[11px] text-[#788681]">First service used by the {{ data.entry_points.new_clients.toLocaleString() }} clients first seen in this period.</p>
                            <VueApexCharts v-if="data.entry_points.services.length" type="bar" :height="Math.max(160, data.entry_points.services.length * 28 + 60)" :options="hbar(data.entry_points.services.map((s) => s.label), '%')" :series="series('Share', data.entry_points.services, 'pct')" />
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Most common service-to-service moves</h2>
                            <p class="mt-0.5 text-[11px] text-[#788681]">Consecutive visits to different services, counted once per client. {{ data.transitions.clients_with_transition }} clients moved between services.</p>
                            <VueApexCharts v-if="data.transitions.top.length" type="bar" :height="Math.max(160, data.transitions.top.length * 26 + 60)" :options="hbar(data.transitions.top.map((t) => t.label))" :series="series('Clients', data.transitions.top)" />
                            <p v-else class="py-6 text-center text-xs text-[#898781]">No multi-service sequences in this period.</p>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Engagement</h2>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <div class="border border-[#eef0eb] px-3 py-2"><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Returning</p><p class="text-xl font-semibold text-[#0b2c2c]">{{ data.engagement.returning_pct }}%</p><p class="text-[10px] text-[#788681]">{{ data.engagement.returning }} of {{ data.engagement.clients }}</p></div>
                                <div class="border border-[#eef0eb] px-3 py-2"><p class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Visits</p><p class="text-xl font-semibold text-[#0b2c2c]">{{ data.engagement.total_visits.toLocaleString() }}</p><p class="text-[10px] text-[#788681]">median {{ data.engagement.median_visits }} per client</p></div>
                            </div>
                            <VueApexCharts type="bar" height="170" :options="hbar(data.engagement.distribution.map((d) => d.label))" :series="series('Clients', data.engagement.distribution)" />
                        </div>
                    </section>

                    <!-- Continua -->
                    <section class="mt-6 grid gap-4 lg:grid-cols-3">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">PrEP continuum</h2>
                            <table class="mt-2 w-full text-xs" style="font-variant-numeric: tabular-nums"><tbody>
                                <tr v-for="s in data.prep_cascade.steps" :key="s.label" class="border-b border-[#eef0eb] last:border-0"><td class="py-1.5 text-[#52514e]">{{ s.label }}</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ s.count }}</td><td class="py-1.5 text-right text-[#788681]">{{ s.pct_of_previous === null ? '' : `${s.pct_of_previous}%` }}</td></tr>
                                <tr class="border-t border-[#d9ded7]"><td class="py-1.5 text-[#52514e]">Continuing / discontinued</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]" colspan="2">{{ data.prep_cascade.continuing }} / {{ data.prep_cascade.discontinued }}</td></tr>
                                <tr><td class="py-1.5 text-[#52514e]">STI screened at PrEP registration</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]" colspan="2">{{ pctText(data.prep_cascade.sti_screened_pct) }}</td></tr>
                            </tbody></table>
                            <p class="mt-2 text-[10px] leading-4 text-[#7d8b85]">{{ data.prep_cascade.note }}</p>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Maternal continuum (adolescent mothers)</h2>
                            <table class="mt-2 w-full text-xs" style="font-variant-numeric: tabular-nums"><tbody>
                                <tr class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">New ANC bookings</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ data.anc_continuum.bookings }}</td></tr>
                                <tr class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">…with HIV status documented at booking</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ pctText(data.anc_continuum.bookings_hiv_known_pct) }}</td></tr>
                                <tr class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">ANC clients also in the HIV-testing register</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ pctText(data.anc_continuum.anc_with_hts_pct) }}</td></tr>
                                <tr class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">ANC clients continuing into PNC</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ pctText(data.anc_continuum.anc_to_pnc_pct) }}</td></tr>
                                <tr class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">Deliveries registered / institutional</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ data.anc_continuum.deliveries }} / {{ pctText(data.anc_continuum.institutional_pct) }}</td></tr>
                                <tr class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">HIV-positive mothers on ART at delivery</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ data.anc_continuum.hiv_pos_mothers }} · {{ pctText(data.anc_continuum.hiv_pos_on_art_pct) }}</td></tr>
                                <tr><td class="py-1.5 text-[#52514e]">PNC mothers taking up family planning</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ pctText(data.anc_continuum.pnc_to_fp_pct) }}</td></tr>
                            </tbody></table>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Mental health pathway</h2>
                            <table class="mt-2 w-full text-xs" style="font-variant-numeric: tabular-nums"><tbody>
                                <tr class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">Screened</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ data.mh_pathway.screened.toLocaleString() }}</td></tr>
                                <tr class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">Screened positive</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ data.mh_pathway.positive }} · {{ pctText(data.mh_pathway.positive_pct) }}</td></tr>
                                <tr class="border-b border-[#eef0eb]"><td class="py-1.5 text-[#52514e]">Positives referred/managed</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ pctText(data.mh_pathway.positive_managed_pct) }}</td></tr>
                                <tr v-for="o in data.mh_pathway.outcomes" :key="o.label" class="border-b border-[#eef0eb]"><td class="py-1.5 pl-3 text-[#788681]">{{ o.label }}</td><td class="py-1.5 text-right text-[#52514e]">{{ o.count }}</td></tr>
                                <tr><td class="py-1.5 text-[#52514e]">Substance use identified</td><td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ data.mh_pathway.substance }}</td></tr>
                            </tbody></table>
                            <p class="mt-2 text-[10px] leading-4 text-[#7d8b85]">{{ data.mh_pathway.note }}</p>
                        </div>
                    </section>

                    <!-- Equity + facilities -->
                    <section class="mt-6 grid gap-4 xl:grid-cols-2">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Who uses each service</h2>
                            <p class="mt-0.5 text-[11px] text-[#788681]">Share of each service's adolescent clients by sex and age band — spot services that boys or 10–14s are not reaching.</p>
                            <div class="mt-3 overflow-x-auto">
                                <table class="w-full min-w-[520px] text-xs" style="font-variant-numeric: tabular-nums">
                                    <thead class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1 text-left">Service</th><th class="pb-1 text-right">Clients</th><th class="pb-1 text-right">Female</th><th class="pb-1 text-right">Male</th><th class="pb-1 text-right">10–14</th><th class="pb-1 text-right">15–19</th></tr></thead>
                                    <tbody>
                                        <tr v-for="u in data.uptake_by_group" :key="u.service" class="border-t border-[#eef0eb]">
                                            <td class="py-1.5 text-[#52514e]">{{ u.service }}</td><td class="py-1.5 text-right text-[#788681]">{{ u.clients }}</td>
                                            <td class="py-1.5 text-right">{{ u.female_pct }}%</td><td class="py-1.5 text-right" :class="u.male_pct < 10 ? 'text-[#b74f3d] font-bold' : ''">{{ u.male_pct }}%</td>
                                            <td class="py-1.5 text-right" :class="u.young_pct < 10 ? 'text-[#b74f3d] font-bold' : ''">{{ u.young_pct }}%</td><td class="py-1.5 text-right">{{ u.older_pct }}%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5">
                            <h2 class="text-sm font-bold text-[#244847]">Service integration by facility</h2>
                            <p class="mt-0.5 text-[11px] text-[#788681]">Average number of different services per adolescent, and share using more than one — a proxy for how well facilities link services.</p>
                            <table class="mt-3 w-full text-xs" style="font-variant-numeric: tabular-nums">
                                <thead class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]"><tr><th class="pb-1 text-left">Facility</th><th class="pb-1 text-right">Clients</th><th class="pb-1 text-right">Avg services</th><th class="pb-1 text-right">Multi-service</th></tr></thead>
                                <tbody>
                                    <tr v-for="f in data.facility_integration" :key="f.facility" class="border-t border-[#eef0eb]">
                                        <td class="py-1.5 font-semibold text-[#244847]">{{ f.facility }}</td><td class="py-1.5 text-right text-[#788681]">{{ f.clients }}</td>
                                        <td class="py-1.5 text-right font-bold text-[#0b2c2c]">{{ f.avg_services }}</td><td class="py-1.5 text-right">{{ f.multi_service_pct }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
