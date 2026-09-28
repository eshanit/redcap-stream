<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CircleAlert, Download, FileSpreadsheet, Info, RefreshCw, Route } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { useTier } from '@/composables/useTier';
import PathwayFlow from './PathwayFlow.vue';
import PathwayMembers from './PathwayMembers.vue';

// Every export on this page (Excel, CSV, chart images, PDF) is Pro+ only.
const { isProPlus } = useTier();

interface Cell { service: string; self: boolean; reached: number; eligible: number; suppressed: boolean; pct: number | null; median_days: number | null; }
interface Row { service: string; clients: number; moved_on: number; moved_on_pct: number; cells: Cell[]; }
interface Headline extends Cell { from: string; to: string; }
interface PathRow { steps: string[]; clients: number; pct: number; median_days: number; small: boolean; }
interface Payload {
    as_of: string;
    window_months: number;
    min_cell: number;
    summary: { cohort: number; too_recent: number; followed: number; moved_on: number; moved_on_pct: number; same_day_only: number; same_day_only_pct: number; distinct_paths: number };
    progression: { columns: string[]; rows: Row[]; headlines: Headline[] };
    flow: { nodes: { id: string; stage: number; label: string; value: number }[]; links: { source: string; target: string; value: number }[]; clients: number; longer_than_shown: number };
    paths: { rows: PathRow[]; other_paths: number; other_clients: number; other_pct: number };
    same_day: { rows: { services: string[]; clients: number; small: boolean }[]; other_combos: number; other_clients: number };
}

const props = defineProps<{
    appTitle: string;
    filterOptions: { districts: string[]; facilities: string[] };
    windows: number[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'AHP overview', href: '/data6' },
    { title: 'Patient pathways', href: '/data6/pathways' },
];

// ---- filters ----------------------------------------------------------------
const today = new Date().toISOString().slice(0, 10);
const presets = [
    { key: 'programme', label: 'Since Jan 2025', range: () => ({ from: '2025-01-01', to: today }) },
    { key: 'last_12', label: 'Last 12 months', range: () => { const d = new Date(); d.setFullYear(d.getFullYear() - 1); return { from: d.toISOString().slice(0, 10), to: today }; } },
    { key: 'all', label: 'All time', range: () => ({ from: '2000-01-01', to: today }) },
    { key: 'custom', label: 'Custom', range: null },
];
const activePreset = ref('programme');
const from = ref('2025-01-01');
const to = ref(today);
const windowMonths = ref(6);
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

// ---- data -------------------------------------------------------------------
const loading = ref(false);
const error = ref('');
const data = ref<Payload | null>(null);

function queryString(): string {
    const params = new URLSearchParams({ from: from.value, to: to.value, window: String(windowMonths.value), age_band: ageBand.value });
    if (district.value) params.set('district', district.value);
    if (facility.value) params.set('facility', facility.value);
    if (gender.value) params.set('gender', gender.value);
    return params.toString();
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/api/data6/pathways?${queryString()}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error(`Request failed (${response.status})`);
        data.value = await response.json();
    } catch {
        error.value = 'Could not compute the pathways. Check the database connection and try again.';
    } finally {
        loading.value = false;
    }
}
onMounted(load);

// ---- progression heatmap (sequential blue, dataviz reference ramp) ------------
// Bins keep text readable: dark ink on the light steps, white on the dark ones.
const bins = [
    { min: 0, max: 0, bg: '#fcfcfb', ink: '#52514e', label: '0%' },
    { min: 0, max: 10, bg: '#cde2fb', ink: '#0b2c2c', label: 'under 10%' },
    { min: 10, max: 20, bg: '#9ec5f4', ink: '#0b2c2c', label: '10–20%' },
    { min: 20, max: 35, bg: '#6da7ec', ink: '#0b2c2c', label: '20–35%' },
    { min: 35, max: 50, bg: '#256abf', ink: '#ffffff', label: '35–50%' },
    { min: 50, max: Infinity, bg: '#184f95', ink: '#ffffff', label: '50%+' },
];
function binFor(pct: number) {
    if (pct === 0) return bins[0];
    return bins.slice(1).find((b) => pct < b.max) ?? bins[bins.length - 1];
}
function cellTitle(row: Row, cell: Cell): string {
    if (cell.self) return `${row.service} is the entry service for this row`;
    if (cell.suppressed) return `Fewer than ${data.value?.min_cell} eligible adolescents - not shown`;
    const median = cell.median_days !== null ? `, median ${cell.median_days} days after entry` : '';
    return `Of ${cell.eligible} adolescents who started with ${row.service}, ${cell.reached} (${cell.pct}%) later reached ${cell.service} within ${data.value?.window_months} months${median}.`;
}

const pathMax = computed(() => Math.max(1, ...(data.value?.paths.rows.map((p) => p.clients) ?? [1])));
// Pathways with fewer than min_cell adolescents: hidden by default, summarised
// as one "other" row; the toggle lists them individually.
const showSmallPaths = ref(false);
const visiblePaths = computed(() => (data.value?.paths.rows ?? []).filter((p) => showSmallPaths.value || !p.small));
const showSmallSameDay = ref(false);

// ---- chart / table views ---------------------------------------------------------
// Both sections open on the chart; the table is the same data read row by row.
const matrixView = ref<'chart' | 'table'>('chart');
const flowView = ref<'chart' | 'table'>('chart');
const toggleOn = 'rounded-full bg-[#173b3b] px-3 py-1 text-[11px] font-bold text-white';
const toggleOff = 'rounded-full px-3 py-1 text-[11px] font-bold text-[#55706a] hover:bg-[#eef0eb]';

/** Where-next as a list: per starting service, the services later reached (most first). */
const matrixTable = computed(() => (data.value?.progression.rows ?? []).map((row) => ({
    ...row,
    items: row.cells.filter((c) => !c.self && !c.suppressed && c.reached > 0).sort((a, b) => b.reached - a.reached || (b.pct ?? 0) - (a.pct ?? 0)),
    hidden: row.cells.filter((c) => !c.self && c.suppressed).length,
})));

/**
 * Flow as a list: one group per service at a step that sends adolescents on,
 * with where they went next. The same links the diagram draws, so "Other"
 * here means exactly what it means on the chart.
 */
const flowTable = computed(() => {
    if (!data.value) return [];
    const nodes = new Map(data.value.flow.nodes.map((n) => [n.id, n]));
    const groups = new Map<string, { stage: number; label: string; total: number; targets: { label: string; value: number }[] }>();
    for (const l of data.value.flow.links) {
        const src = nodes.get(l.source)!;
        const g = groups.get(l.source) ?? { stage: src.stage, label: src.label, total: src.value, targets: [] };
        g.targets.push({ label: nodes.get(l.target)!.label, value: l.value });
        groups.set(l.source, g);
    }
    const rank = (label: string) => (label === 'Other' ? 1 : 0);
    return [...groups.values()]
        .map((g) => {
            g.targets.sort((a, b) => rank(a.label) - rank(b.label) || b.value - a.value);
            const onward = g.targets.reduce((s, t) => s + t.value, 0);
            return { ...g, stopped: g.total - onward };
        })
        .sort((a, b) => a.stage - b.stage || rank(a.label) - rank(b.label) || b.total - a.total);
});
const flowStageTotals = computed(() => {
    const totals: number[] = [];
    for (const n of data.value?.flow.nodes ?? []) totals[n.stage] = (totals[n.stage] ?? 0) + n.value;
    return totals;
});
const visibleSameDay = computed(() => (data.value?.same_day.rows ?? []).filter((s) => showSmallSameDay.value || !s.small));
const sameDayMax = computed(() => Math.max(1, ...(data.value?.same_day.rows.map((s) => s.clients) ?? [1])));
const fmt = (n: number) => n.toLocaleString();

// ---- exports (Pro+) ------------------------------------------------------------
// Every filter control reloads on change, so the filter refs always describe
// the data on screen - file names and image subtitles are built from them.
const filterLabel = computed(() => {
    const sex = gender.value === '1' ? 'Male' : gender.value === '2' ? 'Female' : '';
    const age = { '10_14': 'Age 10–14', '15_19': 'Age 15–19', all: 'All ages' }[ageBand.value] ?? 'Age 10–19';
    return [district.value, facility.value, sex, age].filter(Boolean).join(' · ');
});
const subtitle = computed(() => `First visit ${from.value} to ${to.value} · followed ${windowMonths.value} months · ${filterLabel.value} · data up to ${data.value?.as_of ?? ''}`);
const fileStem = computed(() => {
    const scope = [district.value, facility.value, gender.value === '1' ? 'male' : gender.value === '2' ? 'female' : '', ageBand.value === '10_19' ? '' : ageBand.value]
        .filter(Boolean).map((v) => `_${v}`).join('');
    return `AHP_pathways_${windowMonths.value}m${scope}_${from.value}_${to.value}`;
});
const excelHref = computed(() => `/api/data6/pathways/excel?${queryString()}`);

function triggerDownload(href: string, filename: string): void {
    const link = document.createElement('a');
    link.href = href;
    link.download = filename;
    link.click();
}
function downloadCsv(suffix: string, headers: string[], rows: (string | number | null)[][]): void {
    const escape = (v: string | number | null): string => {
        if (v === null || v === undefined) return '';
        const s = String(v);
        return /[",\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
    };
    const lines = [headers, ...rows].map((r) => r.map(escape).join(','));
    const url = URL.createObjectURL(new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' }));
    triggerDownload(url, `${fileStem.value}_${suffix}.csv`);
    URL.revokeObjectURL(url);
}

function downloadMatrixCsv(): void {
    if (!data.value) return;
    const d = data.value;
    const rows = d.progression.rows.flatMap((row) => row.cells.filter((c) => !c.self).map((c) => [
        row.service, row.clients, row.moved_on_pct, c.service,
        c.suppressed ? `<${d.min_cell}` : c.eligible, c.suppressed ? 'suppressed' : c.reached, c.pct, c.median_days,
    ]));
    downloadCsv('where_next', ['Started with', 'Adolescents who started there', 'Moved on (%)', 'Later reached', 'Eligible', 'Reached', 'Reached (%)', 'Median days after entry'], rows);
}
function downloadFlowCsv(): void {
    if (!data.value) return;
    const rows = data.value.flow.links.map((l) => {
        const [fs, fl] = l.source.split('|');
        const [ts, tl] = l.target.split('|');
        return [Number(fs) + 1, fl, Number(ts) + 1, tl, l.value];
    }).sort((a, b) => (a[0] as number) - (b[0] as number) || (b[4] as number) - (a[4] as number));
    downloadCsv('flow_steps', ['From step', 'From service', 'To step', 'To service', 'Adolescents'], rows);
}
// Like the Excel sheet, the CSV lists every pathway whatever the toggle shows.
function downloadPathsCsv(): void {
    if (!data.value) return;
    const rows = data.value.paths.rows.map((r) => [r.steps.join(' > '), r.steps.length, r.clients, r.pct, r.median_days, r.small ? 'Yes' : '']);
    downloadCsv('pathways', ['Pathway', 'Steps', 'Adolescents', '% of those who moved on', 'Median days to last step', `Fewer than ${data.value.min_cell} adolescents`], rows);
}
function downloadSameDayCsv(): void {
    if (!data.value) return;
    const rows = data.value.same_day.rows.map((r) => [r.services.join(' + '), r.clients, r.small ? 'Yes' : '']);
    downloadCsv('same_day', ['Services reached on the same day', 'Adolescents', `Fewer than ${data.value.min_cell} adolescents`], rows);
}

// Flow diagram image: rendered by the component from its own SVG.
const flowChart = ref<InstanceType<typeof PathwayFlow> | null>(null);
async function downloadFlowImage(format: 'png' | 'jpg'): Promise<void> {
    const uri = await flowChart.value?.toImage(format, `How the ${data.value?.flow.clients ?? ''} who moved on got there`, subtitle.value);
    if (uri) triggerDownload(uri, `${fileStem.value}_flow.${format}`);
}

// Where-next heatmap image: the on-screen version is an HTML table, so the
// image is drawn straight onto a canvas from the same data and colour bins.
function downloadMatrixImage(format: 'png' | 'jpg'): void {
    if (!data.value) return;
    const d = data.value;
    const font = 'system-ui, -apple-system, "Segoe UI", sans-serif';
    const pad = 24, titleH = 56, headH = 40, rowH = 44, labelW = 200, movedW = 84, cellW = 104, gap = 2, scale = 2;
    const cols = d.progression.columns;
    const w = pad * 2 + labelW + movedW + cols.length * cellW;
    const h = pad * 2 + titleH + headH + d.progression.rows.length * rowH + 28;

    const canvas = document.createElement('canvas');
    canvas.width = w * scale;
    canvas.height = h * scale;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    ctx.scale(scale, scale);
    ctx.fillStyle = '#fcfcfb';
    ctx.fillRect(0, 0, w, h);
    ctx.textBaseline = 'middle';

    ctx.fillStyle = '#0b2c2c';
    ctx.font = `700 18px ${font}`;
    ctx.fillText('Where do they go next?', pad, pad + 10);
    ctx.fillStyle = '#52514e';
    ctx.font = `400 12px ${font}`;
    ctx.fillText(subtitle.value, pad, pad + 32);

    let y = pad + titleH;
    ctx.fillStyle = '#82908a';
    ctx.font = `700 10px ${font}`;
    ctx.fillText('STARTED WITH ↓ · LATER REACHED →', pad, y + headH / 2);
    ctx.textAlign = 'right';
    ctx.fillText('MOVED ON', pad + labelW + movedW - 8, y + headH / 2);
    ctx.textAlign = 'center';
    cols.forEach((c, i) => ctx.fillText(c.toUpperCase(), pad + labelW + movedW + i * cellW + cellW / 2, y + headH / 2, cellW - 6));
    y += headH;

    for (const row of d.progression.rows) {
        ctx.textAlign = 'left';
        ctx.fillStyle = '#244847';
        ctx.font = `700 12px ${font}`;
        ctx.fillText(row.service, pad, y + rowH / 2);
        const nameW = ctx.measureText(row.service).width;
        ctx.fillStyle = '#898781';
        ctx.font = `400 12px ${font}`;
        ctx.fillText(`· ${fmt(row.clients)}`, pad + nameW + 6, y + rowH / 2);
        ctx.textAlign = 'right';
        ctx.fillStyle = '#0b2c2c';
        ctx.font = `600 12px ${font}`;
        ctx.fillText(`${row.moved_on_pct}%`, pad + labelW + movedW - 8, y + rowH / 2);

        row.cells.forEach((cell, i) => {
            const x = pad + labelW + movedW + i * cellW;
            const blank = cell.self || cell.suppressed || cell.pct === null;
            const bin = blank ? null : binFor(cell.pct as number);
            ctx.fillStyle = cell.self ? '#f0efec' : cell.suppressed ? '#f5f4f0' : bin!.bg;
            ctx.fillRect(x + gap, y + gap, cellW - gap * 2, rowH - gap * 2);
            ctx.textAlign = 'center';
            if (blank) {
                ctx.fillStyle = '#b9b8b0';
                ctx.font = `700 12px ${font}`;
                ctx.fillText(cell.self ? '·' : '–', x + cellW / 2, y + rowH / 2);
                return;
            }
            ctx.fillStyle = bin!.ink;
            ctx.font = `700 12px ${font}`;
            ctx.fillText(`${cell.pct}%`, x + cellW / 2, y + rowH / 2 - 7);
            ctx.font = `400 10px ${font}`;
            ctx.fillText(`${cell.reached}/${cell.eligible}`, x + cellW / 2, y + rowH / 2 + 8);
        });
        y += rowH;
    }

    ctx.textAlign = 'left';
    ctx.fillStyle = '#898781';
    ctx.font = `400 11px ${font}`;
    ctx.fillText(`Cell: % reached (reached/eligible).   – = fewer than ${d.min_cell} eligible, not shown.   · = the starting service itself.   Only girls count towards ANC, PNC and family planning.`, pad, y + 16);

    triggerDownload(canvas.toDataURL(format === 'png' ? 'image/png' : 'image/jpeg', 0.95), `${fileStem.value}_where_next.${format}`);
}

const btn = 'inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white';
const viewBtn = 'inline-flex items-center gap-1 rounded-full border border-[#bdc9c3] px-2.5 py-0.5 text-[10px] font-bold text-[#3c605b] transition hover:bg-white print:hidden';

// ---- "view adolescents": the records behind a row, cell or band ------------------
interface MembersRequest { title: string; slug: string; selector: Record<string, string> }
const membersRequest = ref<MembersRequest | null>(null);
const slugify = (s: string) => s.replace(/\s*\+\s*/g, '+').replace(/\s*>\s*/g, '-').replace(/[^A-Za-z0-9+-]+/g, '_');

function viewPath(steps: string[]): void {
    const key = steps.join(' > ');
    membersRequest.value = { title: `Pathway: ${steps.join(' → ')}`, slug: `path_${slugify(key)}`, selector: { kind: 'path', key } };
}
function viewSameDay(services: string[]): void {
    const key = services.join(' + ');
    membersRequest.value = { title: `Same day: ${key}`, slug: `sameday_${slugify(key)}`, selector: { kind: 'same_day', key } };
}
function viewCell(start: string, reached: string): void {
    membersRequest.value = {
        title: `Started with ${start}, later reached ${reached}`,
        slug: `${slugify(start)}-to-${slugify(reached)}`,
        selector: { kind: 'cell', start, reached },
    };
}
function viewLink(source: string, target: string): void {
    const [ss, sl] = source.split('|');
    const [ts, tl] = target.split('|');
    membersRequest.value = {
        title: `Step ${Number(ss) + 1} ${sl} → step ${Number(ts) + 1} ${tl}`,
        slug: `step${Number(ss) + 1}_${slugify(sl)}-step${Number(ts) + 1}_${slugify(tl)}`,
        selector: { kind: 'link', source, target },
    };
}
const generatedOn = computed(() => new Date().toLocaleString());
function downloadPdf(): void {
    window.print();
}
</script>

<template>
    <Head title="Patient pathways" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-[#f5f3ee] text-[#173b3b] print:bg-white print:text-black">
            <div class="mx-auto max-w-[1500px] px-5 py-7 sm:px-8 lg:px-10 print:max-w-none print:px-0 print:py-0">

                <header class="flex flex-col justify-between gap-4 border-b border-[#d9ded7] pb-6 lg:flex-row lg:items-end">
                    <div>
                        <div class="mb-3 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.22em] text-[#e2644b]">
                            <span class="h-2 w-2 rounded-full bg-[#e2644b]" />{{ appTitle }}
                        </div>
                        <h1 class="font-serif text-4xl leading-tight tracking-tight">Patient pathways</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-[#60716d]">
                            Where adolescents go after their first service. Each adolescent is followed from their first-ever visit
                            for a fixed window, and the order in which they first reached each service is their pathway.
                        </p>
                        <p class="mt-2 hidden text-xs text-[#60716d] print:block">{{ subtitle }} · Report generated {{ generatedOn }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 print:hidden">
                        <Link href="/data6/flow" class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#3c605b] transition hover:bg-white">
                            <Route class="size-3.5" />Follow one patient
                        </Link>
                        <template v-if="isProPlus && data">
                            <a :href="excelHref" class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#3c605b] transition hover:bg-white"
                                title="Every table on this page, plus the method, in one workbook">
                                <FileSpreadsheet class="size-3.5" />Excel
                            </a>
                            <button class="inline-flex items-center gap-2 rounded-full border border-[#bdc9c3] px-4 py-2 text-xs font-bold text-[#3c605b] transition hover:bg-white"
                                title="Opens the print dialog — choose &quot;Save as PDF&quot; as the destination" @click="downloadPdf">
                                <Download class="size-3.5" />Download PDF
                            </button>
                        </template>
                        <button class="inline-flex items-center gap-2 rounded-full bg-[#173b3b] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#285655]" @click="load">
                            <RefreshCw class="size-3.5" :class="loading ? 'animate-spin' : ''" />Refresh
                        </button>
                    </div>
                </header>

                <!-- Filters: one row, scope everything below -->
                <section class="mt-5 flex flex-wrap items-end gap-3 print:hidden">
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">First visit between</span>
                        <div class="flex gap-1 rounded-full border border-[#cbd3cd] bg-white p-1">
                            <button v-for="preset in presets" :key="preset.key"
                                class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                                :class="activePreset === preset.key ? 'bg-[#173b3b] text-white' : 'text-[#55706a] hover:bg-[#eef0eb]'"
                                @click="applyPreset(preset.key)">{{ preset.label }}</button>
                        </div>
                    </div>
                    <template v-if="activePreset === 'custom'">
                        <label class="text-xs text-[#55706a]">From
                            <input v-model="from" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" />
                        </label>
                        <label class="text-xs text-[#55706a]">To
                            <input v-model="to" type="date" class="ml-1 border border-[#cbd3cd] bg-white px-2 py-1.5 text-xs" @change="load" />
                        </label>
                    </template>
                    <label class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">Follow each client for</span>
                        <select v-model.number="windowMonths" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]" @change="load">
                            <option v-for="w in props.windows" :key="w" :value="w">{{ w }} months</option>
                        </select>
                    </label>
                    <select v-model="district" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]" @change="load">
                        <option value="">All districts</option>
                        <option v-for="d in filterOptions.districts" :key="d" :value="d">{{ d }}</option>
                    </select>
                    <select v-model="facility" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]" @change="load">
                        <option value="">All facilities</option>
                        <option v-for="f in filterOptions.facilities" :key="f" :value="f">{{ f }}</option>
                    </select>
                    <select v-model="gender" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]" @change="load">
                        <option value="">All sexes</option>
                        <option value="1">Male</option>
                        <option value="2">Female</option>
                    </select>
                    <select v-model="ageBand" class="border border-[#cbd3cd] bg-white px-3 py-2 text-xs text-[#45645e]" @change="load">
                        <option value="10_19">Age 10–19 at entry</option>
                        <option value="10_14">Age 10–14 at entry</option>
                        <option value="15_19">Age 15–19 at entry</option>
                        <option value="all">All ages (QA)</option>
                    </select>
                </section>

                <div v-if="error" class="mt-5 flex items-center gap-2 bg-[#fff1ed] px-4 py-3 text-sm text-[#b74f3d]">
                    <CircleAlert class="size-4 shrink-0" />{{ error }}
                </div>
                <div v-else-if="loading && !data" class="mt-8 py-16 text-center text-sm text-[#788681]">Tracing pathways…</div>

                <template v-else-if="data">
                    <!-- Headline numbers -->
                    <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" :class="loading ? 'opacity-60' : ''">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#82908a]">New adolescents</p>
                            <p class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ fmt(data.summary.cohort) }}</p>
                            <p class="mt-1 text-xs text-[#788681]">First-ever visit in the period</p>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#82908a]">Followed for {{ data.window_months }} months</p>
                            <p class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ fmt(data.summary.followed) }}</p>
                            <p class="mt-1 text-xs text-[#788681]">{{ fmt(data.summary.too_recent) }} entered too recently to have had the full window (data up to {{ data.as_of }})</p>
                        </div>
                        <div class="border border-[#173b3b] bg-[#173b3b] p-4 text-white">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#abc1b9]">Moved on to a new service</p>
                            <p class="mt-2 text-3xl font-semibold">{{ data.summary.moved_on_pct }}%</p>
                            <p class="mt-1 text-xs text-[#abc1b9]">{{ fmt(data.summary.moved_on) }} adolescents · {{ data.summary.distinct_paths }} different pathways</p>
                        </div>
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#82908a]">Several services, same day only</p>
                            <p class="mt-2 text-3xl font-semibold text-[#0b2c2c]">{{ data.summary.same_day_only_pct }}%</p>
                            <p class="mt-1 text-xs text-[#788681]">{{ fmt(data.summary.same_day_only) }} adolescents served together at one visit, never moved on</p>
                        </div>
                    </section>

                    <!-- Headline findings -->
                    <section class="mt-4 border-l-4 border-[#e2644b] bg-white px-5 py-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#e2644b]">Strongest onward links</p>
                        <ul v-if="data.progression.headlines.length" class="mt-2 space-y-1.5 text-sm text-[#244847]">
                            <li v-for="h in data.progression.headlines" :key="`${h.from}-${h.to}`">
                                Of <strong>{{ fmt(h.eligible) }}</strong> adolescents who started with <strong>{{ h.from }}</strong>,
                                <strong>{{ h.pct }}%</strong> ({{ h.reached }}) later reached <strong>{{ h.to }}</strong>
                                <span v-if="h.median_days !== null" class="text-[#788681]">— typically {{ h.median_days }} days after entry</span>.
                            </li>
                        </ul>
                        <p v-else class="mt-2 text-sm text-[#788681]">No onward link has enough adolescents behind it (10+ eligible, {{ data.min_cell }}+ reached) to report for these filters.</p>
                    </section>

                    <!-- Progression matrix -->
                    <section class="mt-6 border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-end">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="font-serif text-2xl text-[#173b3b]">Where do they go next?</h2>
                                    <div class="flex rounded-full border border-[#cbd3cd] bg-white p-0.5 print:hidden" role="group" aria-label="View as">
                                        <button :class="matrixView === 'chart' ? toggleOn : toggleOff" :aria-pressed="matrixView === 'chart'" @click="matrixView = 'chart'">Chart</button>
                                        <button :class="matrixView === 'table' ? toggleOn : toggleOff" :aria-pressed="matrixView === 'table'" @click="matrixView = 'table'">Table</button>
                                    </div>
                                    <div v-if="isProPlus" class="flex items-center gap-2 print:hidden">
                                        <button :class="btn" @click="downloadMatrixImage('png')"><Download class="size-3" />PNG</button>
                                        <button :class="btn" @click="downloadMatrixImage('jpg')"><Download class="size-3" />JPG</button>
                                        <button :class="btn" @click="downloadMatrixCsv"><Download class="size-3" />CSV</button>
                                    </div>
                                </div>
                                <p class="mt-1 max-w-3xl text-xs leading-5 text-[#788681]">
                                    Each row is a starting service. Each cell: the share of adolescents who started there and later reached the column's
                                    service within {{ data.window_months }} months. Adolescents who already had that service at entry are left out of the cell,
                                    and only girls count towards ANC, PNC and family planning.
                                </p>
                            </div>
                            <div v-if="matrixView === 'chart'" class="flex flex-wrap items-center gap-2 text-[10px] text-[#52514e]" aria-label="Colour scale">
                                <span v-for="b in bins" :key="b.label" class="inline-flex items-center gap-1">
                                    <span class="inline-block size-3 rounded-sm border border-[rgba(11,11,11,0.10)]" :style="{ background: b.bg }" />{{ b.label }}
                                </span>
                            </div>
                        </div>
                        <div v-if="matrixView === 'chart'" class="mt-4 overflow-x-auto print:overflow-visible">
                            <table class="w-full min-w-[860px] border-separate border-spacing-[2px] text-xs print:min-w-0 print:text-[9px]">
                                <thead>
                                    <tr class="text-[10px] font-bold uppercase tracking-wider text-[#82908a]">
                                        <th class="sticky left-0 z-[1] bg-[#fcfcfb] px-2 py-2 text-left">Started with ↓ · later reached →</th>
                                        <th class="px-2 py-2 text-right">Moved on</th>
                                        <th v-for="col in data.progression.columns" :key="col" class="px-2 py-2 text-center">{{ col }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in data.progression.rows" :key="row.service">
                                        <th class="sticky left-0 z-[1] bg-[#fcfcfb] px-2 py-2 text-left font-bold text-[#244847]">
                                            {{ row.service }} <span class="font-normal text-[#898781]">· {{ fmt(row.clients) }}</span>
                                        </th>
                                        <td class="px-2 py-2 text-right font-semibold text-[#0b2c2c]" style="font-variant-numeric: tabular-nums">{{ row.moved_on_pct }}%</td>
                                        <td v-for="cell in row.cells" :key="cell.service" :title="cellTitle(row, cell)"
                                            class="h-12 rounded-sm px-2 py-1.5 text-center align-middle"
                                            :style="cell.self || cell.suppressed || cell.pct === null ? {} : { background: binFor(cell.pct).bg, color: binFor(cell.pct).ink }"
                                            :class="cell.self ? 'bg-[#f0efec] text-[#b9b8b0]' : cell.suppressed ? 'bg-[#f5f4f0] text-[#b9b8b0]' : ''">
                                            <template v-if="cell.self">·</template>
                                            <template v-else-if="cell.suppressed">–</template>
                                            <button v-else-if="cell.reached > 0" class="block w-full cursor-pointer rounded-sm underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#173b3b]"
                                                :aria-label="`${cellTitle(row, cell)} List them.`" @click="viewCell(row.service, cell.service)">
                                                <span class="block font-bold" style="font-variant-numeric: tabular-nums">{{ cell.pct }}%</span>
                                                <span class="block text-[10px] opacity-80" style="font-variant-numeric: tabular-nums">{{ cell.reached }}/{{ cell.eligible }}</span>
                                            </button>
                                            <template v-else>
                                                <span class="block font-bold" style="font-variant-numeric: tabular-nums">{{ cell.pct }}%</span>
                                                <span class="block text-[10px] opacity-80" style="font-variant-numeric: tabular-nums">{{ cell.reached }}/{{ cell.eligible }}</span>
                                            </template>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-if="matrixView === 'chart'" class="mt-2 text-[11px] text-[#898781]">– fewer than {{ data.min_cell }} eligible adolescents, not shown · hover a cell for the full sentence · click a cell to list the adolescents behind it.</p>

                        <!-- Table view: the same cells, listed per starting service -->
                        <div v-else class="mt-4 overflow-x-auto print:overflow-visible">
                            <table class="w-full min-w-[640px] text-xs">
                                <thead class="border-b border-[#d9ded7] text-[10px] font-bold uppercase tracking-wider text-[#82908a]">
                                    <tr>
                                        <th class="pb-2 text-left">Started with</th>
                                        <th class="pb-2 text-left">Later reached</th>
                                        <th class="pb-2 text-right">Adolescents</th>
                                        <th class="pb-2 text-right">Of eligible</th>
                                        <th class="pb-2 text-right">Share</th>
                                        <th class="w-1/5 pb-2" />
                                        <th class="pb-2 text-right">Median days after entry</th>
                                        <th class="pb-2 print:hidden" />
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="row in matrixTable" :key="row.service">
                                        <tr v-for="(item, i) in row.items.length ? row.items : [null]" :key="item?.service ?? 'none'" class="border-b border-[#eef0eb] align-top">
                                            <td v-if="i === 0" :rowspan="Math.max(1, row.items.length)" class="py-2.5 pr-4">
                                                <span class="font-bold text-[#244847]">{{ row.service }}</span>
                                                <span class="block text-[11px] text-[#898781]">{{ fmt(row.clients) }} started · {{ row.moved_on_pct }}% moved on</span>
                                                <span v-if="row.hidden" class="block text-[10px] text-[#b9b8b0]">{{ row.hidden }} services with fewer than {{ data.min_cell }} eligible not listed</span>
                                            </td>
                                            <template v-if="item">
                                                <td class="py-2.5 font-semibold text-[#244847]">{{ item.service }}</td>
                                                <td class="py-2.5 text-right font-bold text-[#0b2c2c]" style="font-variant-numeric: tabular-nums">{{ item.reached }}</td>
                                                <td class="py-2.5 text-right text-[#52514e]" style="font-variant-numeric: tabular-nums">{{ fmt(item.eligible) }}</td>
                                                <td class="py-2.5 text-right font-semibold text-[#0b2c2c]" style="font-variant-numeric: tabular-nums">{{ item.pct }}%</td>
                                                <td class="py-2.5 pl-3"><div class="h-2 rounded-sm bg-[#2a78d6]" :style="{ width: `${Math.max(2, item.pct ?? 0)}%` }" /></td>
                                                <td class="py-2.5 text-right text-[#52514e]" style="font-variant-numeric: tabular-nums">{{ item.median_days ?? '—' }}</td>
                                                <td class="py-2.5 pl-3 text-right print:hidden"><button :class="viewBtn" @click="viewCell(row.service, item.service)">View {{ item.reached }}</button></td>
                                            </template>
                                            <td v-else colspan="7" class="py-2.5 italic text-[#898781]">Nobody reached another service within {{ data.window_months }} months.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <p class="mt-2 text-[11px] text-[#898781]">Only services at least one adolescent reached are listed; the chart view also shows the zeros. Bar = share of eligible adolescents, on a 0–100% scale.</p>
                        </div>
                    </section>

                    <!-- Flow diagram -->
                    <section class="mt-6 border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="font-serif text-2xl text-[#173b3b]">How the {{ fmt(data.flow.clients) }} who moved on got there</h2>
                            <div v-if="data.flow.links.length" class="flex rounded-full border border-[#cbd3cd] bg-white p-0.5 print:hidden" role="group" aria-label="View as">
                                <button :class="flowView === 'chart' ? toggleOn : toggleOff" :aria-pressed="flowView === 'chart'" @click="flowView = 'chart'">Chart</button>
                                <button :class="flowView === 'table' ? toggleOn : toggleOff" :aria-pressed="flowView === 'table'" @click="flowView = 'table'">Table</button>
                            </div>
                            <div v-if="isProPlus && data.flow.links.length" class="flex items-center gap-2 print:hidden">
                                <button :class="btn" @click="downloadFlowImage('png')"><Download class="size-3" />PNG</button>
                                <button :class="btn" @click="downloadFlowImage('jpg')"><Download class="size-3" />JPG</button>
                                <button :class="btn" @click="downloadFlowCsv"><Download class="size-3" />CSV</button>
                            </div>
                        </div>
                        <p class="mt-1 max-w-3xl text-xs leading-5 text-[#788681]">
                            Steps follow the order services were first reached: step 1 is the first-ever visit, step 2 the next new service, and so on
                            — repeat visits don't add steps. Services reached on the same day share a step (e.g. "ANC + HIV testing").
                            A service with fewer than {{ data.min_cell }} adolescents at a step is grouped as "Other".
                            <template v-if="data.flow.longer_than_shown"> {{ data.flow.longer_than_shown }} pathways run past step 4 and are cut there.</template>
                        </p>
                        <template v-if="data.flow.links.length">
                            <!-- v-show, not v-if: the diagram stays mounted so its PNG/JPG export works from the table view too -->
                            <PathwayFlow v-show="flowView === 'chart'" ref="flowChart" class="mt-4" :nodes="data.flow.nodes" :links="data.flow.links"
                                @select-link="(l) => viewLink(l.source, l.target)" />
                            <p v-show="flowView === 'chart'" class="mt-2 text-[11px] text-[#898781] print:hidden">Hover a band for its count · click a band to list the adolescents who took it.</p>

                            <!-- Table view: the diagram's bands, listed per service and step -->
                            <div v-if="flowView === 'table'" class="mt-4 overflow-x-auto print:overflow-visible">
                                <p class="mb-3 text-xs text-[#52514e]">
                                    Reached step 2: <strong>{{ flowStageTotals[1] ?? 0 }}</strong>
                                    <template v-if="flowStageTotals[2]"> · step 3: <strong>{{ flowStageTotals[2] }}</strong></template>
                                    <template v-if="flowStageTotals[3]"> · step 4: <strong>{{ flowStageTotals[3] }}</strong></template>
                                </p>
                                <table class="w-full min-w-[640px] text-xs">
                                    <thead class="border-b border-[#d9ded7] text-[10px] font-bold uppercase tracking-wider text-[#82908a]">
                                        <tr>
                                            <th class="pb-2 text-left">From</th>
                                            <th class="pb-2 text-left">Went next to</th>
                                            <th class="pb-2 text-right">Adolescents</th>
                                            <th class="pb-2 text-right">Share of the group</th>
                                            <th class="w-1/4 pb-2" />
                                            <th class="pb-2 print:hidden" />
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="g in flowTable" :key="`${g.stage}|${g.label}`">
                                            <tr v-for="(t, i) in g.targets" :key="t.label" class="border-b border-[#eef0eb] align-top">
                                                <td v-if="i === 0" :rowspan="g.targets.length + (g.stopped > 0 ? 1 : 0)" class="py-2.5 pr-4">
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#898781]">Step {{ g.stage + 1 }}{{ g.stage === 0 ? ' · entry' : '' }}</span>
                                                    <span class="block font-bold text-[#244847]">{{ g.label }}</span>
                                                    <span class="block text-[11px] text-[#898781]">{{ g.total }} adolescents</span>
                                                </td>
                                                <td class="py-2.5 font-semibold text-[#244847]">Step {{ g.stage + 2 }} · {{ t.label }}</td>
                                                <td class="py-2.5 text-right font-bold text-[#0b2c2c]" style="font-variant-numeric: tabular-nums">{{ t.value }}</td>
                                                <td class="py-2.5 text-right text-[#52514e]" style="font-variant-numeric: tabular-nums">{{ Math.round((t.value / g.total) * 1000) / 10 }}%</td>
                                                <td class="py-2.5 pl-3"><div class="h-2 rounded-sm bg-[#2a78d6]" :style="{ width: `${Math.max(2, (t.value / g.total) * 100)}%` }" /></td>
                                                <td class="py-2.5 pl-3 text-right print:hidden">
                                                    <button :class="viewBtn" @click="viewLink(`${g.stage}|${g.label}`, `${g.stage + 1}|${t.label}`)">View {{ t.value }}</button>
                                                </td>
                                            </tr>
                                            <tr v-if="g.stopped > 0" class="border-b border-[#eef0eb] text-[#898781]">
                                                <td class="py-2.5 italic">No further new service</td>
                                                <td class="py-2.5 text-right" style="font-variant-numeric: tabular-nums">{{ g.stopped }}</td>
                                                <td class="py-2.5 text-right" style="font-variant-numeric: tabular-nums">{{ Math.round((g.stopped / g.total) * 1000) / 10 }}%</td>
                                                <td />
                                                <td class="print:hidden" />
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <p class="mt-2 text-[11px] text-[#898781]">Services at step 2 or later that sent nobody further aren't listed as a "From" group — everyone there stopped. Timings are in the pathways table below.</p>
                            </div>
                        </template>
                        <p v-else class="py-12 text-center text-xs text-[#898781]">No adolescent moved on to a new service in this window.</p>
                    </section>

                    <!-- Pathways table (the table-view twin of the flow diagram) -->
                    <section class="mt-6 grid gap-6 xl:grid-cols-[3fr_2fr] print:grid-cols-1">
                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="font-serif text-2xl text-[#173b3b]">Most common pathways</h2>
                                <button v-if="isProPlus" :class="btn" class="print:hidden" @click="downloadPathsCsv"><Download class="size-3" />CSV</button>
                            </div>
                            <p class="mt-1 text-xs text-[#788681]">Adolescents who moved on, grouped by their exact pathway.</p>
                            <table v-if="data.paths.rows.length" class="mt-4 w-full text-xs">
                                <thead class="border-b border-[#d9ded7] text-[10px] font-bold uppercase tracking-wider text-[#82908a]">
                                    <tr><th class="pb-2 text-left">Pathway</th><th class="pb-2 text-right">Adolescents</th><th class="w-1/4 pb-2" /><th class="pb-2 text-right">Median days to last step</th><th class="pb-2 print:hidden" /></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="p in visiblePaths" :key="p.steps.join('>')" class="border-b border-[#eef0eb]" :class="p.small ? 'bg-[#f7f6f2]' : ''">
                                        <td class="py-2.5" :class="p.small ? 'pl-2' : ''">
                                            <span v-for="(step, i) in p.steps" :key="i" class="inline-flex items-center">
                                                <span class="rounded px-1.5 py-0.5 font-semibold" :class="p.small ? 'bg-[#eceae4] text-[#52655f]' : 'bg-[#eef0eb] text-[#244847]'">{{ step }}</span>
                                                <ArrowRight v-if="i < p.steps.length - 1" class="mx-1 size-3 text-[#898781]" />
                                            </span>
                                        </td>
                                        <td class="py-2.5 text-right font-bold" :class="p.small ? 'text-[#52514e]' : 'text-[#0b2c2c]'" style="font-variant-numeric: tabular-nums">{{ p.clients }} <span class="font-normal text-[#898781]">({{ p.pct }}%)</span></td>
                                        <td class="py-2.5 pl-3"><div class="h-2 rounded-sm bg-[#2a78d6]" :style="{ width: `${Math.max(2, (p.clients / pathMax) * 100)}%` }" /></td>
                                        <td class="py-2.5 text-right text-[#52514e]" style="font-variant-numeric: tabular-nums">{{ p.median_days }}</td>
                                        <td class="py-2.5 pl-3 text-right print:hidden"><button :class="viewBtn" @click="viewPath(p.steps)">View</button></td>
                                    </tr>
                                    <tr v-if="data.paths.other_clients && !showSmallPaths" class="text-[#788681]">
                                        <td class="py-2.5 italic">{{ data.paths.other_paths }} other pathways, each with fewer than {{ data.min_cell }} adolescents</td>
                                        <td class="py-2.5 text-right" style="font-variant-numeric: tabular-nums">{{ data.paths.other_clients }} ({{ data.paths.other_pct }}%)</td>
                                        <td /><td /><td class="print:hidden" />
                                    </tr>
                                </tbody>
                            </table>
                            <button v-if="data.paths.other_paths" class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white print:hidden"
                                :aria-expanded="showSmallPaths" @click="showSmallPaths = !showSmallPaths">
                                {{ showSmallPaths ? `Hide the ${data.paths.other_paths} smaller pathways` : `Show the ${data.paths.other_paths} smaller pathways` }}
                            </button>
                            <p v-if="showSmallPaths" class="mt-2 text-[11px] text-[#898781]">Shaded rows have fewer than {{ data.min_cell }} adolescents each — treat their percentages and timings with caution.</p>
                            <p v-else class="py-8 text-center text-xs text-[#898781]">No pathways to show.</p>
                        </div>

                        <div class="border border-[#d9ded7] bg-[#fcfcfb] p-5 print:break-inside-avoid">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="font-serif text-2xl text-[#173b3b]">Served together on one day</h2>
                                <button v-if="isProPlus" :class="btn" class="print:hidden" @click="downloadSameDayCsv"><Download class="size-3" />CSV</button>
                            </div>
                            <p class="mt-1 text-xs text-[#788681]">Services first reached on the same day — integrated care at one visit, not movement between services.</p>
                            <table v-if="data.same_day.rows.length" class="mt-4 w-full text-xs">
                                <tbody>
                                    <tr v-for="s in visibleSameDay" :key="s.services.join('+')" class="border-b border-[#eef0eb]" :class="s.small ? 'bg-[#f7f6f2]' : ''">
                                        <td class="py-2.5 font-semibold" :class="s.small ? 'pl-2 text-[#52655f]' : 'text-[#244847]'">{{ s.services.join(' + ') }}</td>
                                        <td class="py-2.5 text-right font-bold" :class="s.small ? 'text-[#52514e]' : 'text-[#0b2c2c]'" style="font-variant-numeric: tabular-nums">{{ s.clients }}</td>
                                        <td class="w-1/3 py-2.5 pl-3"><div class="h-2 rounded-sm bg-[#2a78d6]" :style="{ width: `${Math.max(2, (s.clients / sameDayMax) * 100)}%` }" /></td>
                                        <td class="py-2.5 pl-3 text-right print:hidden"><button :class="viewBtn" @click="viewSameDay(s.services)">View</button></td>
                                    </tr>
                                    <tr v-if="data.same_day.other_clients && !showSmallSameDay" class="text-[#788681]">
                                        <td class="py-2.5 italic">{{ data.same_day.other_combos }} other combinations, each with fewer than {{ data.min_cell }} adolescents</td>
                                        <td class="py-2.5 text-right" style="font-variant-numeric: tabular-nums">{{ data.same_day.other_clients }}</td>
                                        <td /><td class="print:hidden" />
                                    </tr>
                                </tbody>
                            </table>
                            <p v-else class="py-8 text-center text-xs text-[#898781]">No services were reached together on one day.</p>
                            <button v-if="data.same_day.other_combos" class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-[#bdc9c3] px-3 py-1 text-[11px] font-bold text-[#3c605b] transition hover:bg-white print:hidden"
                                :aria-expanded="showSmallSameDay" @click="showSmallSameDay = !showSmallSameDay">
                                {{ showSmallSameDay ? `Hide the ${data.same_day.other_combos} smaller combinations` : `Show the ${data.same_day.other_combos} smaller combinations` }}
                            </button>
                            <p v-if="showSmallSameDay" class="mt-2 text-[11px] text-[#898781]">Shaded rows have fewer than {{ data.min_cell }} adolescents each — treat them with caution.</p>
                        </div>
                    </section>

                    <!-- Method -->
                    <section class="mt-6 border border-[#d9ded7] bg-white p-5 text-xs leading-5 text-[#52655f] print:break-inside-avoid">
                        <h2 class="flex items-center gap-2 text-sm font-bold text-[#244847]"><Info class="size-4" />How to read this</h2>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            <li><strong>Who is counted:</strong> adolescents whose first-ever visit to any service falls in the selected period, aged within the band on that day. Each is followed for {{ data.window_months }} months from that visit; anyone who entered too recently to have had the full window is left out, so recent arrivals don't pull the rates down.</li>
                            <li><strong>A pathway</strong> is the order in which each service was first reached. Repeat visits to the same service don't add steps. Services first reached on the same day share one step, because the data can't say which came first.</li>
                            <li><strong>Not included:</strong> mental health, health education and counselling have no visit date, so they can't be placed in a pathway.</li>
                            <li><strong>Girls-only services:</strong> only adolescents recorded as female count towards ANC, PNC and family planning.</li>
                            <li><strong>Small numbers:</strong> anything with fewer than {{ data.min_cell }} adolescents is hidden or grouped as "Other" — the percentages would be unreliable and could identify individuals. The smaller pathways and same-day combinations can be shown with the toggles under those tables, and their Excel and CSV downloads list them all.</li>
                            <li><strong>This shows what was recorded, not why.</strong> Pathways reflect referral practice and which registers were filled in; a low rate can mean a missed linkage or a service recorded elsewhere. Registers are transcribed from paper, so recent months may still be filling in.</li>
                            <li><strong>Behind every number:</strong> "View" buttons (and clicking a heatmap cell or flow band) list the adolescents behind that number, each linked to their timeline on the patient flow page — useful for checking odd routes such as PNC before ANC.</li>
                        </ul>
                    </section>

                    <PathwayMembers :request="membersRequest" :filter-query="queryString()" :file-stem="fileStem" :subtitle="subtitle" @close="membersRequest = null" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
